const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');

const source = fs.readFileSync(path.join(__dirname, '../assets/js/stakeholder-monitoring.js'), 'utf8');
const prefix = source.slice(0, source.indexOf("  document.addEventListener('click'"));
const profile = (name, id) => ({ id, linkedin_url: 'https://www.linkedin.com/in/' + name, status: 'active' });
const csvFile = text => ({ name: 'stakeholders.csv', text: async () => text });

function harness() {
  const nodes = {};
  function element() {
    return {
      classList: { remove() {} }, setAttribute() {}, focus() {}, remove() { this.removed = true; },
      addEventListener(type, callback) { this[type] = callback; },
      querySelector(selector) { return nodes[selector] ||= element(); }
    };
  }
  const context = vm.createContext({
    URLSearchParams, Set, console,
    window: { location: { search: '' }, confirm: () => true, alert() {} },
    document: {
      getElementById: element, querySelector: element, createElement: element,
      body: { appendChild(node) { nodes.backdrop = node; } },
      addEventListener() {}, removeEventListener() {}
    }
  });
  vm.runInContext(prefix + `
    globalThis.subject = { parseCsv, compareCsvRows, reviewCsvImport, importCsvFile, state,
      setup(deps) {
        api = deps.api;
        addStakeholder = deps.add;
        refreshAll = deps.refresh;
        toast = deps.toast;
        if (deps.review) reviewCsvImport = deps.review;
      }
    };
  })();`, context);
  return { subject: context.subject, context, nodes };
}

test('counts normalized existing profiles, new profiles, duplicates, and missing records', () => {
  const { subject } = harness();
  const rows = subject.parseCsv('linkedin,note\nHTTP://WWW.LINKEDIN.COM/in/ALICE/?x=1,hello\nhttps://linkedin.com/in/alice,duplicate\nhttps://linkedin.com/in/bob,new\nhttps://linkedin.com/in/bob,duplicate');
  const result = subject.compareCsvRows(rows, [profile('alice', 1), profile('carol', 2), { id: 3 }]);
  assert.equal(result.newRows.length, 1);
  assert.equal(result.existing, 1);
  assert.equal(result.duplicates, 2);
  assert.equal(result.missing[0].id, 2);
  assert.equal(result.unidentified, 1);
});

test('finds URLs in arbitrary columns and multiline text without requiring headers', () => {
  const { subject } = harness();
  for (const separator of [',', ';', '\t']) {
    const rows = subject.parseCsv('\uFEFFlinkedin' + separator + 'note\r\nhttps://linkedin.com/in/alice' + separator + '"hello\r\n""Alice"""');
    assert.equal(rows[0].linkedin_url, 'https://www.linkedin.com/in/alice');
    assert.equal(rows.length, 1);
  }
  const rows = subject.parseCsv('Alice,"Contact https://linkedin.com/in/alice, or https://www.linkedin.com/in/bob/?trk=csv"\n"Unclosed quote; dk.linkedin.com/in/carol\nWebsite: https://example.com');
  assert.deepEqual(Array.from(rows, row => row.linkedin_url), [
    'https://www.linkedin.com/in/alice', 'https://www.linkedin.com/in/bob', 'https://www.linkedin.com/in/carol'
  ]);
  assert.equal(subject.compareCsvRows(rows, [{ id: 3, linkedin_url: 'https://dk.linkedin.com/in/carol/' }]).existing, 1);
});

test('ignores unrelated URLs and rejects files with no LinkedIn profile URLs', () => {
  const { subject } = harness();
  for (const text of ['', 'linkedin,note', 'name\nAlice', 'linkedin,note\n,missing',
    'https://example.com', 'https://linkedin.com/company/coherta',
    'https://notlinkedin.com/in/alice', 'https://linkedin.com.evil.com/in/alice',
    'https://example.com/linkedin.com/in/alice', 'https://linkedin.com/in/']) {
    assert.throws(() => subject.parseCsv(text));
  }
});

