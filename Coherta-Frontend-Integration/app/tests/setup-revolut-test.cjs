const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const source = fs.readFileSync('app/setup-funnel.php', 'utf8');
function declaration(name) {
    const match = new RegExp('        (?:async )?function ' + name + '\\(').exec(source);
    assert.ok(match, name);
    return source.slice(match.index).match(/^[\s\S]*?\r?\n        }/)[0];
}
function load(context, names) {
    vm.runInContext(names.map(declaration).join('\n'), context);
}

async function main() {
    const events = [];
    const popup = {closed: false, location: {}, close() { this.closed = true; }};
    const context = vm.createContext({
        URL, URLSearchParams, Intl, Date,
        window: {location: {href: 'https://www.coherta.com/app/setup-funnel', search: '', origin: 'https://www.coherta.com'},
            setTimeout: callback => callback()},
        config: {companyId: '42', userId: '7', userEmail: 'test@example.test', selectedMarket: 'DK',
            paymentRequired: true, hasActiveSubscription: false, trialDays: 7},
        state: {userId: 7, userEmail: 'test@example.test', userSaved: true, accountType: 'business', pricingPlan: 'standard',
            billingCycle: 'monthly', pricingModel: {monthlyPrice: 999, annualMonthlyPrice: 899, currency: 'DKK', label: 'Standard'}},
        paymentEnabled: true, clearhausTestMode: false, talentAttractEnabled: false,
        talentAttractConfig: {}, prefilledAccountType: 'business', setupLocale: 'da', setupIntlLocale: 'da-DK',
        storedSetupProgress: {}, setupUserEmail: null,
        screens: ['account-type', 'payment', 'linkedin'].map(stepId => ({dataset: {stepId}})), activeIndex: 1,
        getSessionId: () => 'fixture-session', currentUserId: () => 7,
        applyAccountTypePricingRules() {}, normalizePricingPlan: value => value,
        normalizeBillingCycle: value => value, billingPeriodKey: value => value === 'yearly' ? 'year_1' : 'month_1',
        getBillingMonthlyPrice: (model, cycle) => cycle === 'yearly' ? model.annualMonthlyPrice : model.monthlyPrice,
        translateSetupText: (text, variables = {}) => text.replace(/\{(\w+)\}/g, (_, key) => variables[key] ?? key),
        setPaymentControlsBusy() {}, updateStepChrome() {}, setPaymentStatus: text => events.push(text),
        saveSetupProgress() {}, goToStep: index => events.push(['step', index]),
        findStepIndexById: id => ['account-type', 'payment', 'linkedin'].indexOf(id),
        isLikelyMobileCheckout: () => false, openPaymentWindowShell: () => popup,
        wait: async () => {}, cohertaApi: async () => [{id: 42, payment_subscription_active: false}]
    });
    load(context, ['isRevolutCheckoutUrl', 'subscriptionValueIsActive', 'payloadHasActiveSubscription',
        'paymentStepCanContinue', 'buildSetupReturnUrl', 'normalizeCheckoutCountryCode', 'checkoutApiPlan',
        'checkoutCurrencyLabel', 'formatCheckoutPrice', 'monthlyPriceDisplay', 'annualMonthlyPriceDisplay',
        'buildPaymentCheckoutPayload', 'headersJson', 'createPaymentCheckout', 'normalizePaymentReturnStatus',
        'paymentReturnStatus', 'nextStepAfterPaymentIndex', 'savePendingPaymentCheckout', 'clearPendingPaymentCheckout',
        'notifyOpenerAboutPaymentReturn', 'handlePaymentReturnOnLoad', 'handlePaymentCheckoutReturnMessage',
        'waitForSubscriptionActivation', 'openPaymentCheckout']);

    for (const url of ['https://checkout.revolut.com/pay/123', 'https://checkout.revolut.com/sandbox/pay/123']) {
        assert.equal(context.isRevolutCheckoutUrl(url), true);
    }
    for (const url of ['https://checkout.stripe.com/pay/123', 'https://revolut.com.example.test/',
        'https://evilrevolut.com/', 'http://checkout.revolut.com/', 'javascript:alert(1)',
        'https://user:pass@checkout.revolut.com/', '/checkout', undefined]) {
        assert.equal(context.isRevolutCheckoutUrl(url), false, String(url));
    }
    assert.equal(context.payloadHasActiveSubscription([{id: 99, payment_subscription_active: true}]), false);
    assert.equal(context.payloadHasActiveSubscription({data: [{id: 42, payment_subscription_active: true}]}), true);
    assert.equal(context.payloadHasActiveSubscription([{id: 42, stripe_subscription: 'sub_existing'}]), true);
    for (const state of ['pending', 'paused', 'overdue', 'cancelled']) {
        assert.equal(context.payloadHasActiveSubscription([{id: 42, payment_state: state,
            payment_subscription_id: 'rev_123', payment_subscription_active: false}]), false);
    }

    let payload = context.buildPaymentCheckoutPayload();
    assert.equal(payload.source, 'setup-funnel');
    assert.equal(payload.pricing, 999);
    assert.equal(payload.trial_days, 7);
    assert.equal(new URL(payload.success_url).searchParams.get('checkout_status'), 'success');
    context.state.billingCycle = 'yearly';
    payload = context.buildPaymentCheckoutPayload();
    assert.equal(payload.pricing, 899 * 12, 'Annual total is sent once');
    assert.equal(payload.quantity, 1);
    assert.equal(payload.billing_interval, 'year');
    context.config.trialDays = 0;
    assert.equal(context.buildPaymentCheckoutPayload().trial_days, 0, 'Zero trial must not become seven days');
    context.state.billingCycle = 'monthly';
    context.talentAttractEnabled = true;
    context.talentAttractConfig = {trialDays: 365, upfrontPrice: 750, subscriptionProductId: 'prod_fixture',
        subscriptionPriceId: 'price_fixture', upfrontProductId: 'prod_intro'};
    payload = context.buildPaymentCheckoutPayload();
    assert.equal(payload.offer_type, 'talent_attract');
    assert.equal(payload.trial_days, 365);
    assert.equal(payload.products[0], 'prod_intro');
    context.talentAttractEnabled = false;

    let request;
    context.fetch = async (url, options) => {
        request = {url, options};
        return {ok: true, json: async () => ({provider: 'revolut', url: 'https://checkout.revolut.com/pay/fixture'})};
    };
    await context.createPaymentCheckout(payload);
    assert.equal(request.url, 'https://api.coherta.com/billing/checkout');
    assert.equal(request.options.headers.Authorization, 'Bearer fixture-session');
    context.getSessionId = () => '';
    await assert.rejects(context.createPaymentCheckout(payload), /Log ind/);
    context.getSessionId = () => 'fixture-session';

    const realPoll = context.waitForSubscriptionActivation;
    let polls = 0;
    context.waitForSubscriptionActivation = () => { polls++; };
    context.window.location.search = '?payment_provider=revolut&checkout_reference=fixture';
    assert.equal(context.paymentReturnStatus(), 'success');
    context.handlePaymentReturnOnLoad();
    assert.equal(polls, 1);
    assert.equal(context.paymentStepCanContinue(), false, 'A success redirect cannot unlock the wizard');
    context.window.location.search = '?stripe=success';
    context.handlePaymentReturnOnLoad();
    assert.equal(context.paymentStepCanContinue(), false, 'Legacy return URLs also require verification');
    context.window.location.search = '?checkout_status=cancelled';
    context.handlePaymentReturnOnLoad();
    assert.equal(context.paymentStepCanContinue(), false);
    assert.equal(context.state.pendingPaymentCheckout, false);

    context.state.paymentCheckoutWindow = popup;
    const message = {origin: context.window.location.origin, source: popup,
        data: {type: 'COHERTA_PAYMENT_CHECKOUT_RETURN', status: 'success'}};
    const before = polls;
    context.handlePaymentCheckoutReturnMessage({...message, source: {}});
    context.handlePaymentCheckoutReturnMessage({...message, origin: 'https://example.test'});
    assert.equal(polls, before, 'Other windows cannot signal a checkout return');
    context.handlePaymentCheckoutReturnMessage(message);
    assert.equal(polls, before + 1);

    assert.equal(await context.openPaymentCheckout(null, {targetIndex: 2}), true);
    assert.equal(popup.location.href, 'https://checkout.revolut.com/pay/fixture', 'Desktop opens hosted checkout');
    context.state.checkoutOpening = false;
    context.isLikelyMobileCheckout = () => true;
    assert.equal(await context.openPaymentCheckout(null, {targetIndex: 2}), true);
    assert.equal(context.window.location.href, 'https://checkout.revolut.com/pay/fixture', 'Mobile redirects the current tab');
    context.state.checkoutOpening = false;
    context.fetch = async () => ({ok: true, json: async () => ({provider: 'stripe', url: 'https://checkout.stripe.com/pay/x'})});
    assert.equal(await context.openPaymentCheckout(null, {targetIndex: 2}), false, 'No silent Stripe fallback');
    assert.equal(context.paymentStepCanContinue(), false);
    context.fetch = async () => ({ok: false, status: 409, json: async () => ({message: 'Customer already has an active subscription'})});
    assert.equal(await context.openPaymentCheckout(null, {targetIndex: 2}), false, 'Conflict text alone is not proof of activation');

    context.waitForSubscriptionActivation = realPoll;
    let reads = 0;
    context.cohertaApi = async () => [{id: 42, payment_subscription_active: ++reads >= 3}];
    assert.equal(await context.waitForSubscriptionActivation({targetIndex: 2}), true);
    assert.equal(reads, 3, 'Delayed webhook activation is polled');
    assert.equal(context.paymentStepCanContinue(), true);
    assert.ok(events.some(event => Array.isArray(event) && event[1] === 2));

    // Evaluate the actual initialization expression with forged/stale browser progress.
    const initialization = source.match(/paymentConfirmed: ([^\r\n]+),/)[1];
    context.config.hasActiveSubscription = false;
    context.storedSetupProgress.paymentConfirmed = true;
    assert.equal(vm.runInContext(initialization, context), false);
    context.state.pendingPaymentTargetIndex = 2;
    context.findStepIndexById = id => ['account-type', 'linkedin'].indexOf(id);
    assert.equal(context.nextStepAfterPaymentIndex(), 1, 'An active return must not skip LinkedIn when PHP omits payment');
    const translations = JSON.parse(fs.readFileSync('app/setup-funnel-i18n.json', 'utf8'));
    for (const locale of ['uk', 'de', 'es', 'fi', 'fr', 'hr', 'is', 'no', 'pl', 'se']) {
        assert.match(translations['Fortsæt med Revolut'][locale], /Revolut/);
    }
    console.log('Revolut setup tests passed: prices, trials, returns, verified activation, company isolation, hosted URLs, desktop/mobile and translations.');
}
main().catch(error => { console.error(error); process.exitCode = 1; });
