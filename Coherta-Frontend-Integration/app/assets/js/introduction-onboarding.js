(() => {
    'use strict';
    const config = JSON.parse(document.getElementById('intro-setup-config').textContent);
    const $ = id => document.getElementById(id);
    const state = { step: 1, authenticated: false, connected: false, completed: false, polling: false, pollVersion: 0 };
    const error = (message, target = 'ios-error') => {
        ['ios-error', 'ios-linkedin-error'].forEach(id => {
            $(id).textContent = id === target ? message || '' : '';
            $(id).hidden = !$(id).textContent;
        });
    };
    const status = message => { $('ios-status').textContent = message || ''; };
    const wait = ms => new Promise(resolve => setTimeout(resolve, ms));
    function showLinkedInApproval(required) {
        window.CohertaLinkedInApproval.update(required && state.step === 2 && state.authenticated);
    }
    async function api(action, method = 'GET', data) {
        const response = await fetch(`${config.endpoint}?action=${action}`, {
            method, credentials: 'same-origin', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': config.csrf },
            ...(data === undefined ? {} : { body: JSON.stringify(data) })
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            if (result.requires_login) { state.authenticated = false; state.connected = false; updateAccount(); showStep(2); }
            throw new Error(result.detail || 'We could not complete this step. Please try again.');
        }
        return result;
    }
    function showStep(step) {
        if (step === 4 && !state.completed) return;
        state.step = step;
        if (step !== 2) showLinkedInApproval(false);
        document.querySelectorAll('[data-step]').forEach(section => { section.hidden = Number(section.dataset.step) !== step; });
        document.querySelectorAll('[data-progress]').forEach(item => {
            const n = Number(item.dataset.progress);
            item.classList.toggle('is-complete', n < step || state.completed);
            if (n === step) item.setAttribute('aria-current', 'step'); else item.removeAttribute('aria-current');
            item.querySelector(':scope > span').textContent = n < step || state.completed ? '✓' : String(n);
        });
        error(''); status('');
        document.querySelector(`[data-step="${step}"] h2`).focus({ preventScroll: true });
        $('intro-setup').scrollIntoView({ block: 'start', behavior: 'instant' });
    }
    function busy(form, value) {
        form.querySelectorAll('button').forEach(button => { button.disabled = value; });
        form.setAttribute('aria-busy', String(value));
    }
    function updateAccount() {
        if (!state.authenticated || state.connected) showLinkedInApproval(false);
        $('ios-account-panel').hidden = state.authenticated;
        $('ios-authenticated').hidden = !state.authenticated;
        $('ios-linkedin-panel').hidden = !state.authenticated;
        $('ios-linkedin-form').hidden = state.connected;
        $('ios-linkedin-ready').hidden = !state.connected;
        $('ios-connect-next').disabled = !state.connected;
        if (state.connected) {
            $('ios-code-form').hidden = true;
            $('ios-linkedin-status').textContent = '';
            $('ios-linkedin-password').value = '';
            $('ios-code').value = '';
        }
    }
    async function load(initial = false) {
        error(''); status('Loading your setup…'); $('ios-reload').hidden = true;
        try {
            const saved = await api('state');
            state.authenticated = Boolean(saved.authenticated); state.connected = Boolean(saved.linkedin_connected); state.completed = Boolean(saved.completed);
            $('ios-description').value = saved.description || $('ios-description').value;
            $('ios-description').dispatchEvent(new Event('input'));
            if (saved.name) $('ios-account-name').textContent = `Signed in as ${saved.name}.`;
            (saved.stars || []).slice(0, 5).forEach((person, index) => { $(`ios-star-${index + 1}`).value = person.linkedin_url || ''; });
            updateAccount(); status('');
            $('ios-target-form').querySelector('[type=submit]').disabled = false;
            if (state.completed) {
                if (config.resume) window.location.replace('/app/sales_insider');
                else showStep(4);
            } else if (!initial) showStep(state.step);
        } catch (e) { error(e.message); status(''); $('ios-reload').hidden = false; }
    }
    $('ios-reload').addEventListener('click', () => load());
    $('ios-description').addEventListener('input', () => { $('ios-character-count').textContent = `${$('ios-description').value.length.toLocaleString('en')} / 8,000`; });
    $('ios-target-form').addEventListener('submit', async event => {
        event.preventDefault(); error(''); busy(event.currentTarget, true);
        try { await api('target', 'PUT', { description: $('ios-description').value.trim() }); showStep(2); }
        catch (e) { error(e.message); } finally { busy($('ios-target-form'), false); }
    });
    document.querySelectorAll('[name=account-mode]').forEach(radio => radio.addEventListener('change', () => {
        const signup = document.querySelector('[name=account-mode]:checked').value === 'signup';
        $('ios-name-field').hidden = !signup; $('ios-name').required = signup;
        document.querySelector('label[for="ios-account-password"]').textContent = signup ? 'Create a Coherta password' : 'Coherta password';
        $('ios-account-password').minLength = signup ? 12 : 1;
        $('ios-account-password').autocomplete = signup ? 'new-password' : 'current-password';
        $('ios-account-password-hint').hidden = !signup; $('ios-reset-link').hidden = signup;
        document.querySelector('.ios-terms').hidden = !signup;
        $('ios-account-submit').textContent = signup ? 'Create free account →' : 'Sign in to Coherta →'; error('');
    }));
    $('ios-account-form').addEventListener('submit', async event => {
        event.preventDefault(); error(''); busy(event.currentTarget, true);
        const mode = document.querySelector('[name=account-mode]:checked').value;
        try {
            await api(mode, 'POST', { name: $('ios-name').value.trim(), email: $('ios-email').value.trim(), password: $('ios-account-password').value });
            state.authenticated = true; $('ios-account-password').value = ''; updateAccount();
            const saved = await api('state'); state.connected = Boolean(saved.linkedin_connected); updateAccount();
            if (saved.name) $('ios-account-name').textContent = `Signed in as ${saved.name}.`;
            if (!state.connected) $('ios-linkedin-username').focus();
        } catch (e) { error(e.message); } finally { $('ios-account-password').value = ''; busy($('ios-account-form'), false); }
    });
    document.querySelectorAll('[data-toggle-password]').forEach(button => button.addEventListener('click', () => {
        const input = $(button.dataset.togglePassword); const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password'; button.textContent = visible ? 'Hide' : 'Show';
        button.setAttribute('aria-label', `${visible ? 'Hide' : 'Show'} ${input.id.includes('linkedin') ? 'LinkedIn' : 'Coherta'} password`);
    }));
    async function pollLinkedIn() {
        if (state.polling || state.connected) return;
        state.polling = true;
        const version = ++state.pollVersion;
        busy($('ios-linkedin-form'), true);
        try {
            for (let attempt = 0; attempt < 90; attempt++) {
                if (version !== state.pollVersion) return;
                const data = await api('linkedin-status');
                if (version !== state.pollVersion) return;
                showLinkedInApproval(data.status === 7);
                if (data.status === 1) { state.connected = true; updateAccount(); $('ios-connect-next').focus(); return; }
                if (data.status === 2) {
                    error(window.CohertaLinkedInApproval.messages.invalidCredentials, 'ios-linkedin-error');
                    $('ios-linkedin-status').textContent = '';
                    return;
                }
                if (data.status === 3) {
                    error(window.CohertaLinkedInApproval.messages.approvalTimeout, 'ios-linkedin-error');
                    $('ios-linkedin-status').textContent = '';
                    return;
                }
                if ((data.status === 8 || data.status === 9) && !data.code_submitted) {
                    $('ios-code-form').hidden = false;
                    $('ios-linkedin-status').textContent = data.status === 8 ? 'Enter the code from your authenticator app.' : 'Enter the verification code LinkedIn sent by SMS or email.';
                    $('ios-code').focus(); return;
                }
                $('ios-linkedin-status').textContent = data.status === 7 || data.status === 10 ? 'Approve the sign-in request in your LinkedIn app. We will check for your approval.' : 'Waiting for LinkedIn to confirm your connection…';
                await wait(2000);
            }
            $('ios-linkedin-status').textContent = 'LinkedIn is still waiting for verification. Approve any LinkedIn prompts, then enter your password and try connecting again.';
        } catch (e) { error(e.message); $('ios-linkedin-status').textContent = ''; }
        finally { showLinkedInApproval(false); state.polling = false; busy($('ios-linkedin-form'), false); }
    }
    $('ios-linkedin-form').addEventListener('submit', async event => {
        event.preventDefault(); if (state.polling) return; error(''); busy(event.currentTarget, true);
        $('ios-code-form').hidden = true; $('ios-linkedin-status').textContent = 'Connecting to LinkedIn…';
        const credentials = { username: $('ios-linkedin-username').value.trim(), password: $('ios-linkedin-password').value };
        $('ios-linkedin-password').value = '';
        try { await api('connect', 'POST', credentials); delete credentials.password; await pollLinkedIn(); }
        catch (e) { error(e.message); $('ios-linkedin-status').textContent = ''; }
        finally { delete credentials.password; busy($('ios-linkedin-form'), false); }
    });
    $('ios-code-form').addEventListener('submit', async event => {
        event.preventDefault(); if (state.polling) return; error(''); busy(event.currentTarget, true);
        try { await api('linkedin-code', 'PATCH', { code: $('ios-code').value.trim() }); $('ios-code').value = ''; $('ios-code-form').hidden = true; await pollLinkedIn(); }
        catch (e) { error(e.message); } finally { busy($('ios-code-form'), false); }
    });
    $('ios-connect-next').addEventListener('click', () => { if (state.connected) showStep(3); });
    document.querySelectorAll('[data-back]').forEach(button => button.addEventListener('click', () => showStep(Number(button.dataset.back))));
    function starUrls() {
        const seen = new Set();
        return Array.from({ length: 5 }, (_, index) => {
            const field = $(`ios-star-${index + 1}`); let url;
            try {
                url = new URL(field.value.trim());
                if (!['http:', 'https:'].includes(url.protocol) || !['linkedin.com', 'www.linkedin.com'].includes(url.hostname.toLowerCase()) || url.username || url.password || url.port || !/^\/in\/[a-z0-9_%\-]+\/?$/i.test(url.pathname)) throw new Error();
                const slug = decodeURIComponent(url.pathname.replace(/\/$/, '').slice(4));
                if (/[\x00-\x20\x7f/\\?#]/.test(slug)) throw new Error();
                const canonical = `https://www.linkedin.com/in/${encodeURIComponent(slug).toLowerCase()}`;
                if (seen.has(canonical)) { field.focus(); throw new Error('Choose 5 different people. A star contact is listed more than once.'); }
                seen.add(canonical); return canonical;
            } catch (e) { field.focus(); throw new Error(e.message.startsWith('Choose 5') ? e.message : `Contact ${index + 1} needs a direct LinkedIn profile URL, such as https://www.linkedin.com/in/name.`); }
        });
    }
    $('ios-stars-form').addEventListener('submit', async event => {
        event.preventDefault(); error('');
        let urls; try { urls = starUrls(); } catch (e) { error(e.message); return; }
        busy(event.currentTarget, true); status('Saving your audience and preferred introducers. This can take a moment…');
        try { const saved = await api('complete', 'POST', { star_urls: urls }); state.completed = saved.completed === true; if (!state.completed) throw new Error('Your setup has not finished. Please try again.'); showStep(4); }
        catch (e) { error(e.message); status(''); } finally { busy($('ios-stars-form'), false); }
    });
    window.addEventListener('pagehide', () => { state.pollVersion++; showLinkedInApproval(false); $('ios-linkedin-password').value = ''; $('ios-account-password').value = ''; $('ios-code').value = ''; });
    load(true);
})();
