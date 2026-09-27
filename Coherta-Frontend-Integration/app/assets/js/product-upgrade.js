(() => {
    'use strict';
    const root = document.getElementById('product-upgrade');
    if (!root) return;
    const el = name => document.getElementById('upgrade-' + name);
    const query = new URLSearchParams(location.search);
    const product = query.get('product') || '';
    const reference = query.get('checkout_reference') || '';
    let quote = null;
    let busy = false;
    let completed = false;
    function message(text) { el('message').textContent = text; el('message').hidden = !text; }
    async function api(data = null) {
        const token = document.body.getAttribute('session_id');
        if (!token) throw new Error('Please sign in again.');
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 45000);
        try {
            const response = await fetch('https://api.coherta.com/users/me/billing' + (data ? '' : '?' + new URLSearchParams({flow: 'upgrade', product})), {
                method: data ? 'POST' : 'GET', signal: controller.signal,
                headers: {Accept: 'application/json', Authorization: 'Bearer ' + token, ...(data ? {'Content-Type': 'application/json'} : {})},
                ...(data ? {body: JSON.stringify({flow: 'upgrade', product, ...data})} : {})
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.detail || 'Unable to confirm billing. Please retry.');
            return result;
        } finally { clearTimeout(timeout); }
    }
    async function run(work) {
        if (busy) return;
        busy = true;
        root.setAttribute('aria-busy', 'true');
        el('confirm').disabled = true;
        el('refresh').disabled = true;
        try { await work(); } catch (error) { message(error.name === 'AbortError' ? 'The request is taking longer than expected. Refresh to check its status.' : error.message); }
        finally {
            busy = false;
            root.setAttribute('aria-busy', 'false');
            el('refresh').disabled = false;
            el('confirm').disabled = completed || !quote || quote.mode === 'included';
        }
    }
    async function refresh() {
        quote = null;
        message('');
        if (reference) {
            const status = await api({action: 'status', checkout_reference: reference});
            if (status.paid) {
                completed = true;
                el('title').textContent = 'Your subscription is active';
                el('terms').textContent = 'Your Revolut checkout is confirmed. You can return to your account.';
                return;
            }
            message('Your Revolut subscription is still being confirmed. Complete checkout, then refresh this page.');
            return;
        }
        quote = (await api()).quote;
        el('title').textContent = quote.label;
        if (quote.mode === 'included') {
            el('terms').textContent = 'This product is already included in your subscription.';
            return;
        }
        el('price').textContent = new Intl.NumberFormat('en', {style: 'currency', currency: quote.currency}).format(quote.amount_minor / 100);
        el('period').textContent = ({P1M: 'Monthly', P3M: 'Every 3 months', P6M: 'Every 6 months', P1Y: 'Yearly', P12M: 'Yearly'})[quote.duration] || quote.duration;
        el('confirm').textContent = quote.mode === 'change' ? 'Schedule upgrade' : 'Continue with Revolut';
        el('terms').textContent = quote.mode === 'change'
            ? 'Your current plan continues until the end of this billing cycle. The new total and added product take effect at the next renewal. No charge is taken now.'
            : (quote.trial_days > 0 ? `Your first payment is due after ${quote.trial_days} days. ` : 'Your first payment is due when checkout is completed. ')
                + 'The total includes your existing plan and the selected product.';
    }
    el('refresh').addEventListener('click', () => run(refresh));
    el('confirm').addEventListener('click', () => run(async () => {
        if (!quote || completed || quote.mode === 'included') return;
        const result = await api({action: 'checkout', quote_id: quote.quote_id});
        if (result.scheduled) { completed = true; message(result.message); return; }
        const checkout = result.checkout;
        const url = new URL(checkout.url);
        if (checkout.provider !== 'revolut' || url.protocol !== 'https:' || url.username || url.password || url.port
            || !['checkout.revolut.com', 'sandbox-checkout.revolut.com', 'pay.revolut.com', 'sandbox-pay.revolut.com'].includes(url.hostname)) {
            throw new Error('Unable to open secure Revolut checkout. Please contact support.');
        }
        location.assign(url.href);
    }));
    run(refresh);
})();
