const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const {spawnSync} = require('node:child_process');

const source = fs.readFileSync(path.join(__dirname, '../setup-funnel.php'), 'utf8');
function declaration(name) {
    const match = new RegExp('        (?:async )?function ' + name + '\\(').exec(source);
    assert.ok(match, name);
    return source.slice(match.index).match(/^[\s\S]*?\r?\n        }/)[0];
}

async function signupPayload(plan, billingCycle) {
    let payload;
    const storage = () => {
        const values = new Map();
        return {getItem: key => values.get(key) ?? null, setItem: (key, value) => values.set(key, String(value))};
    };
    const body = new Map();
    const context = vm.createContext({
        bearerToken: '',
        window: {localStorage: storage(), sessionStorage: storage()},
        document: {body: {getAttribute: key => body.get(key) ?? null, setAttribute: (key, value) => body.set(key, value)}},
        signupBlocked: false, talentAttractEnabled: false, pricingModels: {}, storedSetupProgress: {},
        config: {userLoggedIn: false, selectedMarket: 'Denmark'},
        state: {accountType: 'business', pricingPlan: plan, billingCycle, userId: 0},
        setupUserName: {value: 'Test User'}, setupUserEmail: {value: 'test@example.test'},
        isPersonalAccountType: value => value === 'personal', truthy: Boolean,
        setIdentityStatus() {}, updateStepChrome() {}, saveSetupProgress() {},
        getAffiliateAttribution: () => ({}),
        fetch: async (url, options) => {
            assert.equal(url, 'https://api.coherta.com/users/create');
            payload = JSON.parse(options.body);
            return {ok: true, json: async () => ({user_id: 7, session_id: 'fixture_session', access_token: 'fixture_access_token'})};
        }
    });
    vm.runInContext(['normalizeBillingCycle', 'normalizePricingPlan', 'billingPeriodKey', 'getPricingModel',
        'getBillingMonthlyPrice', 'buildPaymentActivationUrl', 'applyAccountTypePricingRules',
        'isValidEmail', 'splitName', 'extractSessionId', 'extractUserId', 'normalizeCheckoutCountryCode',
        'checkoutApiPlan', 'persistSessionId', 'persistUserId', 'currentUserId',
        'isStoredUserIdSession', 'getSessionId', 'saveAccountIdentityStep'].map(declaration).join('\n'), context);
    assert.equal(await context.saveAccountIdentityStep(), true);
    assert.equal(payload.plan_key, plan);
    for (const saved of [context.window.localStorage, context.window.sessionStorage]) {
        assert.equal(saved.getItem('session_id'), 'fixture_access_token', 'Signup keeps the access token as session_id after saving the user ID');
        assert.equal(saved.getItem('access_token'), 'fixture_access_token');
        assert.equal(saved.getItem('coherta_setup_user_id'), '7');
    }
    assert.equal(context.getSessionId(), 'fixture_access_token');
    return payload;
}

async function main() {
    const urls = [
        'https://www.coherta.com/app/setup-funnel?account_type=business&customer_type=company&use=business&plan=extended&model=extended&billing=monthly&country_code=dk&source=pricing',
        'https://www.coherta.com/app/setup-funnel?account_type=business&customer_type=company&use=business&plan=standard&model=standard&billing=monthly&country_code=dk&source=pricing',
        'https://www.coherta.com/app/setup-funnel'
    ];
    const configResult = spawnSync('php', [path.join(__dirname, 'fixtures/setup-hubspot.php'), '--config'], {
        encoding: 'utf8', input: JSON.stringify(urls.map(url => Object.fromEntries(new URL(url).searchParams)))
    });
    assert.equal(configResult.status, 0, configResult.stderr || configResult.error?.message);
    const configs = JSON.parse(configResult.stdout);
    const expectedPlans = ['extended', 'standard', 'standard'];
    for (let index = 0; index < configs.length; index++) {
        const config = configs[index];
        assert.equal(config.selectedPlan, expectedPlans[index], urls[index]);
        const context = vm.createContext({config, pricingRequestPresent: index < 2,
            storedSetupProgress: {pricingPlan: 'extended', billingCycle: 'yearly'}});
        vm.runInContext(['normalizePricingPlan', 'normalizeBillingCycle'].map(declaration).join('\n'), context);
        const initialPlan = source.match(/const initialPricingPlan = ([^\r\n]+);/)[1];
        const initialBilling = source.match(/const initialBillingCycle = ([^\r\n]+);/)[1];
        assert.equal(vm.runInContext(initialPlan, context), expectedPlans[index], 'The URL overrides a stale browser plan');
        assert.equal(vm.runInContext(initialBilling, context), 'monthly', 'The URL overrides a stale billing cycle');
    }
    const standard = await signupPayload('standard', 'monthly');
    const annual = await signupPayload('standard', 'yearly');
    const extended = await signupPayload('extended', 'monthly');
    assert.equal(standard.monthly_price, 999);
    assert.equal(annual.monthly_price, 999);
    const result = spawnSync('php', [path.join(__dirname, 'fixtures/setup-hubspot.php')], {
        encoding: 'utf8', input: JSON.stringify([standard, annual, extended,
            {...standard, monthly_price: 1, custom_monthly_price: 1},
            {source: 'legacy-signup', plan: 'growth', plan_key: 'standard', currency: 'DKK'},
            {source: 'startups', plan: 'extended', currency: 'DKK'}])
    });
    assert.equal(result.status, 0, result.stderr || result.error?.message);
    const deals = JSON.parse(result.stdout);
    for (const index of [0, 1, 3]) {
        const {deal, lineItem} = deals[index];
        assert.equal(lineItem.hs_product_id, '427467262189', 'Standard selects the existing 999 DKK product');
        assert.equal(lineItem.price, '999', 'Use the catalogue price, including when client pricing is tampered with');
        assert.equal(deal.amount, '999');
        assert.equal(deal.deal_currency_code, 'DKK');
    }
    for (const index of [2, 4, 5]) {
        assert.equal(deals[index].lineItem.hs_product_id, '427918554344', 'Extended, legacy Growth and Startups retain Extended');
    }
    assert.equal(deals[5].deal.amount, '1799');
    assert.equal(deals[5].deal.deal_currency_code, 'USD');
    console.log('Setup signup payloads select the correct HubSpot catalogue products and prices. No live records changed.');
}

main().catch(error => { console.error(error); process.exitCode = 1; });
