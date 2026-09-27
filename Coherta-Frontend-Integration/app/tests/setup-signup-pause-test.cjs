const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const source = fs.readFileSync('app/setup-funnel.php', 'utf8');
function declaration(name) {
    const match = new RegExp('        (?:async )?function ' + name + '\\(').exec(source);
    assert.ok(match, name);
    return source.slice(match.index).split(/\r?\n(?=        (?:async )?function )/)[0];
}

async function main() {
    let requests = 0;
    let status = '';
    const context = vm.createContext({
        signupBlocked: true,
        config: {signupNotice: 'Signups are paused'},
        state: {accountType: 'business', userSaving: false},
        setupUserName: {value: 'Test User'},
        setupUserEmail: {value: 'test@example.test'},
        setIdentityStatus: message => { status = message; },
        fetch: () => { requests++; throw Error('No account request is allowed'); },
        window: {location: {search: '?step=linkedin'}},
        URLSearchParams,
        findStepIndexById: id => ['account-type', 'campaign', 'workflow', 'linkedin'].indexOf(id),
        paymentReturnStatus: () => '',
        storedSetupProgress: {currentStepId: 'linkedin', currentStepIndex: 3},
        screens: new Array(4)
    });
    vm.runInContext(['isValidEmail', 'accountStepCanContinue', 'saveAccountIdentityStep',
        'resolveInitialStepIndex', 'stepIndexFromId', 'goToStep'].map(declaration).join('\n'), context);

    assert.equal(context.accountStepCanContinue(), false, 'Valid details must not unlock signup');
    assert.equal(await context.saveAccountIdentityStep(), false, 'Calling the save handler cannot bypass the pause');
    assert.equal(requests, 0, 'Paused signup must not send a request');
    assert.equal(status, 'Signups are paused');
    assert.equal(context.resolveInitialStepIndex(), 0, 'A direct step URL cannot bypass the pause');
    context.window.location.search = '';
    assert.equal(context.resolveInitialStepIndex(), 0, 'Stored setup progress cannot bypass the pause');
    assert.doesNotThrow(() => context.goToStep(3), 'Step navigation exits before doing any work');

    context.signupBlocked = false;
    assert.equal(context.accountStepCanContinue(), true, 'Existing customers can continue with valid details');
    assert.equal(context.resolveInitialStepIndex(), 3, 'Existing customers retain their saved progress');

    const translations = JSON.parse(fs.readFileSync('app/setup-funnel-i18n.json', 'utf8'));
    const notice = 'Vi kan i øjeblikket ikke tage imod nye kunder på grund af stor efterspørgsel.';
    for (const locale of ['uk', 'de', 'es', 'fi', 'fr', 'hr', 'is', 'no', 'pl', 'se']) {
        assert.ok(translations[notice][locale], 'Notice translation: ' + locale);
        assert.ok(translations['Opret din konto'][locale], 'Signup translation: ' + locale);
    }
    console.log('Signup pause tests passed: form validation, submission guard, URL and saved-progress bypasses, existing customers and translations.');
}

main().catch(error => { console.error(error); process.exitCode = 1; });
