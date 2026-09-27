const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../setup-funnel.php'), 'utf8');
function declaration(name) {
    const match = new RegExp('        (?:async )?function ' + name + '\\(').exec(source);
    assert.ok(match, name);
    return source.slice(match.index).match(/^[\s\S]*?\r?\n        }/)[0];
}

function createContext({token = '', bodyToken = '', local = {}, session = {}} = {}) {
    const requests = [];
    const errors = [];
    let polls = 0;
    const storage = values => ({getItem: key => values[key] ?? null});
    const context = vm.createContext({
        bearerToken: token, config: {}, storedSetupProgress: {},
        state: {userId: 42, linkedinConnected: false, linkedinStatus: 'idle'}, linkedInPolling: false,
        document: {body: {getAttribute: () => bodyToken}},
        window: {localStorage: storage(local), sessionStorage: storage(session)},
        linkedInUsername: {value: 'test@example.test'}, linkedInPassword: {value: 'fixture-password'},
        linkedInCode: {value: '123456'}, linkedInCodeBtn: {disabled: false},
        LINKEDIN_CONNECT_ENDPOINT: 'https://api.coherta.com/apps/linkedin/connect',
        LINKEDIN_SMS_ENDPOINT: 'https://api.coherta.com/apps/linkedin/connect/sms',
        setLinkedInBusy() {}, setTwoFactorVisible() {}, translateSetupText: text => text,
        setLinkedInStatus: (message, type) => { if (type === 'error') errors.push(message); },
        pollLinkedInStatus: async () => { polls++; },
        fetch: async (url, options) => {
            requests.push({url, ...options});
            return {ok: true, json: async () => ({})};
        }
    });
    vm.runInContext(['currentUserId', 'isStoredUserIdSession', 'getSessionId', 'headersJson',
        'connectLinkedInFromSetup', 'submitLinkedInCodeFromSetup'].map(declaration).join('\n'), context);
    return {context, requests, errors, polls: () => polls};
}

async function main() {
    const token = 'fixture-auth-token';
    for (const sessionSource of [
        {local: {coherta_auth_session_id: token, session_id: '42'}},
        {session: {coherta_auth_session_id: token}},
        {local: {access_token: token}},
        {token},
        {bodyToken: token},
        {token: '42', local: {coherta_auth_session_id: token}}
    ]) {
        const test = createContext(sessionSource);
        await test.context.connectLinkedInFromSetup();
        assert.equal(test.requests.length, 1, 'Saved authentication must allow LinkedIn login after reload or payment return');
        assert.equal(test.requests[0].method, 'POST');
        assert.equal(test.requests[0].headers.Authorization, 'Bearer ' + token);
        await test.context.submitLinkedInCodeFromSetup();
        assert.equal(test.requests.length, 2, 'The verification-code step uses the same saved authentication');
        assert.equal(test.requests[1].method, 'PATCH');
        assert.equal(test.requests[1].headers.Authorization, 'Bearer ' + token);
        assert.deepEqual(JSON.parse(test.requests[1].body), {sms_code: '123456'});
        assert.equal(test.polls(), 2);
        assert.deepEqual(test.errors, []);
    }
    for (const missingSession of [{}, {token: '42', local: {session_id: '42', coherta_auth_session_id: '42'}}]) {
        const test = createContext(missingSession);
        await test.context.connectLinkedInFromSetup();
        await test.context.submitLinkedInCodeFromSetup();
        assert.equal(test.requests.length, 0, 'A missing token or numeric user ID must not authorize LinkedIn requests');
        assert.equal(test.errors.length, 2);
        assert.ok(test.errors.every(message => message.includes('Coherta session mangler')));
    }
    console.log('LinkedIn session tests passed: stored-token login and verification, request authorization, and missing-session guards. No live requests made.');
}

main().catch(error => { console.error(error); process.exitCode = 1; });
