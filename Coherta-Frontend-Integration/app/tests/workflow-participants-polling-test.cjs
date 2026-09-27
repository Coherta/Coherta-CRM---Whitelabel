const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('app/campaign/campaigns-workflow-builder.php', 'utf8');

for (const match of source.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)) {
    new vm.Script(match[1].replace(/<\?php[\s\S]*?\?>/g, 'null'));
}
function declaration(name) {
    const start = source.search(new RegExp('^        (?:async )?function ' + name + '\\(', 'm'));
    assert.ok(start >= 0, name);
    return source.slice(start).split(/\r?\n(?=        (?:async )?function |        const |        let )/)[0];
}
function createNode(id) {
    return {
        dataset: {id}, badge: null,
        querySelector() { return this.badge; },
        prepend(badge) { this.badge = badge; badge.parent = this; }
    };
}
function createBadge() {
    const children = {span: {}, small: {}};
    return {
        dataset: {}, attributes: {},
        querySelector(selector) { return children[selector]; },
        setAttribute(name, value) { this.attributes[name] = value; },
        remove() { this.parent.badge = null; }
    };
}
const nodes = [createNode('first'), createNode('second')];
const requests = [], intervals = new Map(), timeouts = new Map();
let timerId = 0, modalRenders = 0, panelRenders = 0;
const badgeClasses = new Set();
const context = vm.createContext({
    campaignId: 42, AbortController,
    setInterval(callback, delay) { const id = ++timerId; intervals.set(id, {callback, delay}); return id; },
    clearInterval(id) { intervals.delete(id); },
    setTimeout(callback, delay) { const id = ++timerId; timeouts.set(id, {callback, delay}); return id; },
    clearTimeout(id) { timeouts.delete(id); },
    api: {request(path, options) {
        return new Promise((resolve, reject) => requests.push({path, options, resolve, reject}));
    }},
    document: {createElement: createBadge},
    nodesLayer: {querySelectorAll: () => nodes},
    peopleModalBackdrop: {classList: {contains: () => true}},
    peopleModalBody: {scrollTop: 120},
    peopleModalSearchInput: {value: 'Example'},
    wfPeopleStatusBadge: {classList: {toggle(name, enabled) {
        if (enabled) badgeClasses.add(name); else badgeClasses.delete(name);
    }}},
    renderSelectedPanel() { panelRenders++; },
    renderPeopleModal() { modalRenders++; context.peopleModalBody.scrollTop = 0; },
    render() { assert.fail('A participant refresh must not rebuild the workflow canvas'); }
});
const state = source.split(/\r?\n/).filter(line => /^        (?:let workflowContacts[=R]|const WORKFLOW_CONTACTS_POLL_MS=)/.test(line));
assert.equal(state.length, 3);
vm.runInContext(state.join('\n') + '\n' + [
    'extractWorkflowContacts', 'getWorkflowContactValue', 'getWorkflowLeadId', 'normalizeWorkflowContact',
    'rebuildWorkflowContactsIndex', 'getContactsForNode', 'formatNodeContactCount', 'peopleLabel',
    'setPeopleStatusBadge', 'refreshWorkflowContactBadges', 'loadWorkflowContacts',
    'startWorkflowContactsPolling', 'stopWorkflowContactsPolling'
].map(declaration).join('\n'), context);
const evaluate = code => vm.runInContext(code, context);
const payload = node => ({contacts: [{participant_id: 7, name: 'Example', current_node_id: node}]});
const settle = () => new Promise(resolve => setImmediate(resolve));

