<?php
require_once "./base.php";
$PageTitle = "Clean my connections";
include('partial/header.php');
?>
<style>
    .clean-connections-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
    }
    .clean-criteria-row {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(180px, 1fr) auto;
        gap: 12px;
        align-items: end;
        margin-bottom: 12px;
    }
    .clean-connection-table th,
    .clean-connection-table td {
        vertical-align: middle;
    }
    .clean-connection-profile {
        min-width: 220px;
    }
    .clean-connection-name {
        font-weight: 600;
        color: #2b2b2b;
    }
    .clean-connection-meta {
        color: #6c757d;
        font-size: 13px;
    }
    .clean-selection-bar {
        position: fixed;
        right: 24px;
        bottom: 24px;
        left: 304px;
        z-index: 1040;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        background: #ffffff;
        border: 1px solid #e6edef;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
        padding: 14px 16px;
    }
    .clean-selection-bar.is-visible {
        display: flex;
    }
    .clean-selection-summary {
        color: #2b2b2b;
        font-weight: 600;
    }
    @media (max-width: 767px) {
        .clean-criteria-row {
            grid-template-columns: 1fr;
        }
        .clean-connections-toolbar {
            align-items: stretch;
        }
        .clean-connections-toolbar .btn {
            width: 100%;
        }
        .clean-selection-bar {
            right: 12px;
            bottom: 12px;
            left: 12px;
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
<?php include('partial/loader.php') ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <?php include('partial/topbar.php'); ?>
    <div class="page-body-wrapper">
        <?php include('partial/sidebar.php'); ?>
        <div class="page-body">
            <?php include('partial/breadcrumb.php'); ?>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card" id="cleanConnectionsSearchCard">
                            <div class="card-header">
                                <div class="clean-connections-toolbar">
                                    <h5 class="mb-0">Clean my connections</h5>
                                    <button class="btn btn-outline-primary" type="button" id="addCriteriaBtn" style="display: none;">
                                        <i data-feather="plus"></i> Add criteria
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="cleanConnectionsForm" autocomplete="off">
                                    <div id="criteriaRows"></div>
                                    <div class="mb-3" role="alert">
                                        This process usually take 2-3 minutes. You are welcome to stay on this page to get the result; otherwise we will send you an email when the list is ready.
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-primary" type="submit" id="findConnectionsBtn">
                                            <i data-feather="search"></i> Find connections
                                        </button>
                                        <button class="btn btn-light" type="button" id="resetCriteriaBtn">Reset</button>
                                    </div>
                                </form>
                                <div class="alert mt-3 mb-0 d-none" id="cleanStatus" role="alert"></div>
                            </div>
                        </div>

                        <div class="card d-none" id="connectionsCard">
                            <div class="card-header">
                                <div class="clean-connections-toolbar">
                                    <div>
                                        <h5 class="mb-1">Matching connections</h5>
                                        <div class="text-muted" id="connectionsCount">0 connections found</div>
                                    </div>
                                    <button class="btn btn-danger" type="button" id="disconnectSelectedBtn">
                                        <i data-feather="user-x"></i> Disconnect selected
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover clean-connection-table">
                                        <thead>
                                        <tr>
                                            <th style="width:44px;">
                                                <input class="form-check-input" type="checkbox" id="selectAllConnections">
                                            </th>
                                            <th>Connection</th>
                                            <th>LinkedIn URL</th>
                                            <th>Country</th>
                                            <th>Position</th>
                                            <th>Company</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody id="connectionsBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('partial/footer.php'); ?>
    </div>
</div>

<div class="clean-selection-bar" id="cleanSelectionBar">
    <div class="clean-selection-summary" id="cleanSelectionSummary">0 connections selected</div>
    <button class="btn btn-danger" type="button" id="disconnectSelectedBarBtn">
        <i data-feather="user-x"></i> Disconnect selected
    </button>
</div>

<script>
    const cleanConnectionsEndpoint = 'https://api.coherta.com/me/linked/clean';
    const cleanConnectionsToken = document.body.getAttribute('session_id') || '';
    const cleanConnectionsSearchCard = document.getElementById('cleanConnectionsSearchCard');
    const criteriaRows = document.getElementById('criteriaRows');
    const cleanStatus = document.getElementById('cleanStatus');
    const connectionsCard = document.getElementById('connectionsCard');
    const connectionsBody = document.getElementById('connectionsBody');
    const connectionsCount = document.getElementById('connectionsCount');
    const selectAllConnections = document.getElementById('selectAllConnections');
    const disconnectSelectedBtn = document.getElementById('disconnectSelectedBtn');
    const cleanSelectionBar = document.getElementById('cleanSelectionBar');
    const cleanSelectionSummary = document.getElementById('cleanSelectionSummary');
    const disconnectSelectedBarBtn = document.getElementById('disconnectSelectedBarBtn');
    let currentConnections = [];
    const selectedConnectionIdSet = new Set();
    let cleanConnectionsPollTimer = null;
    let cleanConnectionsPollAttempts = 0;
    const cleanConnectionsPollIntervalMs = 10000;
    const cleanConnectionsMaxPollAttempts = 18;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showStatus(message, type) {
        cleanStatus.textContent = message;
        cleanStatus.className = 'alert mt-3 mb-0 alert-' + type;
    }

    function hideStatus() {
        cleanStatus.className = 'alert mt-3 mb-0 d-none';
        cleanStatus.textContent = '';
    }

    function splitCountries(value) {
        return String(value || '')
            .split(',')
            .map(function (country) { return country.trim(); })
            .filter(Boolean);
    }

    function uniqueValues(values) {
        return Array.from(new Set(values.filter(Boolean)));
    }

    function addCriteriaRow(country = '', jobPosition = '') {
        const row = document.createElement('div');
        row.className = 'clean-criteria-row';
        row.innerHTML = `
            <div>
                <label class="form-label">Country</label>
                <input class="form-control clean-country" type="text" placeholder="Denmark, Sweden" value="${escapeHtml(country)}">
            </div>
            <div>
                <label class="form-label">Job position</label>
                <input class="form-control clean-job-position" type="text" placeholder="Sales Manager" value="${escapeHtml(jobPosition)}">
            </div>
        `;
        criteriaRows.appendChild(row);
        if (window.feather) feather.replace();
    }

    function getCriteria() {
        return Array.from(criteriaRows.querySelectorAll('.clean-criteria-row'))
            .map(function (row) {
                const countries = splitCountries(row.querySelector('.clean-country').value);
                return {
                    countries: countries,
                    job_position: row.querySelector('.clean-job-position').value.trim()
                };
            })
            .filter(function (criteria) {
                return criteria.countries.length > 0 || criteria.job_position !== '';
            });
    }

    function payloadStatus(payload) {
        return String(payload?.status || payload?.request?.status || payload?.data?.status || payload?.result?.status || '').toLowerCase();
    }

    function payloadRequestId(payload) {
        return payload?.request?.id || payload?.id || payload?.data?.request?.id || payload?.data?.id || null;
    }

    function requestIdFromUrl() {
        const params = new URLSearchParams(window.location.search);
        return params.get('request') || params.get('request_id') || params.get('id') || params.get('`request') || '';
    }

    function updateUrlWithRequestId(requestId) {
        if (!requestId || !window.history || !window.history.replaceState) return;
        const url = new URL(window.location.href);
        url.search = '';
        url.searchParams.set('id', requestId);
        window.history.replaceState({}, '', url.toString());
    }

    function payloadParticipantsFound(payload) {
        const value = payload?.participants_found || payload?.request?.participants_found || payload?.data?.participants_found;
        return Number.isFinite(Number(value)) ? Number(value) : null;
    }

    function parseConnections(payload) {
        if (Array.isArray(payload)) return payload;
        if (Array.isArray(payload?.participants)) return payload.participants;
        if (Array.isArray(payload?.connections)) return payload.connections;
        if (Array.isArray(payload?.data)) return payload.data;
        if (Array.isArray(payload?.results)) return payload.results;
        if (Array.isArray(payload?.items)) return payload.items;
        return [];
    }

    function connectionId(connection) {
        return String(connection.id || connection.connection_id || connection.linkedin_id || connection.profile_id || connection.public_identifier || '');
    }

    function selectedConnectionIds() {
        const visibleIds = new Set(currentConnections.map(connectionId).filter(Boolean));
        return Array.from(selectedConnectionIdSet).filter(function (id) {
            return visibleIds.has(id);
        });
    }

    function selectableConnectionCheckboxes() {
        return Array.from(connectionsBody.querySelectorAll('.clean-connection-check:not(:disabled)'));
    }

    function updateDisconnectButton() {
        const selected = selectedConnectionIds();
        const selectable = selectableConnectionCheckboxes();
        const hasSelection = selected.length > 0;
        const allSelected = selectable.length > 0 && selected.length === selectable.length;
        const label = selected.length > 0 ? 'Disconnect selected (' + selected.length + ')' : 'Disconnect selected';

        selectAllConnections.checked = allSelected;
        selectAllConnections.indeterminate = selected.length > 0 && !allSelected;
        disconnectSelectedBtn.disabled = !hasSelection;
        disconnectSelectedBarBtn.disabled = !hasSelection;
        disconnectSelectedBtn.textContent = label;
        disconnectSelectedBarBtn.textContent = label;
        cleanSelectionSummary.textContent = allSelected
            ? 'All ' + selected.length + ' connections selected'
            : selected.length + (selected.length === 1 ? ' connection selected' : ' connections selected');
        cleanSelectionBar.classList.toggle('is-visible', hasSelection);
    }

    function renderConnections(connections) {
        currentConnections = connections;
        const visibleIds = new Set(currentConnections.map(connectionId).filter(Boolean));
        Array.from(selectedConnectionIdSet).forEach(function (id) {
            if (!visibleIds.has(id)) selectedConnectionIdSet.delete(id);
        });
        connectionsBody.innerHTML = '';
        selectAllConnections.checked = false;
        selectAllConnections.indeterminate = false;
        connectionsCard.classList.remove('d-none');
        connectionsCount.textContent = connections.length + (connections.length === 1 ? ' connection found' : ' connections found');

        if (connections.length === 0) {
            connectionsBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No matching connections found.</td></tr>';
            updateDisconnectButton();
            return;
        }

        connections.forEach(function (connection, index) {
            const id = connectionId(connection);
            const isSelected = id && selectedConnectionIdSet.has(id);
            const name = connection.full_name || connection.name || [connection.first_name, connection.last_name].filter(Boolean).join(' ') || 'Unknown connection';
            const headline = connection.headline || connection.title || '';
            const country = connection.country || connection.location || '';
            const position = connection.job_position || connection.job_title || connection.position || connection.current_position || '';
            const company = connection.company || connection.company_name || connection.organization || '';
            const status = Number(connection.accepted_for_deletion) === 1
                ? 'set for disconnection'
                : (connection.disconnect_status || connection.status || 'ready');
            const linkedinUrl = connection.linkedin_url || connection.profile_url || connection.url || '';
            const profile = escapeHtml(name);
            const linkedinProfileUrl = linkedinUrl
                ? '<a href="' + escapeHtml(linkedinUrl) + '" target="_blank" rel="noreferrer">' + escapeHtml(linkedinUrl) + '</a>'
                : '<span class="text-muted">-</span>';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input class="form-check-input clean-connection-check" type="checkbox" value="${escapeHtml(id)}" data-index="${index}" ${isSelected ? 'checked' : ''}>
                </td>
                <td class="clean-connection-profile">
                    <div class="clean-connection-name">${profile}</div>
                    <div class="clean-connection-meta">${escapeHtml(headline)}</div>
                </td>
                <td>${linkedinProfileUrl}</td>
                <td>${escapeHtml(country)}</td>
                <td>${escapeHtml(position)}</td>
                <td>${escapeHtml(company)}</td>
                <td><span class="badge badge-light-primary">${escapeHtml(status)}</span></td>
            `;
            connectionsBody.appendChild(tr);
        });

        updateDisconnectButton();
    }

    function markConnectionsUpForDeletion(ids) {
        currentConnections = currentConnections.map(function (connection) {
            if (!ids.includes(connectionId(connection))) return connection;
            return Object.assign({}, connection, {
                status: 'set for disconnection',
                disconnect_status: 'set for disconnection'
            });
        });
        renderConnections(currentConnections);
    }

    function stopCleanConnectionsPolling() {
        if (cleanConnectionsPollTimer) {
            window.clearTimeout(cleanConnectionsPollTimer);
            cleanConnectionsPollTimer = null;
        }
        cleanConnectionsPollAttempts = 0;
    }

    function updateSearchStatus(data) {
        const status = payloadStatus(data);
        const participantsFound = payloadParticipantsFound(data);
        const foundText = participantsFound !== null ? ' ' + participantsFound + ' matches identified.' : '';

        if (status === 'failed') {
            showStatus('Connection search failed. Please try again.', 'danger');
            return true;
        }

        if (status === 'completed') {
            showStatus('Connection search completed.' + foundText, 'success');
            return true;
        }

        showStatus('Connection search is still processing.' + foundText + ' We check for identified matches every 10 seconds.', 'info');
        return false;
    }

    async function requestCleanConnections(method, payload, url = cleanConnectionsEndpoint) {
        const options = {
            method: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + cleanConnectionsToken
            }
        };
        if (method !== 'GET') {
            options.body = JSON.stringify(payload);
        }

        const response = await fetch(url, options);
        const text = await response.text();
        let data = null;
        try {
            data = text ? JSON.parse(text) : null;
        } catch (error) {
            data = { detail: text };
        }
        if (!response.ok) {
            throw new Error(data?.detail || data?.message || 'Request failed with status ' + response.status);
        }
        return data;
    }

    function cleanConnectionsRequestUrl(requestId) {
        const separator = cleanConnectionsEndpoint.includes('?') ? '&' : '?';
        return cleanConnectionsEndpoint + separator + 'request_id=' + encodeURIComponent(requestId);
    }

    async function loadCleanConnectionsRequest(requestId, shouldPoll = true) {
        stopCleanConnectionsPolling();
        showStatus('Loading saved connection search...', 'info');

        try {
            const data = await requestCleanConnections('GET', null, cleanConnectionsRequestUrl(requestId));
            const connections = parseConnections(data);
            if (connections.length > 0 || payloadStatus(data) === 'completed') {
                renderConnections(connections);
            }

            if (updateSearchStatus(data)) {
                return;
            }

            if (shouldPoll) {
                cleanConnectionsPollAttempts = 0;
                pollCleanConnections(requestId);
            } else {
            }
        } catch (error) {
            showStatus(error.message || 'Could not load saved connection search.', 'danger');
        }
    }

    function pollCleanConnections(requestId) {
        if (!requestId || cleanConnectionsPollAttempts >= cleanConnectionsMaxPollAttempts) {
            stopCleanConnectionsPolling();
            showStatus('The search is still running. We will send you an email when the list is ready.', 'info');
            return;
        }

        cleanConnectionsPollTimer = window.setTimeout(async function () {
            cleanConnectionsPollAttempts += 1;
            try {
                const data = await requestCleanConnections('GET', null, cleanConnectionsRequestUrl(requestId));
                const connections = parseConnections(data);
                if (connections.length > 0) {
                    renderConnections(connections);
                }

                if (updateSearchStatus(data)) {
                    stopCleanConnectionsPolling();
                    return;
                }

                pollCleanConnections(requestId);
            } catch (error) {
                stopCleanConnectionsPolling();
                showStatus(error.message || 'Could not check for identified matches.', 'danger');
            }
        }, cleanConnectionsPollIntervalMs);
    }

    document.getElementById('addCriteriaBtn').addEventListener('click', function () {
        addCriteriaRow();
    });

    document.getElementById('resetCriteriaBtn').addEventListener('click', function () {
        stopCleanConnectionsPolling();
        criteriaRows.innerHTML = '';
        connectionsCard.classList.add('d-none');
        selectedConnectionIdSet.clear();
        hideStatus();
        updateDisconnectButton();
        addCriteriaRow();
    });

    document.getElementById('cleanConnectionsForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        const criteria = getCriteria();
        if (criteria.length === 0) {
            showStatus('Add at least one country or job position criteria.', 'warning');
            return;
        }

        const submitBtn = document.getElementById('findConnectionsBtn');
        stopCleanConnectionsPolling();
        currentConnections = [];
        selectedConnectionIdSet.clear();
        connectionsCard.classList.add('d-none');
        connectionsBody.innerHTML = '';
        updateDisconnectButton();
        showStatus('Finding LinkedIn connections. This can take up to 3 minutes; we will check for identified matches every 10 seconds.', 'info');

        try {
            const countries = uniqueValues(criteria.flatMap(function (item) {
                return item.countries;
            }));
            const jobPositions = uniqueValues(criteria.map(function (item) {
                return item.job_position;
            }));
            const payload = {
                countries: countries,
                job_position: jobPositions.join(', ')
            };
            const data = await requestCleanConnections('POST', payload);
            const connections = parseConnections(data);
            if (connections.length > 0 || payloadStatus(data) === 'completed') {
                renderConnections(connections);
            }
            if (updateSearchStatus(data)) {
                return;
            }

            const requestId = payloadRequestId(data);
            updateUrlWithRequestId(requestId);
            pollCleanConnections(requestId);
        } catch (error) {
            showStatus(error.message || 'Could not fetch connections.', 'danger');
        }
    });

    selectAllConnections.addEventListener('change', function (event) {
        const shouldSelect = event.target.checked;
        selectableConnectionCheckboxes().forEach(function (checkbox) {
            if (shouldSelect) {
                selectedConnectionIdSet.add(checkbox.value);
            } else {
                selectedConnectionIdSet.delete(checkbox.value);
            }
            checkbox.checked = shouldSelect;
        });
        updateDisconnectButton();
    });

    connectionsBody.addEventListener('change', function (event) {
        if (!event.target.classList.contains('clean-connection-check')) return;
        if (event.target.checked) {
            selectedConnectionIdSet.add(event.target.value);
        } else {
            selectedConnectionIdSet.delete(event.target.value);
        }
        updateDisconnectButton();
    });

    async function disconnectSelectedConnections() {
        const ids = selectedConnectionIds();
        if (ids.length === 0) return;
        if (!window.confirm('Disconnect ' + ids.length + ' selected LinkedIn connection(s)?')) return;

        showStatus('Sending disconnect request...', 'info');

        try {
            const selectedConnections = currentConnections
                .filter(function (connection) {
                    return ids.includes(connectionId(connection));
                })
                .map(function (connection) {
                    return Object.assign({}, connection, {
                        status: 'set for disconnection',
                        disconnect_status: 'set for disconnection'
                    });
                });

            await requestCleanConnections('PATCH', {
                action: 'disconnect',
                status: 'set for disconnection',
                connection_ids: ids,
                connections: selectedConnections
            });
            ids.forEach(function (id) { selectedConnectionIdSet.delete(id); });
            markConnectionsUpForDeletion(ids);
            showStatus('Selected connections were marked as set for disconnection.', 'success');
        } catch (error) {
            showStatus(error.message || 'Could not disconnect selected connections.', 'danger');
            updateDisconnectButton();
        }
    }

    disconnectSelectedBtn.addEventListener('click', disconnectSelectedConnections);
    disconnectSelectedBarBtn.addEventListener('click', disconnectSelectedConnections);

    addCriteriaRow();

    const historicalRequestId = requestIdFromUrl();
    if (historicalRequestId) {
        cleanConnectionsSearchCard.classList.add('d-none');
        loadCleanConnectionsRequest(historicalRequestId);
    }
</script>

<?php include('partial/scripts.php'); ?>
<?php include('partial/footer-end.php'); ?>
