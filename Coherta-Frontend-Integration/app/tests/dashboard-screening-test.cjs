const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('app/campaign/campaigns-dashboard.php', 'utf8');
for (const match of source.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)) {
    new vm.Script(match[1].replace(/<\?php[\s\S]*?\?>/g, 'null'));
}
function declaration(name) {
    const start = source.search(new RegExp('            (?:async )?function ' + name + '\\('));
    assert.ok(start >= 0, name);
    return source.slice(start).split(/\r?\n(?=            (?:async )?function )/)[0];
}
const elements = () => ({textContent: '', value: '', innerHTML: '', style: {}, querySelectorAll: () => []});
const table = elements(), head = elements();
let requests = [], campaignContacts = [];
const context = vm.createContext({
    window: {},
    esc: value => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;'),
    attachedContactsTableBody: table, attachedContactsTableHead: head,
    attachedContactsModal: {}, attachedContactsOpportunityFilter: elements(),
    attachedContactsCampaignIdInput: elements(), attachedContactsModalTitle: elements(), attachedContactsModalSubtitle: elements(),
    attachedContactsRequestId: 0, attachedContactsCurrentCampaignId: null, attachedContactsCurrentList: [],
    api: {request: url => new Promise((resolve, reject) => requests.push({url, resolve, reject}))},
    getCampaignById: () => ({title: 'Campaign', attached_contacts_data: [{id: 'stale'}]}),
    getAttachedContactsFromCampaign: () => { throw new Error('Must refresh screening results'); },
    setAttachedContactsForCampaign: (id, contacts) => { campaignContacts = contacts; context.attachedContactsCurrentList = contacts; },
    renderCurrentAttachedContacts: message => context.renderAttachedContacts(campaignContacts, message),
    openModal: () => {},
    isOpportunityPaused: contact => contact.completed === 2,
    isContactComplete: contact => contact.completed === 1,
    formatContactName: contact => context.esc(context.getContactFullName(contact))
});
vm.runInContext(fs.readFileSync('app/campaign/screening-results.js', 'utf8'), context);
vm.runInContext([
    'getContactFullName', 'getContactIdentifier', 'getContactTitle', 'getContactCompany', 'getContactLinkedin',
    'getFilteredAttachedContacts', 'setContactsTableHead', 'renderContactScreening', 'renderAttachedContacts',
    'normalizeAttachedContacts', 'openAttachedContacts'
].map(declaration).join('\n'), context);
const contacts = [
    {id: 1, full_name: 'Accepted', completed: 0, automated_screening_score: 90, automated_screening_branch: 'Match'},
    {id: 2, full_name: 'Declined', completed: 1, automated_screening_score: 0, automated_screening_branch: 'No Match'},
    {id: 3, full_name: 'Not visited', completed: 2},
    {id: 4, full_name: '<script>unsafe</script>', automated_screening_score: 88}
];
context.renderAttachedContacts(contacts);
assert.equal((head.innerHTML.match(/<th>/g) || []).length, 8);
assert.equal((table.innerHTML.match(/<td>/g) || []).length, 32);
assert.ok(table.innerHTML.includes('0 / 100'));
assert.ok(table.innerHTML.includes('Declined'));
assert.ok(table.innerHTML.includes('Accepted'));
assert.ok(table.innerHTML.includes('Not screened'));
assert.ok(!table.innerHTML.includes('<script>'));
assert.ok(context.renderContactScreening(contacts[1]).includes('data-screening-contact-id="2"'));
context.attachedContactsOpportunityFilter.value = 'completed';
context.renderAttachedContacts(contacts);
assert.ok(table.innerHTML.includes('Declined'));
assert.ok(!table.innerHTML.includes('Accepted'));

(async () => {
    const first = context.openAttachedContacts(1);
    assert.equal(requests[0].url, '/campaigns/workflow/attached?campaign_id=1&scope=all');
    const second = context.openAttachedContacts(2);
    requests[1].resolve({contacts});
    await second;
    requests[0].resolve({contacts: [{id: 99, full_name: 'Stale response'}]});
    await first;
    assert.equal(campaignContacts, contacts, 'An older campaign response must not replace the current campaign');
    const refresh = context.openAttachedContacts(2);
    requests[2].resolve({contacts: []});
    await refresh;
    assert.equal(campaignContacts.length, 0, 'An empty response must clear cached results');
    assert.ok(table.innerHTML.includes('colspan="8"'));
    const failure = context.openAttachedContacts(2);
    requests[3].reject(new Error('offline'));
    await failure;
    assert.ok(table.innerHTML.includes('Could not load attached contacts'));
    console.log('PASS: dashboard syntax, scores/decisions, completed leads, escaping, fresh data, empty/error states, and response ordering');
})().catch(error => { console.error(error); process.exitCode = 1; });
