(function () {
    'use strict';
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const parseList = value => [...new Set(String(value || '').split(/[\n,]/).map(item => item.trim()).filter(Boolean))];
    const reviewPending = person => person?.match_status === 'review_pending';
    const eligible = candidates => (Array.isArray(candidates) ? candidates : []).filter(person => person.id != null && (reviewPending(person) || (Number.isFinite(Number(person.icp_score)) && Number(person.icp_score) > 95 && Number(person.icp_score) <= 100))).sort((a, b) => Number(reviewPending(a)) - Number(reviewPending(b)) || Number(b.icp_importance || 0) - Number(a.icp_importance || 0) || Number(b.icp_score) - Number(a.icp_score));
    const profileUrl = value => {
        try { const url = new URL(value); return url.protocol === 'https:' && /^(www\.)?linkedin\.com$/.test(url.hostname) && /^\/(in|pub)\//.test(url.pathname) ? url.href : ''; } catch (_) { return ''; }
    };
    function imageUrl(value) {
        if (!value) return '';
        if (/^\/app\/assets\/images\/[a-z0-9/_ .-]+$/i.test(value)) return value;
        if (!/[/:]/.test(value)) return 'https://api.coherta.com/images/' + encodeURIComponent(value);
        try { const url = new URL(value); return url.protocol === 'https:' ? url.href : ''; } catch (_) { return ''; }
    }
    function draftMessage(target, introducer, sender) {
        const firstName = value => String(value || '').trim().split(/\s+/)[0];
        const senderFirstName = firstName(sender);
        const signature = senderFirstName.charAt(0).toUpperCase() + senderFirstName.slice(1);
        return `Hi ${firstName(introducer.name)},\n\nI'm using @Coherta to identify new leads, and it has scored ${target.name} as a ${target.icp_score}% match for the customers I'm looking for.\n\nI'd love to connect with ${firstName(target.name)}. I noticed you know each other and thought I'd reach out.\n\nWould you be comfortable making an introduction? I’d really appreciate it, and I’m happy to send a short note you can forward.\n\nThanks${signature ? ',\n' + signature : '!'}`;
    }
    function confirmationChoice(search) {
        const params = new URLSearchParams(search);
        if (params.get('confirm_introduction') !== '1') return null;
        const choice = {};
        for (const field of ['icp_id', 'participant_id', 'introducer_id']) {
            const value = params.get(field);
            if (params.getAll(field).length !== 1 || !/^[1-9][0-9]*$/.test(value || '') || !Number.isSafeInteger(Number(value))) return null;
            choice[field] = value;
        }
        return choice;
    }
    if (typeof module !== 'undefined' && module.exports) module.exports = {parseList, eligible, profileUrl, imageUrl, escapeHtml, draftMessage, confirmationChoice};
    if (typeof document === 'undefined') return;
    const root = document.getElementById('introduction-app');
    if (!root) return;
    const config = JSON.parse(document.getElementById('intro-config').textContent);
    const byId = id => document.getElementById('intro-' + id);
    const state = {icps: [], editingIcp: null, creationKey: null, savingIcp: false, stars: [], candidates: [], scheduled: [], target: null, introducer: null, starDraft: new Map(), contacts: [], pending: null, sending: false, loadId: 0, contactId: 0, icpLoaded: false, overviewLoaded: false};
    let tooltipSequence = 0;
    let searchTimer;
    let removing = false;
    const emailChoice = confirmationChoice(window.location.search);
    let emailChoiceHandled = false;

    async function request(action, method = 'GET', body = null, query = '') {
        const url = new URL(config.endpoint, window.location.origin);
        url.searchParams.set('action', action);
        if (query && typeof query === 'object') {
            for (const [key, value] of Object.entries(query)) url.searchParams.set(key, value);
        } else if (query) url.searchParams.set('q', query);
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 30000);
        try {
            const response = await fetch(url, {method, credentials: 'same-origin', cache: 'no-store', headers: {'Accept':'application/json', 'Content-Type':'application/json', 'X-CSRF-Token':config.csrf}, body: body === null ? undefined : JSON.stringify(body), signal: controller.signal});
            const data = await response.json().catch(() => null);
            if (!response.ok || !data || data.error) {
                const error = new Error(data?.detail || data?.error || 'This request could not be completed. Please try again.');
                error.status = response.status;
                throw error;
            }
            return data;
        } catch (error) {
            if (error.name === 'AbortError') throw new Error('The request timed out. Please try again.');
            throw error;
        } finally { clearTimeout(timer); }
    }
    function showError(id, message) { byId(id).textContent = message || ''; byId(id).hidden = !message; }
    function notice(message, error = false) { const el = byId('notice'); el.textContent = message; el.dataset.error = String(error); el.hidden = !message; }
    function avatar(person, large = false) {
        const initials = (person.name || '?').split(/\s+/).slice(0, 2).map(part => part[0]).join('');
        const src = imageUrl(person.image);
        return `<span class="intro-avatar${large ? ' intro-avatar-lg' : ''}" aria-label="${escapeHtml(person.name)}">${src ? `<img src="${escapeHtml(src)}" alt="${escapeHtml(person.name)}" loading="lazy" referrerpolicy="no-referrer">` : escapeHtml(initials)}</span>`;
    }
    root.addEventListener('error', event => { if (event.target.tagName === 'IMG') { const parent = event.target.parentElement; parent.textContent = (parent.getAttribute('aria-label') || '?').split(/\s+/).slice(0, 2).map(part => part[0]).join(''); } }, true);
    function score(value, reasons, intro = false) {
        const title = intro ? 'Introduction fit' : 'ICP match';
        const valid = value !== null && value !== undefined && Number.isFinite(Number(value)) && Number(value) >= 0 && Number(value) <= 100;
        const label = valid ? `${Number(value)}% ${intro ? 'introduction fit' : 'ICP match'}` : 'Score unavailable';
        const items = Array.isArray(reasons) && reasons.length ? reasons : ['No explanation is available yet.'];
        const tooltipId = 'intro-tooltip-' + (++tooltipSequence);
        return `<span class="intro-score" tabindex="0" aria-describedby="${tooltipId}">${escapeHtml(label)} <i aria-hidden="true">i</i><span class="intro-tooltip" id="${tooltipId}" role="tooltip"><strong>${escapeHtml(title)}</strong><ul>${items.map(reason => `<li>${escapeHtml(reason)}</li>`).join('')}</ul></span></span>`;
    }
    function candidateIntroducers(person) {
        const seen = new Set();
        return (Array.isArray(person.introducers) ? person.introducers : []).filter(p => p.id != null && String(p.id) !== String(person.id) && !seen.has(String(p.id)) && seen.add(String(p.id))).slice(0, 4);
    }
    function matchScore(person) {
        return reviewPending(person) ? '<span class="intro-score">Match review pending</span>' : score(person.icp_score, person.icp_reasons);
    }
    function renderSettings() {
        const groups = [...state.icps].sort((a, b) => b.importance - a.importance || a.id - b.id);
        byId('icp-count').textContent = groups.length + (groups.length === 1 ? ' group' : ' groups');
        byId('icp-manage-count').textContent = byId('icp-count').textContent;
        byId('icp-summary').innerHTML = !state.icpLoaded ? '<p class="intro-muted">Your ICP groups could not be loaded. Refresh to try again.</p>' : groups.length ? `<ul class="intro-icp-compact-list">${groups.map(icp => `<li class="intro-icp-compact-row"><div class="intro-icp-compact-details"><strong>${escapeHtml(icp.name)}</strong><div class="intro-tags intro-icp-compact-tags">${(Array.isArray(icp.tags) ? icp.tags : []).map(tag => `<span class="intro-tag">${escapeHtml(tag)}</span>`).join('')}</div></div><div class="intro-icp-identified"><strong>${Number(icp.matching_participants || 0).toLocaleString()}</strong><span>identified</span></div></li>`).join('')}</ul>` : '<p class="intro-muted">Use Manage to add your first ideal customer profile.</p>';
        byId('icp-overview').innerHTML = !state.icpLoaded ? '<p class="intro-muted">Your ICP groups could not be loaded. Refresh to try again.</p>' : groups.length ? `<div class="intro-icp-table" role="table" aria-label="Ideal customer groups ordered by importance">
            <div class="intro-icp-row intro-icp-columns" role="row"><span role="columnheader">Customer group</span><span role="columnheader">Tags</span><span role="columnheader">Importance</span><span role="columnheader">Participants</span><span role="columnheader">Introductions</span><span role="columnheader" class="intro-sr-only">Edit</span></div>
            ${groups.map((icp, index) => `<div class="intro-icp-row" role="row">
                <div class="intro-icp-name" role="cell"><span class="intro-icp-rank">${index + 1}</span><div><button type="button" data-edit-icp="${escapeHtml(icp.id)}">${escapeHtml(icp.name)}</button>${index === 0 ? '<small>TOP PRIORITY</small>' : ''}</div></div>
                <div class="intro-tags intro-icp-tags" role="cell">${(Array.isArray(icp.tags) ? icp.tags : []).map(tag => `<span class="intro-tag">${escapeHtml(tag)}</span>`).join('')}</div>
                <div class="intro-icp-metric" role="cell"><span class="intro-icp-mobile-label">Importance</span><span class="intro-importance-badge" data-high="${icp.importance >= 8}">${escapeHtml(icp.importance)}<small> / 10</small></span></div>
                <div class="intro-icp-metric" role="cell"><span class="intro-icp-mobile-label">Participants</span><strong>${Number(icp.matching_participants || 0).toLocaleString()}</strong></div>
                <div class="intro-icp-metric" role="cell"><span class="intro-icp-mobile-label">Introductions</span><strong>${Number(icp.introductions_sent || 0).toLocaleString()}</strong></div>
                <div role="cell" class="intro-icp-edit"><button class="intro-text-btn" type="button" data-edit-icp="${escapeHtml(icp.id)}" aria-label="Edit ${escapeHtml(icp.name)}">Edit ↗</button></div>
            </div>`).join('')}</div>` : '<div class="intro-icp-empty"><span aria-hidden="true">◎</span><div><h3>Who is your next great customer?</h3><p>Add your first ICP in a few sentences. Create a separate group for every audience you want to reach.</p></div><button class="intro-btn intro-btn-white" type="button" data-add-icp>Add your first ICP →</button></div>';
        byId('star-count').textContent = state.stars.length + ' / 10';
        byId('star-summary').innerHTML = state.stars.length ? `<div class="intro-avatar-stack">${state.stars.map(p => avatar(p)).join('')}</div><p>${state.stars.length} trusted ${state.stars.length === 1 ? 'connection' : 'connections'}<br>${state.stars.length < 5 ? 'Add a few more to grow your circle.' : 'Good people. Great introductions.'}</p>` : '<button class="intro-btn intro-btn-white" type="button" data-manage-stars>Choose 5–10 people who are happy to help.</button>';
    }
    function renderHistory() {
        byId('scheduled-count').textContent = state.scheduled.filter(item => item.status === 'scheduled').length;
            byId('history').innerHTML = state.scheduled.length ? state.scheduled.map(item => `<div class="intro-history-item"><div><strong>${escapeHtml(item.target_name)}</strong><p>Via ${escapeHtml(item.introducer_name)}</p></div><div><span class="intro-score">${escapeHtml(statusLabel(item.status))}</span><time>${escapeHtml(formatDate(item.scheduled_at))}</time></div></div>`).join('') : '<p class="intro-muted">Your scheduled introduction requests will appear here.</p>';
    }
    function statusLabel(status) { return ({scheduled:'Scheduled', sent:'Sent', paused:'Paused', needs_attention:'Needs attention'})[status] || 'Pending'; }
    function formatDate(value) { const date = new Date(value); return value && !Number.isNaN(date.getTime()) ? date.toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'}) : ''; }
    function render() {
        renderSettings(); renderHistory();
        byId('match-count').textContent = state.candidates.length;
        const pendingReviews = state.candidates.filter(reviewPending).length;
        if (byId('match-summary')) byId('match-summary').textContent = pendingReviews ? (pendingReviews === state.candidates.length ? `${pendingReviews} awaiting match review` : `Above 95% ICP match · ${pendingReviews} awaiting review`) : 'Above 95% ICP match';
        byId('loading').hidden = true;
        byId('workspace').hidden = !state.candidates.length;
        byId('empty').hidden = !!state.candidates.length;
        if (!state.candidates.length) {
            byId('empty-title').textContent = !state.overviewLoaded ? 'We couldn’t load your opportunities' : !state.icps.length ? 'A good introduction starts with your ICP' : 'Your next introduction is on its way';
            byId('empty-text').textContent = !state.overviewLoaded ? 'Please refresh to try again. Your saved settings and scheduled requests are safe.' : !state.icps.length ? 'Define who you want to meet, then add your star connections.' : 'We’re actively searching LinkedIn for matches for you. We usually find your first match within 60 minutes, and we’ll send you an email as soon as we find a match.';
            state.target = null; state.introducer = null;
            return;
        }
        if (!state.candidates.some(person => String(person.id) === String(state.target))) { state.target = String(state.candidates[0].id); state.introducer = null; }
        const target = state.candidates.find(person => String(person.id) === String(state.target));
        const introducers = candidateIntroducers(target);
        const url = profileUrl(target.linkedin_url);
        byId('target').innerHTML = `<div class="intro-target-profile">${avatar(target, true)}<div><h3 class="intro-profile-name">${escapeHtml(target.name)}</h3><p class="intro-profile-role">${escapeHtml(target.job_title || 'Job not available')}<span class="intro-profile-company">${target.company_name ? ' · ' + escapeHtml(target.company_name) : ''}</span></p><div class="intro-profile-meta">${matchScore(target)}<span class="intro-tag">${escapeHtml(target.icp_name || '')}</span>${url ? `<a href="${escapeHtml(url)}" class="intro-profile-link" target="_blank" rel="noopener noreferrer">View LinkedIn profile ↗</a>` : ''}</div></div></div>`;
        byId('introducer-count').textContent = introducers.length ? introducers.length + (introducers.length === 1 ? ' way in' : ' ways in') + ' · select one' : 'Finding a mutual connection';
        byId('introducers').innerHTML = introducers.length ? introducers.map(person => {
            const selected = String(person.id) === String(state.introducer);
            const isStar = state.stars.some(star => String(star.id) === String(person.id));
            return `<label class="intro-connection-card"><input type="radio" name="intro-introducer" value="${escapeHtml(person.id)}" aria-label="Ask ${escapeHtml(person.name)}" ${selected ? 'checked' : ''} ${person.can_schedule === false ? 'disabled' : ''}><div class="intro-card-avatar-row">${avatar(person)}${isStar ? '<span class="intro-star-indicator" aria-label="Star connection">★</span>' : ''}</div><h3>${escapeHtml(person.name)}</h3><p>${escapeHtml(person.job_title || 'Job not available')}</p><p class="intro-card-company">${escapeHtml(person.company_name || 'Company not available')}</p>${score(person.introduction_score, person.introduction_reasons, true)}<div class="intro-card-bottom"><span>${isStar ? '★ Star connection' : 'Your connection'}</span><span class="intro-radio-text">${selected ? 'Selected ✓' : 'Select'}</span></div>${person.can_schedule === false ? `<p>${escapeHtml(person.unavailable_reason || 'Connect your LinkedIn account to schedule.')}</p>` : ''}</label>`;
        }).join('') : reviewPending(target) ? '<p class="intro-muted">This previously identified profile is saved. An updated match assessment and a mutual connection are needed before you can request an introduction.</p>' : '<p class="intro-muted">This person matches your ideal customer profile. We’re still finding a mutual connection who can introduce you. You can request an introduction here as soon as a connection is available.</p>';
        updateSelection();
        byId('candidates').innerHTML = state.candidates.filter(person => String(person.id) !== String(state.target)).map(person => {
            const count = candidateIntroducers(person).length;
            const label = count ? count + (count === 1 ? ' way in' : ' ways in') : 'Finding a connection';
            return `<article class="intro-candidate-card"><div class="intro-candidate-top">${avatar(person)}<div><h3>${escapeHtml(person.name)}</h3><p>${escapeHtml(person.job_title || 'Job not available')}</p><p>${escapeHtml(person.company_name || 'Company not available')}</p></div></div><div class="intro-candidate-actions">${matchScore(person)}<button type="button" data-target="${escapeHtml(person.id)}" aria-label="View introduction paths for ${escapeHtml(person.name)}">${label} <span aria-hidden="true">→</span></button></div></article>`;
        }).join('') || '<p class="intro-muted">You’re viewing your last current opportunity.</p>';
    }
    function updateSelection() {
        const target = state.candidates.find(person => String(person.id) === String(state.target));
        const person = target && candidateIntroducers(target).find(p => String(p.id) === String(state.introducer));
        byId('ask').disabled = removing || reviewPending(target) || !person || person.can_schedule === false;
        byId('remove').disabled = removing || !target || !!state.pending;
        byId('selection-help').textContent = reviewPending(target) ? 'Your profile is saved. Match review is pending before you can request an introduction.' : person ? `Ask ${person.name.split(' ')[0]} to open the door. You’ll review the message first.` : target && !candidateIntroducers(target).length ? 'Your match is saved. An introduction needs a mutual connection first.' : 'Select one connection to ask for an introduction.';
        root.querySelectorAll('.intro-connection-card').forEach(card => { card.querySelector('.intro-radio-text').textContent = card.querySelector('input').checked ? 'Selected ✓' : 'Select'; });
    }
    async function refresh() {
        const loadId = ++state.loadId;
        byId('icp-open').disabled = true; byId('icp-manage-open').disabled = true; byId('stars-save').disabled = true;
        byId('loading').hidden = false; byId('empty').hidden = true; byId('workspace').hidden = true;
        const resolveEmailChoice = emailChoice && !emailChoiceHandled;
        const results = await Promise.allSettled([request('overview'), resolveEmailChoice ? request('opportunity', 'GET', null, emailChoice) : Promise.resolve(null)]);
        if (loadId !== state.loadId) return;
        const [overviewResult] = results;
        state.icpLoaded = overviewResult.status === 'fulfilled' && Array.isArray(overviewResult.value.icps);
        if (state.icpLoaded) state.icps = overviewResult.value.icps;
        state.overviewLoaded = overviewResult.status === 'fulfilled';
        if (state.overviewLoaded) {
            const data = overviewResult.value;
            state.stars = Array.isArray(data.stars) ? data.stars : [];
            state.scheduled = Array.isArray(data.scheduled) ? data.scheduled : [];
            if (state.pending && state.scheduled.some(item => String(item.target_id) === String(state.pending.target_id))) state.pending = null;
            const queued = new Set(state.scheduled.filter(p => p.status !== 'failed' && p.status !== 'cancelled').map(p => String(p.target_id)));
            state.candidates = eligible(data.candidates).filter(p => !queued.has(String(p.id)));
        } else { state.candidates = []; notice(overviewResult.reason.message, true); }
        byId('icp-open').disabled = !state.icpLoaded;
        byId('icp-manage-open').disabled = !state.icpLoaded;
        byId('stars-save').disabled = !state.overviewLoaded;
        if (!state.icpLoaded) notice('Your ICP could not be loaded. Refresh before editing to keep your saved settings safe.', true);
        let openEmailPreview = false;
        if (resolveEmailChoice) {
            const choiceResult = results[1];
            if (choiceResult.status === 'fulfilled') {
                const candidate = choiceResult.value?.candidate;
                const person = candidate && candidateIntroducers(candidate).find(p => String(p.id) === emailChoice.introducer_id);
                if (candidate && String(candidate.id) === emailChoice.participant_id && String(candidate.icp_id) === emailChoice.icp_id && eligible([candidate]).length && person?.can_schedule === true) {
                    state.candidates = [candidate, ...state.candidates.filter(p => String(p.id) !== String(candidate.id))];
                    state.target = String(candidate.id); state.introducer = String(person.id);
                    openEmailPreview = true;
                } else {
                    notice(person?.unavailable_reason || 'This emailed introduction is no longer available. Refresh your opportunities to choose a current connection.', true);
                }
                emailChoiceHandled = true;
            } else {
                notice(choiceResult.reason.message, true);
            }
        }
        render();
        if (openEmailPreview) {
            openMessagePreview();
            const url = new URL(window.location.href);
            for (const field of ['confirm_introduction', 'icp_id', 'participant_id', 'introducer_id']) url.searchParams.delete(field);
            window.history.replaceState(null, '', url.pathname + url.search + url.hash);
        }
    }
    function openDialog(name) { byId(name + '-dialog').showModal(); }
    root.addEventListener('click', event => {
        if (event.target.closest('[data-manage-stars]')) openStarsDialog();
        if (event.target.closest('[data-add-icp]')) openIcpDialog();
        const icpEdit = event.target.closest('[data-edit-icp]');
        if (icpEdit) openIcpDialog(state.icps.find(icp => String(icp.id) === icpEdit.dataset.editIcp));
        const close = event.target.closest('[data-close]');
        if (close) { const dialog = close.closest('dialog'); if (!(dialog.id === 'intro-message-dialog' && state.sending) && !(dialog.id === 'intro-icp-dialog' && state.savingIcp)) dialog.close(); }
        const candidate = event.target.closest('[data-target]');
        if (candidate) { state.target = candidate.dataset.target; state.introducer = null; render(); byId('target').scrollIntoView({block:'center', behavior:matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'}); }
        const contact = event.target.closest('[data-contact]');
        if (contact) toggleStar(contact.dataset.contact);
        const remove = event.target.closest('[data-remove-star]');
        if (remove) { state.starDraft.delete(remove.dataset.removeStar); renderContacts(); }
    });
    byId('introducers').addEventListener('change', event => { if (event.target.name === 'intro-introducer') { state.introducer = event.target.value; updateSelection(); } });
    byId('retry').addEventListener('click', () => { notice(''); refresh(); });
    byId('history-open').addEventListener('click', () => { renderHistory(); openDialog('history'); });
    function openIcpDialog(icp = null) {
        if (!state.icpLoaded || state.savingIcp) return;
        state.editingIcp = icp;
        state.creationKey = crypto.randomUUID();
        const form = byId('icp-form');
        form.elements.description.value = icp?.description || '';
        form.elements.importance.value = String(icp?.importance || 5);
        byId('importance-value').textContent = form.elements.importance.value + ' / 10';
        byId('icp-dialog-title').textContent = icp ? 'Edit ideal customer profile' : 'Add an ideal customer profile';
        byId('icp-archive').hidden = !icp;
        showError('icp-error', ''); openDialog('icp');
        form.elements.description.focus();
    }
    function setIcpSaving(saving) {
        state.savingIcp = saving;
        byId('icp-form').querySelectorAll('button, input, textarea').forEach(element => element.disabled = saving);
        byId('icp-save').textContent = saving ? 'Saving…' : 'Save ICP';
    }
    byId('icp-open').addEventListener('click', () => openIcpDialog());
    byId('icp-manage-open').addEventListener('click', () => openDialog('icp-manage'));
    byId('icp-dialog').addEventListener('cancel', event => { if (state.savingIcp) event.preventDefault(); });
    byId('icp-form').addEventListener('change', event => {
        if (event.target.name === 'importance') byId('importance-value').textContent = event.target.value + ' / 10';
    });
    byId('icp-form').addEventListener('submit', async event => {
        event.preventDefault(); if (state.savingIcp) return;
        const form = event.currentTarget;
        const payload = {description:form.elements.description.value.trim(), importance:Number(form.elements.importance.value)};
        if (!payload.description) return showError('icp-error', 'Describe who you would like to reach.');
        if (state.editingIcp) Object.assign(payload, {id:state.editingIcp.id, version:state.editingIcp.version});
        else payload.creation_key = state.creationKey;
        setIcpSaving(true); showError('icp-error', '');
        try {
            await request('icps', state.editingIcp ? 'PUT' : 'POST', payload);
            state.target = null; state.introducer = null;
            byId('icp-dialog').close(); notice('ICP saved. Your groups are ordered by importance and matches are being updated.');
            await refresh();
        } catch (error) { showError('icp-error', error.message); }
        finally { setIcpSaving(false); }
    });
    byId('icp-archive').addEventListener('click', async () => {
        if (!state.editingIcp || state.savingIcp) return;
        setIcpSaving(true); showError('icp-error', '');
        try {
            await request('icps', 'DELETE', {id:state.editingIcp.id, version:state.editingIcp.version});
            byId('icp-dialog').close(); notice('ICP archived. Its introduction history has been kept.');
            await refresh();
        } catch (error) { showError('icp-error', error.message); }
        finally { setIcpSaving(false); }
    });
    async function loadContacts() {
        const id = ++state.contactId; showError('star-error', '');
        byId('contact-results').innerHTML = '<p class="intro-muted">Loading your connections…</p>';
        try { const data = await request('contacts', 'GET', null, byId('contact-search').value.trim()); if (id !== state.contactId) return; state.contacts = data.contacts || []; renderContacts(); }
        catch (error) { if (id !== state.contactId) return; byId('contact-results').textContent = ''; showError('star-error', error.message); }
    }
    function renderContacts() {
        byId('star-draft-count').textContent = state.starDraft.size + ' / 10';
        byId('star-selected').innerHTML = [...state.starDraft.values()].map(p => `<span class="intro-star-chip">★ ${escapeHtml(p.name)}<button type="button" data-remove-star="${escapeHtml(p.id)}" aria-label="Remove ${escapeHtml(p.name)} from star connections">×</button></span>`).join('');
        byId('contact-results').innerHTML = state.contacts.length ? state.contacts.map(p => `<div class="intro-contact">${avatar(p)}<div><strong>${escapeHtml(p.name)}</strong><p>${escapeHtml([p.job_title,p.company_name].filter(Boolean).join(' · '))}</p>${profileUrl(p.linkedin_url) ? `<a href="${escapeHtml(profileUrl(p.linkedin_url))}" target="_blank" rel="noopener noreferrer">View LinkedIn profile ↗</a>` : ''}</div><button type="button" data-contact="${escapeHtml(p.id)}" aria-label="${state.starDraft.has(String(p.id)) ? 'Remove' : 'Add'} ${escapeHtml(p.name)}" aria-pressed="${state.starDraft.has(String(p.id))}" ${state.starDraft.size >= 10 && !state.starDraft.has(String(p.id)) ? 'disabled' : ''}>${state.starDraft.has(String(p.id)) ? '★' : '+'}</button></div>`).join('') : '<p class="intro-muted">No synced connections match. Paste a LinkedIn profile URL to add this person manually.</p>';
    }
    function toggleStar(id) {
        if (state.starDraft.has(id)) state.starDraft.delete(id);
        else { const person = state.contacts.find(p => String(p.id) === id); if (person && state.starDraft.size < 10) state.starDraft.set(id, person); }
        showError('star-error', ''); renderContacts();
    }
    function openStarsDialog() {
        state.starDraft = new Map(state.stars.map(p => [String(p.id),p]));
        byId('contact-search').value = '';
        byId('stars-save').disabled = !state.overviewLoaded;
        showError('star-error', ''); renderContacts(); openDialog('stars'); loadContacts();
        if (!state.overviewLoaded) showError('star-error', 'Your saved connections have not loaded. You can search, but refresh the page before saving.');
    }
    byId('stars-open').addEventListener('click', openStarsDialog);
    byId('contact-search').addEventListener('input', () => { ++state.contactId; clearTimeout(searchTimer); searchTimer = setTimeout(loadContacts, 250); });
    byId('stars-save').addEventListener('click', async () => {
        if (!state.overviewLoaded) return;
        const button = byId('stars-save'); button.disabled = true; button.textContent = 'Saving…'; showError('star-error', '');
        try { await request('stars', 'PUT', {participant_ids:[...state.starDraft.values()].map(person => person.manual ? person.linkedin_url : person.id)}); byId('stars-dialog').close(); notice('Your star connections have been saved.'); await refresh(); }
        catch (error) { showError('star-error', error.message); }
        finally { button.disabled = false; button.textContent = 'Save star connections'; }
    });
    byId('remove').addEventListener('click', async () => {
        const target = state.candidates.find(p => String(p.id) === String(state.target));
        if (!target || removing || state.pending || state.sending) return;
        removing = true;
        const button = byId('remove');
        button.textContent = 'Removing…';
        updateSelection();
        try {
            const result = await request('remove', 'POST', {target_id: target.id});
            if (result.removed !== true || String(result.target_id) !== String(target.id)) throw new Error('Removal was not confirmed. Please try again.');
            ++state.loadId;
            state.candidates = state.candidates.filter(p => String(p.id) !== String(target.id));
            if (String(state.target) === String(target.id)) { state.target = null; state.introducer = null; }
            render();
            notice(`Introduction opportunity for ${target.name} removed.`);
        } catch (error) { notice(error.message, true); }
        finally { removing = false; button.textContent = 'Remove Introduction Opportunity'; updateSelection(); }
    });
    function openMessagePreview() {
        if (removing) return;
        const target = state.candidates.find(p => String(p.id) === String(state.target));
        const person = target && candidateIntroducers(target).find(p => String(p.id) === String(state.introducer));
        if (!person || reviewPending(target) || person.can_schedule === false) return;
        if (state.pending && (String(state.pending.target_id) !== String(target.id) || String(state.pending.introducer_id) !== String(person.id))) { notice('Please refresh to check the previous request before scheduling another introduction.', true); return; }
        byId('message-recipient').innerHTML = `${avatar(person)}<div><small>TO YOUR CONNECTION</small><strong>${escapeHtml(person.name)}</strong><p>${escapeHtml([person.job_title,person.company_name].filter(Boolean).join(' · '))}</p></div>`;
        byId('message-context').textContent = `Ask ${person.name.split(' ')[0]} to introduce you to ${target.name}. You can edit the message before sending.`;
        byId('message-text').value = state.pending ? state.pending.message : draftMessage(target, person, config.sender_name || '');
        byId('message-text').readOnly = !!state.pending;
        byId('send').textContent = state.pending ? 'Retry scheduling' : 'Send & schedule →';
        showError('send-error', state.pending ? 'The previous attempt was not confirmed. Retry the same request to avoid a duplicate.' : '');
        openDialog('message');
    }
    byId('ask').addEventListener('click', openMessagePreview);
    byId('message-dialog').addEventListener('cancel', event => { if (state.sending) event.preventDefault(); });
    byId('message-form').addEventListener('submit', async event => {
        event.preventDefault(); if (state.sending) return;
        const message = byId('message-text').value;
        if (!message.trim()) return showError('send-error', 'Please write a message before sending.');
        const target = state.candidates.find(person => String(person.id) === String(state.target));
        if (!state.pending && !target) return showError('send-error', 'This opportunity changed. Refresh and try again.');
        if (!state.pending) state.pending = {target_id:state.target, introducer_id:state.introducer, message, icp_id:target.icp_id, icp_version:target.icp_version, idempotency_key:crypto.randomUUID()};
        const payload = state.pending; state.sending = true;
        const button = byId('send'); button.disabled = true; button.textContent = 'Scheduling…'; byId('message-text').readOnly = true;
        byId('message-dialog').querySelectorAll('[data-close]').forEach(el => el.disabled = true); showError('send-error', '');
        try {
            const data = await request('schedule', 'POST', payload);
            const scheduled = data.request;
            if (!scheduled?.id || !['scheduled', 'sent', 'paused', 'needs_attention'].includes(scheduled.status) || String(scheduled.target_id) !== String(payload.target_id)) throw new Error('Scheduling was not confirmed. Retry to check the same request.');
            state.scheduled = [scheduled,...state.scheduled.filter(item => String(item.id) !== String(scheduled.id))];
            state.candidates = state.candidates.filter(person => String(person.id) !== String(payload.target_id));
            state.target = null; state.introducer = null; state.pending = null;
            byId('message-dialog').close(); render();
            const statusText = scheduled.status === 'scheduled' ? `scheduled${scheduled.scheduled_at ? ' for ' + formatDate(scheduled.scheduled_at) : ''}` : scheduled.status === 'sent' ? 'already sent' : `saved; status: ${statusLabel(scheduled.status).toLowerCase()}. Check Scheduled for details`;
            await refresh();
            notice(`Introduction request to ${scheduled.introducer_name} ${statusText}. ${state.candidates.length ? 'Your next opportunity is ready.' : 'You’re all caught up.'}`, ['paused', 'needs_attention'].includes(scheduled.status));
        } catch (error) {
            const definitive = error.status >= 400 && error.status < 500 && ![408, 429].includes(error.status);
            if (definitive) { state.pending = null; byId('message-text').readOnly = false; }
            showError('send-error', error.message + (definitive ? '' : ' Retry to confirm the same request; it will not be scheduled twice.'));
        } finally {
            state.sending = false; button.disabled = false; button.textContent = state.pending ? 'Retry scheduling' : 'Send & schedule →';
            byId('message-dialog').querySelectorAll('[data-close]').forEach(el => el.disabled = false);
        }
    });
    refresh();
})();
