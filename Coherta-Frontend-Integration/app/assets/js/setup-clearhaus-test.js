(function (root) {
    'use strict';

    const termsVersion = '2026-09-21-v1.2';
    const cardLast4 = '4242';

    function formatDate(date) {
        return new Intl.DateTimeFormat('da-DK', {day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC'})
            .format(new Date(date + 'T12:00:00Z'));
    }

    function buildOffer(options) {
        const yearly = options.billingCycle === 'yearly';
        const monthlyPrice = Number(options.monthlyPrice);
        const recurringPrice = Math.round(monthlyPrice * (yearly ? 12 : 1) * 100) / 100;
        const currency = String(options.currency || 'DKK').toUpperCase();
        const initialPrice = recurringPrice;
        const valid = options.plan !== 'custom' && Number.isFinite(recurringPrice) && recurringPrice > 0
            && Number.isFinite(initialPrice) && initialPrice >= 0;
        const money = value => new Intl.NumberFormat('da-DK', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(value) + ' ' + currency;
        const interval = yearly ? 'årligt' : 'månedligt';
        const startDate = options.startDate;
        const firstBillingDate = startDate;
        const recurringText = valid ? money(recurringPrice) + ' ' + interval + ' ekskl. moms' : 'Aftales individuelt';
        return {
            valid, startDate, firstBillingDate, recurringPrice, initialPrice, currency,
            key: JSON.stringify([termsVersion, 'upfront', options.identity, options.plan, options.billingCycle, monthlyPrice, currency]),
            plan: options.label,
            initialPriceText: valid ? money(initialPrice) + ' ekskl. moms' : 'Aftales individuelt',
            recurringText,
            startDateText: formatDate(startDate) + ' (ved testgodkendelse)',
            firstBillingDateText: valid ? formatDate(firstBillingDate) + ' (ved godkendelse)' : 'Aftales individuelt',
            frequencyText: valid ? (yearly ? 'Hvert år' : 'Hver måned') + ' fra ' + formatDate(firstBillingDate) + '. Betales forud.' : 'Aftales individuelt',
            durationText: 'Løbende indtil opsigelse. Fornyes automatisk ' + (yearly ? 'for 12 måneder' : 'for 1 måned') + ' ad gangen.'
        };
    }

    function isApproved(offer, receipt) {
        return !!(offer.valid && receipt && receipt.offerKey === offer.key && receipt.startDate
            && Number.isFinite(Date.parse(receipt.acceptedAt)) && receipt.cardLast4 === cardLast4);
    }

    function approve(offer, accepted, now) {
        if (!offer.valid || accepted !== true) return null;
        return {offerKey: offer.key, startDate: offer.startDate, acceptedAt: now.toISOString(), termsVersion, cardLast4};
    }

    root.CohertaClearhausTest = {buildOffer, isApproved, approve};
})(typeof window === 'undefined' ? globalThis : window);
