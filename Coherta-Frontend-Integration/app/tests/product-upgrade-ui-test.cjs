const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const {test} = require('node:test');
const source = fs.readFileSync(require('node:path').join(__dirname, '../assets/js/product-upgrade.js'), 'utf8');
const tick = () => new Promise(resolve => setImmediate(resolve));
const offer = {mode: 'checkout', label: 'Website scraping', quote_id: 'quote', amount_minor: 15000, currency: 'DKK', duration: 'P1M', trial_days: 14};
function harness(handle, search = '?product=Tj6iHmP3XyIEdS') {
    const elements = new Map(), requests = [], navigations = [];
    const el = id => {
        if (!elements.has(id)) elements.set(id, {textContent: '', disabled: false, hidden: false, handlers: {}, setAttribute() {}, addEventListener(type, fn) { this.handlers[type] = fn; }});
        return elements.get(id);
    };
    vm.runInNewContext(source, {document: {getElementById: el, body: {getAttribute: () => 'test-token'}},
        location: {search, assign: url => navigations.push(url)}, URL, URLSearchParams, AbortController, setTimeout, clearTimeout, Intl,
        fetch: async (url, options) => {
            const body = options.body ? JSON.parse(options.body) : null;
            requests.push({url, body});
            const result = await handle(body, requests.length);
            return {ok: !result.error, json: async () => result.error ? {detail: result.error} : result};
        }});
    return {get: id => el('upgrade-' + id), click: id => el('upgrade-' + id).handlers.click(), requests, navigations};
}
test('old upgrade=true URLs only display a quote; checkout requires an explicit click', async () => {
    const h = harness(body => body ? {checkout: {provider: 'revolut', url: 'https://checkout.revolut.com/example'}} : {quote: offer}, '?product=Tj6iHmP3XyIEdS&upgrade=true');
    await tick();
    assert.equal(h.requests.length, 1); assert.equal(h.requests[0].body, null);
    assert.match(h.get('terms').textContent, /14 days/);
    await h.click('confirm');
    assert.deepEqual(h.requests[1].body, {flow: 'upgrade', product: 'Tj6iHmP3XyIEdS', action: 'checkout', quote_id: 'quote'});
    assert.deepEqual(h.navigations, ['https://checkout.revolut.com/example']);
});
test('existing Revolut subscriptions show and schedule next-cycle changes', async () => {
    const h = harness(body => body ? {scheduled: true, message: 'Scheduled'} : {quote: {...offer, mode: 'change', trial_days: 0}});
    await tick(); assert.equal(h.get('confirm').textContent, 'Schedule upgrade');
    assert.match(h.get('terms').textContent, /next renewal/);
    await h.click('confirm'); await h.click('confirm');
    assert.equal(h.requests.length, 2); assert.equal(h.get('confirm').disabled, true);
    assert.deepEqual(h.navigations, []);
});
test('included products cannot be purchased again', async () => {
    const h = harness(() => ({quote: {...offer, mode: 'included', quote_id: undefined}}));
    await tick(); assert.equal(h.get('confirm').disabled, true);
});
test('unavailable pricing stays disabled and explains the error', async () => {
    const h = harness(() => ({error: 'A price is not configured.'}));
    await tick(); assert.equal(h.get('confirm').disabled, true);
    assert.equal(h.get('message').textContent, 'A price is not configured.');
});
test('return verification cannot start another checkout while payment is pending', async () => {
    const h = harness(() => ({paid: false}), '?product=Tj6iHmP3XyIEdS&checkout_reference=' + 'a'.repeat(32));
    await tick(); await h.click('refresh');
    assert.equal(h.get('confirm').disabled, true);
    assert.deepEqual(h.requests.map(r => r.body.action), ['status', 'status']);
});
test('verified returns display activation without creating payment', async () => {
    const h = harness(() => ({paid: true}), '?checkout_reference=' + 'a'.repeat(32));
    await tick(); assert.equal(h.get('title').textContent, 'Your subscription is active');
    assert.equal(h.get('confirm').disabled, true);
});
test('concurrent clicks submit one checkout', async () => {
    let resolve;
    const h = harness(body => body ? new Promise(done => {resolve = done;}) : {quote: offer});
    await tick(); const first = h.click('confirm'); await h.click('confirm');
    assert.equal(h.requests.length, 2);
    resolve({checkout: {provider: 'revolut', url: 'https://pay.revolut.com/example'}}); await first;
});
test('Stripe and unsafe checkout redirects are rejected', async () => {
    for (const checkout of [
        {provider: 'stripe', url: 'https://checkout.stripe.com/example'},
        {provider: 'revolut', url: 'https://checkout.revolut.com.attacker.test/'},
        {provider: 'revolut', url: 'https://user:pass@checkout.revolut.com/'},
        {provider: 'revolut', url: 'http://checkout.revolut.com/'},
    ]) {
        const h = harness(body => body ? {checkout} : {quote: offer});
        await tick(); await h.click('confirm'); assert.equal(h.navigations.length, 0);
        assert.match(h.get('message').textContent, /secure Revolut/);
    }
});
