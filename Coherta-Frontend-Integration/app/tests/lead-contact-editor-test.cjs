const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');
const context = vm.createContext({ window: {}, document: { addEventListener() {} } });
vm.runInContext(fs.readFileSync(path.join(__dirname, '../assets/js/lead-contact-editor.js'), 'utf8'), context);
const editor = context.window.LeadContactEditor;

test('missing contact data offers manual entry without LinkedIn or enrichment', () => {
  const row = { id: 8, participant_id: 21, name: 'Alex' };
  assert.match(editor.renderCell(null, 'display', row, 'phone_number'), /Add phone/);
  assert.match(editor.renderCell(null, 'display', row, 'email'), /Add email/);
  assert.equal(editor.tableContact(row, 'email').target.participant_id, 21);
});

test('sort, search and export receive contact values without controls', () => {
  const row = { id: 8, participant_id: 21, phonenumber_2: '+4512345678' };
  for (const type of ['sort', 'filter', 'export', 'type']) {
    assert.equal(editor.renderCell(null, type, row, 'phone_number'), '+4512345678');
    assert.equal(editor.renderCell('test@example.com', type, row, 'email'), 'test@example.com');
    assert.equal(editor.renderCell(null, type, row, 'email'), '');
  }
});

test('display values and accessible labels escape user-controlled HTML', () => {
  const row = { id: 8, participant_id: 21, name: '"><img src=x onerror=alert(1)>' };
  const html = editor.renderCell('bad" onclick="alert(1)', 'display', row, 'email');
  assert.ok(!html.includes('<img'));
  assert.ok(!html.includes('href="mailto:bad"'));
  assert.match(html, /&quot;/);
});

test('key contacts retain the correct ID namespace and automatic lookup', () => {
  const row = { id: 8, uid: 'company-a', key_employee: { employee: { id: 21, enricher: 'internal', linkedin: 'https://linkedin.com/in/example' } } };
  assert.equal(editor.tableContact(row, 'key_employee.employee.email').target.participant_source, 'internal');
  const html = editor.renderCell(null, 'display', row, 'key_employee.employee.phone');
  assert.match(html, /checkFullEnrich/);
  assert.match(html, /Add phone/);
  row.key_employee.employee.enricher = 'unknown';
  assert.equal(editor.tableContact(row, 'key_employee.employee.email'), null);
});

test('company and person contact forms do not copy secondary phones into primary phone fields', () => {
  const row = { id: 8, company_name: 'Company', company_phone: '+4512345678', company_email: 'hello@example.com', participant_id: 21, phonenumber: '+4587654321', phonenumber_2: '+4599999999' };
  assert.equal(editor.tableContact(row, 'company_email').phone, '+4512345678');
  assert.equal(editor.tableContact(row, 'email').phone, '+4587654321');
});
