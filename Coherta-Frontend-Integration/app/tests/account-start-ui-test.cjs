const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {test} = require('node:test');
const source = fs.readFileSync(path.join(__dirname, '../assets/js/start-account.js'), 'utf8');

function harness(response, companyId = '534') {
    const elements = new Map();
    const requests = [];
    const redirects = [];
    let reloads = 0;
    function element(id) {
        if (!elements.has(id)) elements.set(id, {textContent: 'Start my account', hidden: true, disabled: false,
            tagName: 'DIV', inert: false, removed: false, handlers: {},
            getAttribute: () => companyId, setAttribute() {}, focus() {},
            addEventListener(name, callback) {this.handlers[name] = callback;},
            remove() {this.removed = true;}});
        return elements.get(id);
    }
    const modal = element('account-start-modal');
    const page = element('page');
    vm.runInNewContext(source, {document: {getElementById: element, body: {children: [modal, page], getAttribute: () => 'fixture'}},
        window: {location: {search: '', assign: url => redirects.push(url), reload: () => reloads++}},
        URL, URLSearchParams, AbortController, setTimeout, clearTimeout,
        fetch: async (url, options) => {requests.push({url, body: JSON.parse(options.body)}); return {ok: true, json: async () => response};}});
    return {element, requests, redirects, reloads: () => reloads, click: () => element('paymentInfoStart').handlers.click()};
}

const paid = {source: 'crm_company_stripe', company_id: 534, active: true, subscription_id: 'sub_existing', stripe_renewal_current: true};
test('a stale start modal checks the displayed company and dismisses for its existing paid subscription', async () => {
    const h = harness({subscription: paid});
    await h.click();
    assert.deepEqual(h.requests, [{url: 'https://api.coherta.com/users/me/subscription_center?view=gate&company_id=534', body: {action: 'start'}}]);
    assert.equal(h.element('account-start-modal').removed, true);
    assert.equal(h.element('page').inert, false);
    assert.equal(h.reloads(), 1);
    assert.deepEqual(h.redirects, []);
});

test('another company or unverified status cannot dismiss the start modal', async () => {
    for (const change of [{company_id: 1}, {active: false}, {stripe_renewal_current: false}, {subscription_id: ''}]) {
        const h = harness({subscription: {...paid, ...change}});
        await h.click();
        assert.equal(h.element('account-start-modal').removed, false);
        assert.equal(h.reloads(), 0);
        assert.equal(h.element('paymentInfoStart').disabled, false);
        assert.deepEqual(h.redirects, []);
    }
});

test('unpaid accounts still open verified Revolut checkout', async () => {
    const h = harness({checkout: {provider: 'revolut', url: 'https://checkout.revolut.com/pay/fixture'}});
    await h.click();
    assert.deepEqual(h.redirects, ['https://checkout.revolut.com/pay/fixture']);
    assert.equal(h.reloads(), 0);
});

test('a modal without its company ID requests a reload instead of selecting another account', async () => {
    const h = harness({}, '');
    await h.click();
    assert.equal(h.requests.length, 0);
    assert.equal(h.element('paymentInfoError').textContent, 'Reload the page to confirm your account.');
});