(async () => {
    let pending = context.loadWorkflowContacts();
    assert.equal(evaluate('workflowContactsLoading'), true);
    requests.at(-1).resolve(payload('first'));
    await pending;
    assert.equal(nodes[0].badge.querySelector('span').textContent, '1');
    assert.equal(nodes[1].badge, null);
    assert.equal(timeouts.size, 0);

    context.startWorkflowContactsPolling();
    context.startWorkflowContactsPolling();
    assert.equal(intervals.size, 1, 'Polling starts only once');
    const poll = [...intervals.values()][0];
    assert.equal(poll.delay, 30000);
    const existingBadge = nodes[0].badge;
    const rendersBefore = modalRenders;
    poll.callback();
    assert.equal(evaluate('workflowContactsLoading'), false, 'Background requests do not enable loading UI');
    assert.equal(badgeClasses.has('loading'), false);
    assert.equal(modalRenders, rendersBefore, 'The people list remains usable while checking');
    assert.equal(nodes[0].badge, existingBadge);
    assert.equal(requests.at(-1).options.cache, 'no-store');
    assert.match(requests.at(-1).path, /campaign_id=42&id=42$/);
    const countBefore = requests.length;
    poll.callback();
    assert.equal(requests.length, countBefore, 'Slow polling requests cannot overlap');
    context.peopleModalBody.scrollTop = 120;
    requests.at(-1).resolve(payload('second'));
    await settle();
    assert.equal(nodes[0].badge, null, 'The old node loses its participant badge');
    assert.equal(nodes[1].badge.querySelector('span').textContent, '1');
    assert.equal(nodes[1].badge.dataset.action, 'view-contacts');
    assert.equal(context.getContactsForNode('second')[0].participantId, '7');
    assert.equal(context.peopleModalBody.scrollTop, 120);
    assert.equal(context.peopleModalSearchInput.value, 'Example');

    const rendersAfterMove = [modalRenders, panelRenders];
    poll.callback();
    requests.at(-1).resolve(payload('second'));
    await settle();
    assert.deepEqual([modalRenders, panelRenders], rendersAfterMove, 'Unchanged participants do not redraw UI');
    poll.callback();
    requests.at(-1).reject(new Error('Temporary network failure'));
    await settle();
    assert.equal(context.getContactsForNode('second').length, 1, 'Failed checks preserve known positions');
    assert.equal(evaluate('workflowContactsError'), '');
    assert.deepEqual([modalRenders, panelRenders], rendersAfterMove);

    poll.callback();
    const stale = requests.at(-1);
    pending = context.loadWorkflowContacts(true);
    const latest = requests.at(-1);
    assert.equal(stale.options.signal.aborted, true, 'Manual refresh supersedes an in-flight poll');
    stale.resolve(payload('first'));
    await settle();
    assert.equal(evaluate('workflowContactsLoading'), true, 'A stale response cannot clear newer request state');
    assert.equal(context.getContactsForNode('second').length, 1);
    latest.resolve(payload('first'));
    await pending;
    assert.equal(context.getContactsForNode('first').length, 1);

    evaluate("workflowContactActionKey='approving-participant'");
    const requestsBeforeAction = requests.length;
    poll.callback();
    assert.equal(requests.length, requestsBeforeAction, 'Polling waits for participant actions');
    evaluate("workflowContactActionKey=''");

    poll.callback();
    const timedOut = requests.at(-1);
    const timeout = [...timeouts.values()][0];
    assert.ok(timeout.delay < poll.delay);
    timeout.callback();
    assert.equal(timedOut.options.signal.aborted, true);
    timedOut.reject(new Error('Aborted'));
    await settle();
    poll.callback();
    assert.notEqual(requests.at(-1), timedOut, 'The next check retries after a timeout');
    const stopped = requests.at(-1);
    context.stopWorkflowContactsPolling();
    assert.equal(intervals.size, 0);
    assert.equal(stopped.options.signal.aborted, true);
    stopped.resolve(payload('second'));
    await settle();
    assert.equal(context.getContactsForNode('first').length, 1, 'A stopped request cannot apply results');
    assert.equal(timeouts.size, 0);
    context.startWorkflowContactsPolling();
    assert.equal(intervals.size, 1, 'Polling resumes when returning to the page');
    context.stopWorkflowContactsPolling();
    context.campaignId = 0;
    context.startWorkflowContactsPolling();
    const requestsBeforeMissingId = requests.length;
    await context.loadWorkflowContacts();
    assert.equal(intervals.size, 0);
    assert.equal(requests.length, requestsBeforeMissingId);

    console.log('PASS: 30-second background polling, in-place participant movement, unchanged UI, failure recovery, request races, timeout, and lifecycle cleanup');
})().catch(error => { console.error(error); process.exitCode = 1; });
