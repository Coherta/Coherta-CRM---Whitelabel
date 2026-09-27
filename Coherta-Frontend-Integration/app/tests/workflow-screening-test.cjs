const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('app/campaign/campaigns-workflow-builder.php', 'utf8');
for (const match of source.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)) {
    new vm.Script(match[1].replace(/<\?php[\s\S]*?\?>/g, 'null'));
}
function declaration(name) {
    const start = source.indexOf('        function ' + name + '(');
    assert.ok(start >= 0, name);
    // Functions in this file have an eight-space top-level indent.
    return source.slice(start).split(/\r?\n(?=        (?:async )?function |        const |        let )/)[0];
}
const elements = new Map();
const element = id => {
    if (!elements.has(id)) elements.set(id, {textContent: '', dataset: {}, hidden: false, addEventListener() {}});
    return elements.get(id);
};
const dialog = {
    showModal() { this.open = true; }, close() { this.open = false; },
    setAttribute() {}, addEventListener() {}, querySelector: selector => element(selector.slice(1))
};
const context = vm.createContext({
    window: {}, document: {createElement: () => dialog, body: {appendChild() {}}},
    workflowContacts: [], peopleModalNodeId: 'after-screening', workflowContactActionKey: '',
    getNodeById: () => ({type: 'wait'})
});
vm.runInContext(fs.readFileSync('app/campaign/screening-results.js', 'utf8'), context);
vm.runInContext([
    'escapeHtml', 'getWorkflowContactValue', 'getWorkflowLeadId', 'getWorkflowScreeningResult',
    'normalizeWorkflowContact', 'safeExternalUrl', 'getWorkflowContactImageUrl',
    'getWorkflowContactInitials', 'renderPeopleAvatar', 'getWorkflowContactKey',
    'renderWorkflowContactRow', 'openScreeningResult'
].map(declaration).join('\n'), context);

for (const raw of [{}, {automated_screening_score: null}, {automated_screening_score: ''}, {current_action: 'icp_screening'}, {automated_screening_score: 'invalid'}]) {
    assert.equal(context.getWorkflowScreeningResult(raw), null);
}
const raw = {
    id: 7, participant_id: 100, name: 'Example Lead', linkedin_url: 'https://www.linkedin.com/in/example',
    automated_screening_score: '0', automated_screening_reason: 'Wrong role.\n<script>bad()</script>',
    automated_screening_branch: 'No Match', automated_screening_threshold: 70
};
assert.equal(context.getWorkflowScreeningResult(raw).score, 0);
assert.equal(context.getWorkflowScreeningResult(raw).decision, 'declined');
assert.equal(context.getWorkflowScreeningResult({...raw, automated_screening_score: 70, automated_screening_branch: ''}).decision, 'accepted');
assert.equal(context.getWorkflowScreeningResult({...raw, automated_screening_score: 99}).decision, 'declined', 'Recorded branch takes precedence');
assert.equal(context.getWorkflowScreeningResult({automated_screening_score: 90}).decision, '', 'No guessed threshold');
assert.equal(context.getWorkflowScreeningResult({participant: raw}).score, 0);
assert.equal(context.getWorkflowScreeningResult({automated_screening_score: 0, automated_screening_threshold: 0}).decision, 'accepted');

const contact = context.normalizeWorkflowContact(raw);
context.workflowContacts.push(contact);
const row = context.renderWorkflowContactRow(contact);
assert.ok(row.indexOf('wf-screening-result-button') > row.indexOf('class="wf-people-link"'), 'Icon is after LinkedIn');
assert.ok(row.includes('data-screening-contact-index="0"'));
assert.ok(!context.renderWorkflowContactRow(context.normalizeWorkflowContact({name: 'Unscreened'})).includes('wf-screening-result-button'));
assert.ok(context.renderWorkflowContactRow({...contact, linkedinUrl: ''}).includes('wf-screening-result-button'), 'Screened leads without LinkedIn still show results');
context.openScreeningResult(contact);
assert.equal(dialog.open, true);
assert.equal(element('screeningResultScore').textContent, '0 / 100');
assert.equal(element('screeningResultDecision').textContent, 'Declined');
assert.equal(element('screeningResultReason').textContent, raw.automated_screening_reason, 'Reason is plain text, not HTML');
assert.equal(element('screeningResultThreshold').hidden, false);
context.openScreeningResult(context.normalizeWorkflowContact({name: 'Older Lead', automated_screening_score: 88}));
assert.equal(element('screeningResultThreshold').hidden, true);
assert.equal(element('screeningResultReason').textContent, 'No reason was recorded for this screening.');
assert.equal(element('screeningResultDecision').textContent, 'Completed');
console.log('PASS: JavaScript syntax, result detection, recorded decisions, zero scores, icon placement, and safe popup content');