function setupImport(options = {}) {
  const env = harness();
  const calls = [];
  const initial = options.initial || [profile('alice', 1), profile('carol', 2)];
  let reads = 0;
  env.subject.state.accountCreditLimit = options.credits === undefined ? 10 : options.credits;
  env.subject.setup({
    api: async (url, request) => {
      if (request) {
        calls.push('delete:' + url.split('/').pop());
        if (options.failDelete) throw new Error('Delete failed');
        return {};
      }
      calls.push('read');
      reads += 1;
      return { stakeholders: reads > 1 && options.changed ? initial.concat(profile('dave', 4)) : initial };
    },
    add: async row => {
      calls.push('add:' + row.linkedin_url.split('/').pop());
      if (options.failAdd) throw new Error('Upload failed');
    },
    review: async comparison => {
      calls.push('review');
      assert.equal(comparison.existing, 1);
      return options.cancel ? null : { remove: Boolean(options.remove) };
    },
    refresh: async () => calls.push('refresh'),
    toast: message => calls.push(message)
  });
  env.context.window.alert = message => calls.push(message);
  return { ...env, calls };
}
const input = csvFile('linkedin\nhttps://linkedin.com/in/alice\nhttps://linkedin.com/in/bob');

test('canceling review makes no changes', async () => {
  const env = setupImport({ cancel: true });
  await env.subject.importCsvFile(input);
  assert.deepEqual(env.calls, ['read', 'review']);
});

test('keeps missing records by default and uploads only new rows', async () => {
  const env = setupImport();
  await env.subject.importCsvFile(input);
  assert.deepEqual(env.calls.slice(0, 4), ['read', 'review', 'read', 'add:bob']);
  assert.ok(!env.calls.some(call => call.startsWith('delete:')));
});

test('removes only missing records after new rows succeed', async () => {
  const env = setupImport({ remove: true });
  await env.subject.importCsvFile(input);
  assert.deepEqual(env.calls.slice(0, 5), ['read', 'review', 'read', 'add:bob', 'delete:2']);
});

test('supports removal when the CSV contains no new profiles', async () => {
  const env = setupImport({ remove: true });
  await env.subject.importCsvFile(csvFile('linkedin\nhttps://linkedin.com/in/alice'));
  assert.ok(env.calls.includes('delete:2'));
  assert.ok(!env.calls.some(call => call.startsWith('add:')));
});

test('aborts before writes if reviewed data changes or credits are insufficient', async () => {
  for (const options of [{ changed: true }, { credits: 2 }]) {
    const env = setupImport({ ...options, remove: true });
    await assert.rejects(env.subject.importCsvFile(input));
    assert.deepEqual(env.calls, ['read', 'review', 'read']);
  }
});

test('failed additions prevent all deletions and refresh the list', async () => {
  const env = setupImport({ remove: true, failAdd: true });
  await env.subject.importCsvFile(input);
  assert.ok(!env.calls.some(call => call.startsWith('delete:')));
  assert.ok(env.calls.some(call => call.includes('0 added, 0 removed')));
  assert.equal(env.calls.at(-1), 'refresh');
});

test('deletion failures report partial progress and stop', async () => {
  const env = setupImport({ remove: true, failDelete: true });
  await env.subject.importCsvFile(input);
  assert.ok(env.calls.some(call => call.includes('1 added, 0 removed')));
  assert.equal(env.calls.at(-1), 'refresh');
});

test('both deletion confirmations must be accepted; either rejection keeps review open', async () => {
  for (const answers of [[false], [true, false], [true, true]]) {
    const env = harness();
    let count = 0;
    env.context.window.confirm = () => answers[count++];
    const result = env.subject.reviewCsvImport({ newRows: [], existing: 1, duplicates: 0,
      missing: [profile('carol', 2)], unidentified: 0 }, '<sample>.csv', 'company');
    env.nodes.backdrop.querySelector('#smCsvRemoveMissing').checked = true;
    env.nodes['[data-csv-apply]'].click();
    assert.equal(count, answers.length);
    if (answers.every(Boolean)) {
      assert.equal((await result).remove, true);
    } else {
      assert.ok(!env.nodes.backdrop.removed);
      env.nodes['[data-csv-cancel]'].click();
      assert.equal(await result, null);
    }
    assert.ok(env.nodes.backdrop.innerHTML.includes('&lt;sample&gt;.csv'));
  }
});
