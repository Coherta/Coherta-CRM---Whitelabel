const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
require('../assets/js/setup-clearhaus-test.js');
const checkout = globalThis.CohertaClearhausTest;
const standard = {
    plan: 'standard', label: 'Coherta Standard', billingCycle: 'monthly',
    monthlyPrice: 999, currency: 'DKK',
    startDate: '2026-09-21', identity: [123, 'test@example.test']
};
const monthly = checkout.buildOffer(standard);
assert.equal(monthly.initialPrice, 999, 'The first month is payable upfront');
assert.equal(monthly.recurringPrice, 999);
assert.equal(monthly.firstBillingDate, '2026-09-21');
assert.match(monthly.firstBillingDateText, /21\. september 2026 \(ved godkendelse\)/);
assert.match(monthly.recurringText, /999,00 DKK månedligt/);
assert.equal(checkout.approve(monthly, false, new Date()), null, 'Consent is required');
const receipt = checkout.approve(monthly, true, new Date('2026-09-21T12:00:00Z'));
assert.equal(receipt.cardLast4, '4242');
assert.ok(checkout.isApproved(monthly, JSON.parse(JSON.stringify(receipt))), 'Approval survives a session reload');

const annual = checkout.buildOffer({...standard, billingCycle: 'yearly'});
assert.equal(annual.recurringPrice, 11988, 'Annual payment shows the whole year, not just its monthly equivalent');
assert.equal(annual.initialPrice, 11988, 'The whole first year is payable upfront');
assert.equal(annual.firstBillingDate, standard.startDate);
assert.match(annual.recurringText, /11.988,00 DKK årligt/);
assert.ok(!checkout.isApproved(annual, receipt), 'Changing billing requires new consent');
for (const changes of [{plan: 'extended', monthlyPrice: 4198}, {currency: 'EUR'}, {identity: [456, 'other@example.test']}]) {
    assert.ok(!checkout.isApproved(checkout.buildOffer({...standard, ...changes}), receipt), 'Approval must match the offer and customer');
}
for (const legacyOffer of [{trialDays: 7}, {trialDays: 30}, {talentAttract: true, trialDays: 365, upfrontPrice: 750}]) {
    const offer = checkout.buildOffer({...standard, ...legacyOffer});
    assert.equal(offer.initialPrice, 999, 'Legacy trial and introductory offers cannot reduce upfront payment');
    assert.equal(offer.firstBillingDate, standard.startDate, 'Legacy trial settings cannot postpone billing');
    assert.equal(offer.trialText, undefined);
}
const previousTrialReceipt = {...receipt, offerKey: JSON.stringify(['2026-09-21-v1.2', standard.identity, 'standard', 'monthly', 999, 'DKK', 7, false, 0])};
assert.ok(!checkout.isApproved(monthly, previousTrialReceipt), 'A previously approved trial must require approval of the new upfront amount');
for (const startDate of ['2028-02-29', '2026-10-25']) {
    assert.equal(checkout.buildOffer({...standard, startDate}).firstBillingDate, startDate, 'First payment stays on the start date across leap days and daylight-saving transitions');
}
for (const options of [{plan: 'custom', monthlyPrice: 0}, {monthlyPrice: NaN}, {monthlyPrice: -1}]) {
    const offer = checkout.buildOffer({...standard, ...options});
    assert.equal(checkout.approve(offer, true, new Date()), null, 'Unpriced offers cannot be approved');
}

// Exercise the real wizard's payment gate and approval handler with observable boundaries.
const source = fs.readFileSync('app/setup-funnel.php', 'utf8');
for (const script of source.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)) {
    new vm.Script(script[1].replace(/<\?php[\s\S]*?\?>/g, 'null'));
}
function declaration(name) {
    const start = source.indexOf('        function ' + name + '(');
    assert.ok(start >= 0, name);
    return source.slice(start).split(/\r?\n(?=        (?:async )?function )/)[0];
}
let navigated = null;
let saved = 0;
const state = {paymentConfirmed: false, clearhausTestPayment: null};
const context = vm.createContext({
    window: {CohertaClearhausTest: checkout}, clearhausTestMode: true,
    paymentEnabled: false, config: {paymentRequired: true, hasActiveSubscription: true}, state,
    getClearhausTestOffer: () => monthly,
    clearhausAcceptTerms: {checked: false, focus() {}},
    saveSetupProgress: () => { saved++; }, setPaymentStatus() {},
    goToStep: index => { navigated = index; },
    openPaymentCheckout: () => { throw Error('Test approval must not open checkout'); }
});
vm.runInContext(['paymentStepCanContinue', 'approveClearhausTestPayment', 'beginPaymentActivation'].map(declaration).join('\n'), context);
assert.equal(context.paymentStepCanContinue(), false, 'An existing real subscription must not skip the requested test');
context.beginPaymentActivation(4);
assert.equal(navigated, null);
assert.equal(saved, 0);
context.clearhausAcceptTerms.checked = true;
context.beginPaymentActivation(4);
assert.equal(navigated, 4, 'Test approval advances to the next wizard screen');
assert.equal(saved, 1);
assert.equal(state.paymentConfirmed, false, 'Never mark a real payment as confirmed');
assert.equal(context.paymentStepCanContinue(), true);
context.clearhausTestMode = false;
context.paymentEnabled = true;
context.config.hasActiveSubscription = false;
assert.equal(context.paymentStepCanContinue(), false, 'Test receipts must not satisfy the normal payment gate');
context.paymentEnabled = false;
assert.equal(context.paymentStepCanContinue(), true, 'The normal disabled-payment flow remains unchanged');
console.log('Clearhaus tests passed: pricing, dates, consent, resume, payment gate and mode isolation.');
