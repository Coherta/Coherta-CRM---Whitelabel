(function () {
    'use strict';
    const root = document.getElementById('company-activity');
    if (!root) return;
    const el = name => document.getElementById('ca-' + name);
    const categories = {campaigns: 'Campaign activity', leads: 'Leads added', crm: 'Notes and pipeline changes', tasks: 'Tasks', workflows: 'Workflow changes'};
    let busy = false;
    let cursor = null;
    let count = 0;
    let filters = {category: 'all', from: '', to: ''};
    const seen = new Set();

    function setBusy(value) {
        busy = value;
        root.setAttribute('aria-busy', String(value));
        ['refresh', 'more', 'apply', 'reset', 'category', 'from', 'to'].forEach(name => { el(name).disabled = value; });
        el('more').textContent = value ? 'Loading…' : 'Load older activity';
    }

    function text(tag, value, className) {
        const node = document.createElement(tag);
        node.textContent = value || '—';
        if (className) node.className = className;
        return node;
    }

    function renderRow(event) {
        const row = document.createElement('tr');
        const dateCell = document.createElement('td');
        const time = text('time', event.occurred_at.replace('T', ' '));
        time.dateTime = event.occurred_at.replace(' ', 'T');
        dateCell.append(time);
        const activityCell = document.createElement('td');
        activityCell.append(text('span', event.title, 'ca-title'));
        if (event.detail) activityCell.append(text('span', event.detail, 'ca-detail'));
        activityCell.append(text('span', categories[event.category] || 'Activity', 'ca-type'));
        const recordCell = document.createElement('td');
        // Build only local, known destinations; never render API-provided HTML or URLs.
        const campaignId = Number(event.campaign_id);
        const leadId = Number(event.pipeline_company_id);
        let href = '';
        let label = '';
        if (Number.isSafeInteger(campaignId) && campaignId > 0) {
            href = '/app/campaign/campaigns-workflow-builder.php?id=' + campaignId;
            label = event.campaign_name || 'Open campaign';
        } else if (Number.isSafeInteger(leadId) && leadId > 0) {
            href = '/app/lead.php?id=' + leadId;
            label = 'Open lead';
        }
        if (href) {
            const link = text('a', label);
            link.href = href;
            recordCell.append(link);
        } else recordCell.textContent = '—';
        row.append(dateCell, activityCell, text('td', event.subject), text('td', event.actor || 'System'), recordCell);
        return row;
    }

    async function request() {
        const token = document.body.getAttribute('session_id');
        if (!token) throw new Error('Sign in to view company activity.');
        const query = new URLSearchParams(filters);
        if (cursor) query.set('cursor', cursor);
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 30000);
        try {
            const response = await fetch('https://api.coherta.com/users/me/company_activity?' + query, {
                headers: {Accept: 'application/json', Authorization: 'Bearer ' + token},
                cache: 'no-store', signal: controller.signal
            });
            if (response.status === 401) throw new Error('Your session has expired. Sign in again to view company activity.');
            if (!response.ok) throw new Error('Unable to load company activity. Please try again.');
            const data = await response.json();
            if (!data || !Array.isArray(data.events) || !data.company || typeof data.company.name !== 'string'
                || !(data.next_cursor === null || typeof data.next_cursor === 'string')
                || !data.events.every(event => event && typeof event.id === 'string' && typeof event.title === 'string' && typeof event.occurred_at === 'string')) {
                throw new Error('Unable to read company activity. Please try again.');
            }
            return data;
        } catch (error) {
            if (error.name === 'AbortError') throw new Error('Loading activity timed out. Please try again.');
            if (error instanceof TypeError || error instanceof SyntaxError) throw new Error('Unable to connect to company activity. Please try again.');
            throw error;
        } finally { clearTimeout(timeout); }
    }

    async function load(reset) {
        if (busy) return;
        if (reset) {
            cursor = null;
            count = 0;
            seen.clear();
            el('rows').replaceChildren();
            el('results').hidden = true;
            el('more').hidden = true;
            el('count').textContent = '';
            el('state').hidden = false;
            el('state').textContent = 'Loading company activity…';
        }
        el('error').hidden = true;
        setBusy(true);
        try {
            const data = await request();
            el('account').textContent = data.company.name;
            data.events.forEach(event => {
                if (seen.has(event.id)) return;
                seen.add(event.id);
                el('rows').append(renderRow(event));
                count++;
            });
            cursor = data.next_cursor;
            el('results').hidden = count === 0;
            el('state').hidden = count > 0;
            el('state').textContent = filters.category !== 'all' || filters.from || filters.to
                ? 'No activity matches these filters. Try a different type or date range.'
                : 'No company activity has been recorded yet.';
            el('count').textContent = count + (count === 1 ? ' activity shown' : ' activities shown') + (cursor ? '' : ' · All matching activity loaded');
            el('more').hidden = !cursor;
        } catch (error) {
            el('error').textContent = error.message;
            el('error').hidden = false;
            if (!count) {
                el('state').hidden = true;
                el('account').textContent = 'Activity unavailable';
            }
        } finally { setBusy(false); }
    }

    el('filters').addEventListener('submit', event => {
        event.preventDefault();
        if (busy) return;
        if (el('from').value && el('to').value && el('from').value > el('to').value) {
            el('error').textContent = 'The From date must be on or before the To date.';
            el('error').hidden = false;
            return;
        }
        filters = {category: el('category').value, from: el('from').value, to: el('to').value};
        load(true);
    });
    el('reset').addEventListener('click', () => {
        el('filters').reset();
        filters = {category: 'all', from: '', to: ''};
        load(true);
    });
    el('refresh').addEventListener('click', () => load(true));
    el('more').addEventListener('click', () => load(false));
    load(true);
})();
