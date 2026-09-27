(function () {
    'use strict';
    const modal = document.getElementById('account-start-modal');
    if (!modal) return;
    const companyId = Number(modal.getAttribute('data-company-id'));
    const button = document.getElementById('paymentInfoStart');
    const startButtonLabel = button.textContent;
    const errorMessage = document.getElementById('paymentInfoError');
    const copy = document.getElementById('paymentInfoCopy');
    const inactiveElements = [];
    let busy = false;
    let retryConfirmation = false;
    for (const child of document.body.children) {
        if (child !== modal && !child.inert && !['SCRIPT', 'STYLE'].includes(child.tagName)) {
            child.inert = true;
            inactiveElements.push(child);
        }
    }
    button.focus();

    function setBusy(value, label) {
        busy = value;
        button.disabled = value;
        button.textContent = value ? label : (retryConfirmation ? 'Check status' : startButtonLabel);
        modal.setAttribute('aria-busy', String(value));
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.hidden = !message;
    }

    async function request(body) {
        const token = document.body.getAttribute('session_id');
        if (!token) throw new Error('Your session has expired. Please sign in again.');
        if (!Number.isSafeInteger(companyId) || companyId <= 0) throw new Error('Reload the page to confirm your account.');
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 60000);
        try {
            const response = await fetch('https://api.coherta.com/users/me/subscription_center?view=gate&company_id=' + companyId, {
                method: 'POST', cache: 'no-store', signal: controller.signal,
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token},
                body: JSON.stringify(body)
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.detail || 'Unable to start your account. Please try again.');
            return data;
        } catch (error) {
            if (error.name === 'AbortError') throw new Error('The request timed out. Try again to resume your checkout.');
            if (error instanceof SyntaxError || error instanceof TypeError) throw new Error('Unable to connect to the payment service. Please try again.');
            throw error;
        } finally {
            clearTimeout(timer);
        }
    }

    button.addEventListener('click', async () => {
        if (busy) return;
        if (retryConfirmation) { confirmReturn(); return; }
        showError('');
        setBusy(true, 'Starting account…');
        try {
            const data = await request({action: 'start'});
            const subscription = data.subscription;
            if (subscription && subscription.source === 'crm_company_stripe'
                && subscription.company_id === companyId && subscription.active === true
                && subscription.stripe_renewal_current === true
                && typeof subscription.subscription_id === 'string' && subscription.subscription_id.trim() !== '') {
                inactiveElements.forEach(element => { element.inert = false; });
                modal.remove();
                window.location.reload();
                return;
            }
            const checkout = data.checkout;
            const url = new URL(checkout && checkout.url);
            if (!checkout || checkout.provider !== 'revolut' || url.protocol !== 'https:'
                || !(url.hostname === 'revolut.com' || url.hostname.endsWith('.revolut.com')) || url.username || url.password) {
                throw new Error('Unable to open checkout. Please try again.');
            }
            window.location.assign(url.href);
        } catch (error) {
            showError(error.message);
            setBusy(false);
        }
    });

    modal.addEventListener('keydown', event => {
        if (event.key !== 'Tab') return;
        const focusable = [...modal.querySelectorAll('button:not(:disabled), a[href]')];
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });

    const query = new URLSearchParams(window.location.search);
    function confirmReturn() {
        showError('');
        setBusy(true, 'Confirming subscription…');
        request({action: 'checkout_status', checkout_reference: query.get('checkout_reference')})
            .then(data => {
                retryConfirmation = false;
                const subscription = data.subscription;
                if (subscription && subscription.source === 'hubspot' && subscription.active === true
                    && typeof subscription.subscription_id === 'string' && subscription.subscription_id.trim() !== '') {
                    inactiveElements.forEach(element => { element.inert = false; });
                    modal.remove();
                    const url = new URL(window.location.href);
                    url.searchParams.delete('payment_provider');
                    url.searchParams.delete('checkout_reference');
                    window.location.replace(url.href);
                } else {
                    copy.textContent = 'Your subscription is not active yet. Continue to finish setting up your account.';
                }
            })
            .catch(error => { retryConfirmation = true; showError(error.message); })
            .finally(() => setBusy(false));
    }
    if (query.get('payment_provider') === 'revolut' && query.has('checkout_reference')) confirmReturn();
})();
