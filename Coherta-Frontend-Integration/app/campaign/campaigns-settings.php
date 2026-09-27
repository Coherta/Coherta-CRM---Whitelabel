<?php
$PageTitle = 'Campaign Settings';
include 'campaigns-shared.php';
$campaignId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<style>
    .campaign-settings-shell{display:flex;flex-direction:column;gap:16px;}
    .campaign-settings-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:16px 18px;border:1px solid rgba(148,163,184,.22);border-radius:18px;background:linear-gradient(180deg,#fff, #f8fafc);}
    .campaign-settings-head h3{margin:0;font-size:20px;line-height:1.2;}
    .campaign-settings-head p{margin:4px 0 0;color:var(--muted);font-size:13px;line-height:1.45;}
    .campaign-settings-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:16px;align-items:start;}
    .settings-card{border:1px solid rgba(148,163,184,.18);border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.05);overflow:hidden;}
    .settings-card-head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;padding:14px 16px 10px;border-bottom:1px solid rgba(148,163,184,.14);}
    .settings-card-head h4{margin:0;font-size:15px;line-height:1.2;}
    .settings-card-head p{margin:4px 0 0;color:var(--muted);font-size:12px;line-height:1.45;}
    .settings-card-body{padding:14px 16px 16px;}
    .compact-field label{display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:7px;}
    .compact-field .input{min-height:42px;}
    .compact-stack{display:flex;flex-direction:column;gap:12px;}
    .compact-help{margin:7px 0 0;color:var(--muted);font-size:12px;line-height:1.45;}
    .checkbox-grid.compact{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;}
    .checkbox-card{padding:11px 12px;border-radius:14px;border:1px solid rgba(148,163,184,.18);display:flex;gap:10px;align-items:flex-start;background:#fff;}
    .checkbox-card input{margin-top:2px;}
    .checkbox-card strong{font-size:13px;line-height:1.35;display:block;}
    .checkbox-card span{line-height:1.35;}
    .day-grid.compact{display:flex;flex-direction:column;gap:8px;}
    .day-row{display:grid;grid-template-columns:minmax(120px,1.2fr) auto 110px 110px;gap:10px;align-items:center;padding:10px 12px;border:1px solid rgba(148,163,184,.18);border-radius:14px;background:#fff;}
    .day-row.disabled{opacity:.68;background:#f8fafc;}
    .day-toggle{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;}
    .day-row .input{min-height:40px;padding-top:8px;padding-bottom:8px;}
    .actions-bar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:0;}
    .actions-right{display:flex;gap:10px;flex-wrap:wrap;}
    #settingsToast.show{animation:fadeInOut 5s ease forwards;}
    @media (max-width: 1100px){
        .campaign-settings-grid{grid-template-columns:1fr;}
    }
    @media (max-width: 720px){
        .campaign-settings-head{padding:14px;}
        .settings-card-head,.settings-card-body{padding-left:14px;padding-right:14px;}
        .checkbox-grid.compact{grid-template-columns:1fr;}
        .day-row{grid-template-columns:1fr 1fr;}
        .day-row .day-toggle{grid-column:1 / -1;}
        .day-row .day-status-pill{justify-self:start;}
    }
</style>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include('../partial/sidebar.php'); ?>
        <div class="page-body no-top-margin">
            <div class="app-shell">
                <main class="content">
                    <?php campaign_topbar('dashboard'); ?>
                    <section class="campaign-settings-shell">
                        <div class="campaign-settings-head">
                            <div>
                                <h3>Campaign Settings</h3>
                                <p id="campaignSettingsCopy">Adjust campaign name, employees and activity period.</p>
                            </div>
                            <div class="count-pill" id="campaignIdPill">Campaign ID <?php echo (int)$campaignId; ?></div>
                        </div>

                        <div class="campaign-settings-grid">
                            <div class="compact-stack">
                                <div class="settings-card">
                                    <div class="settings-card-head">
                                        <div>
                                            <h4>General</h4>
                                            <p>Core settings for this campaign.</p>
                                        </div>
                                    </div>
                                    <div class="settings-card-body compact-field">
                                        <label for="campaignTitleInput">Campaign name</label>
                                        <input class="input campaign-setting-field" type="text" id="campaignTitleInput" maxlength="255" placeholder="Loading campaign name...">
                                    </div>
                                </div>

                                <div class="settings-card">
                                    <div class="settings-card-head">
                                        <div>
                                            <h4>Active employees</h4>
                                            <p>Choose who can run this campaign.</p>
                                        </div>
                                    </div>
                                    <div class="settings-card-body">
                                        <div class="checkbox-grid compact" id="activeEmployeesGrid">
                                            <div class="empty-state">Loading employees...</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-card">
                                    <div class="settings-card-head">
                                        <div>
                                            <h4>Sales Navigator profile</h4>
                                            <p>Choose which connected profile should run Add new leads searches.</p>
                                        </div>
                                    </div>
                                    <div class="settings-card-body compact-field">
                                        <label for="salesNavigatorProfileInput">Search profile</label>
                                        <select class="select campaign-setting-field" id="salesNavigatorProfileInput">
                                            <option value="">Loading profiles...</option>
                                        </select>
                                        <p class="compact-help" id="salesNavigatorProfileHelp">Only LinkedIn connected employees can be selected.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-card">
                                <div class="settings-card-head">
                                    <div>
                                        <h4>Activity period</h4>
                                        <p>Choose when the campaign is allowed to run.</p>
                                    </div>
                                </div>
                                <div class="settings-card-body">
                                    <div class="day-grid compact" id="activityPeriodGrid">
                                        <div class="empty-state">Loading activity period...</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="actions-bar">
                            <button class="btn btn-outline" type="button" id="deleteCampaignBtn" style="border-color:#dc2626;color:#dc2626;">Delete Campaign</button>
                            <div class="actions-right">
                                <button class="btn btn-outline" type="button" onclick="window.location.href='campaigns-dashboard.php'">Back</button>
                                <button class="btn btn-success" type="button" id="saveCampaignSettingsBtn">Save settings</button>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="settingsToast">Settings saved</div>

<div id="deleteCampaignModal" style="position:fixed; inset:0; background:rgba(15,23,42,0.55); display:none; align-items:center; justify-content:center; padding:24px; z-index:9999;">
    <div style="width:min(100%, 520px); background:#fff; border-radius:18px; box-shadow:0 20px 50px rgba(15,23,42,0.24); padding:24px;">
        <div style="display:flex; align-items:flex-start; gap:14px;">
            <div style="width:44px; height:44px; min-width:44px; border-radius:999px; background:#fee2e2; color:#b91c1c; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:700;">!</div>
            <div>
                <h4 style="margin:0 0 8px;">Delete Campaign</h4>
                <p style="margin:0; color:#475569; line-height:1.55;">Are you sure you want to delete this campaign? When you press <strong>Accept</strong>, the campaign will be deleted and stopped.</p>
            </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px; flex-wrap:wrap;">
            <button class="btn btn-outline" type="button" id="cancelDeleteCampaignBtn">Cancel</button>
            <button class="btn" type="button" id="acceptDeleteCampaignBtn" style="background:#dc2626; color:#fff; border-color:#dc2626;">Accept</button>
        </div>
    </div>
</div>

<script>
    const allEmployees = <?php echo json_encode($campaignEmployees, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;

    (function(){
        const api = window.CohertaCampaignsApi;
        const campaignId = <?php echo (int)$campaignId; ?>;
        const toast = document.getElementById('settingsToast');
        const employeesGrid = document.getElementById('activeEmployeesGrid');
        const activityGrid = document.getElementById('activityPeriodGrid');
        const campaignTitleInput = document.getElementById('campaignTitleInput');
        const salesNavigatorProfileInput = document.getElementById('salesNavigatorProfileInput');
        const salesNavigatorProfileHelp = document.getElementById('salesNavigatorProfileHelp');
        const saveBtn = document.getElementById('saveCampaignSettingsBtn');
        const deleteBtn = document.getElementById('deleteCampaignBtn');
        const deleteModal = document.getElementById('deleteCampaignModal');
        const cancelDeleteBtn = document.getElementById('cancelDeleteCampaignBtn');
        const acceptDeleteBtn = document.getElementById('acceptDeleteCampaignBtn');
        const dayLabels = <?php echo json_encode($dayLabels, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
        let settingsToastTimer = null;

        function esc(value){
            return String(value ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#39;');
        }
        function showToast(message){
            toast.textContent = message || 'Settings saved';
            if (settingsToastTimer) {
                clearTimeout(settingsToastTimer);
                settingsToastTimer = null;
            }
            toast.classList.remove('show');
            void toast.offsetWidth;
            toast.classList.add('show');
            settingsToastTimer = setTimeout(() => {
                toast.classList.remove('show');
                settingsToastTimer = null;
            }, 5000);
        }
        function normalizePeriod(period){
            const normalized = {};
            Object.keys(dayLabels).forEach(key => {
                const row = (period && period[key]) ? period[key] : {};
                normalized[key] = {
                    enabled: Boolean(Number(row.enabled ?? 0)),
                    start: row.start || '08:00',
                    end: row.end || '16:00'
                };
            });
            return normalized;
        }
        function isLinkedInConnected(employee){
            if (!employee || typeof employee !== 'object') return false;
            if (Number(employee.connect) === 1) return true;
            const connector = employee.connectors && employee.connectors.linkedin;
            if (Array.isArray(connector) && connector.some(item => Number(item && item.status) === 1)) return true;
            return Number(employee.linkedin_connected || employee.is_linkedin_connected || employee.linkedin_status) === 1;
        }
        function firstFilledNumber(sources, keys){
            for (const source of sources) {
                if (!source || typeof source !== 'object') continue;
                for (const key of keys) {
                    const value = source[key];
                    if (value !== undefined && value !== null && String(value).trim() !== '') return Number(value);
                }
            }
            return 0;
        }
        function getSalesNavigatorProfileId(payload){
            return firstFilledNumber([payload && payload.campaign, payload], [
                'navigator_profile',
                'navigator',
                'sales_navigator',
                'sales_navigator_id',
                'sales_navigator_profile_id',
                'sales_navigator_employee_id',
                'sales_navigator_user_id',
                'navigator_employee_id',
                'navigator_user_id',
                'linkedin_search_employee_id',
                'linkedin_search_user_id',
                'lead_search_employee_id',
                'lead_search_user_id',
                'add_leads_employee_id',
                'add_leads_user_id'
            ]);
        }
        function renderSalesNavigatorProfiles(employees, selectedId){
            const allEmployeesMap = new Map(
                (allEmployees || []).map(employee => [Number(employee.id), employee])
            );
            const employeeList = (employees || [])
                .map(employee => allEmployeesMap.get(Number(employee.id)) || employee);
            (allEmployees || []).forEach(employee => {
                if (!employeeList.some(item => Number(item.id) === Number(employee.id))) employeeList.push(employee);
            });
            const connectedEmployees = employeeList.filter(isLinkedInConnected);
            const selectedEmployee = selectedId
                ? (employeeList.find(employee => Number(employee.id) === Number(selectedId)) || {id: selectedId, full_name: 'User #' + selectedId})
                : null;
            if (selectedEmployee && !connectedEmployees.some(employee => Number(employee.id) === Number(selectedId))) {
                connectedEmployees.unshift(selectedEmployee);
            }
            salesNavigatorProfileInput.innerHTML = '<option value="">Campaign default</option>' + connectedEmployees.map(employee => {
                const id = Number(employee.id);
                const name = employee.full_name || employee.email || 'Unknown user';
                const email = employee.email ? ' - ' + employee.email : '';
                return '<option value="' + id + '">' + esc(name + email) + '</option>';
            }).join('');
            salesNavigatorProfileInput.value = selectedId ? String(selectedId) : '';
            salesNavigatorProfileInput.disabled = !connectedEmployees.length;
            salesNavigatorProfileHelp.textContent = selectedEmployee && !isLinkedInConnected(selectedEmployee)
                ? 'This profile is saved on the campaign, but LinkedIn is not marked as connected.'
                : (connectedEmployees.length
                    ? 'This profile is used for Add new leads to campaign searches when the source is Sales Navigator or LinkedIn search.'
                    : 'No LinkedIn connected employees are available for Sales Navigator searches.');
        }
        function renderEmployees(employees, activeEmployees) {
            const activeIds = new Set(
                (activeEmployees || []).map(item => Number(item.id || item.employee_id || item))
            );

            const allEmployeesMap = new Map(
                (allEmployees || []).map(employee => [Number(employee.id), employee])
            );

            employeesGrid.innerHTML = (employees || []).map(employee => {
                const employeeId = Number(employee.id);
                const fullEmployee = allEmployeesMap.get(employeeId) || employee;

                const isLinkedInConnected = Number(fullEmployee.connect) === 1;

                return `
            <label class="checkbox-card">
                <input class="campaign-setting-field employee-checkbox" type="checkbox" value="${employeeId}" ${activeIds.has(employeeId) ? 'checked' : ''}>
                <div>
                    <strong>${esc(fullEmployee.full_name || employee.full_name || 'Unknown user')}</strong><br>
                    <span style="color:var(--muted);font-size:13px;">${esc(fullEmployee.email || employee.email || 'No email')}</span>
                    ${!isLinkedInConnected ? `
                        <div style="display:flex;align-items:center;gap:8px;margin-top:8px;color:#b45309;font-size:12px;font-weight:600;">
                            <span aria-hidden="true" style="font-size:14px;line-height:1;">⚠</span>
                            <span>LinkedIn is not yet connected</span>
                        </div>
                    ` : ''}
                </div>
            </label>
        `;
            }).join('');
        }
        function renderActivity(period){
            const normalized = normalizePeriod(period);
            activityGrid.innerHTML = Object.keys(dayLabels).map(key => {
                const row = normalized[key];
                return `
        <div class="day-row${row.enabled ? '' : ' disabled'}" data-day="${key}">
          <label class="day-toggle"><input class="day-enabled campaign-setting-field" type="checkbox" ${row.enabled ? 'checked' : ''}> ${esc(dayLabels[key])}</label>
          <span class="pill day-status-pill">${row.enabled ? 'Active' : 'Off'}</span>
          <input class="input campaign-setting-field day-start" type="time" value="${esc(row.start)}">
          <input class="input campaign-setting-field day-end" type="time" value="${esc(row.end)}">
        </div>
      `;
            }).join('');
            activityGrid.querySelectorAll('.day-enabled').forEach(input => {
                input.addEventListener('change', function(){
                    const row = this.closest('.day-row');
                    row.classList.toggle('disabled', !this.checked);
                    row.querySelector('.day-status-pill').textContent = this.checked ? 'Active' : 'Off';
                });
            });
        }
        function openDeleteModal(){
            deleteModal.style.display = 'flex';
        }
        function closeDeleteModal(){
            deleteModal.style.display = 'none';
        }
        async function loadSettings(){
            if (!campaignId) {
                campaignTitleInput.value = '';
                campaignTitleInput.disabled = true;
                salesNavigatorProfileInput.innerHTML = '<option value="">Missing campaign ID</option>';
                salesNavigatorProfileInput.disabled = true;
                salesNavigatorProfileHelp.textContent = 'Choose a campaign before selecting a Sales Navigator profile.';
                employeesGrid.innerHTML = '<div class="empty-state">Missing campaign ID.</div>';
                activityGrid.innerHTML = '<div class="empty-state">Missing campaign ID.</div>';
                saveBtn.disabled = true;
                deleteBtn.disabled = true;
                return;
            }
            try {
                const payload = await api.request('/campaigns/settings?campaign_id=' + encodeURIComponent(campaignId));
                const campaignTitle = payload.campaign && (payload.campaign.title || payload.campaign.name) ? (payload.campaign.title || payload.campaign.name) : ('Campaign #' + campaignId);
                document.getElementById('campaignIdPill').textContent = 'Campaign ID ' + campaignId;
                document.getElementById('campaignSettingsCopy').innerHTML = 'Adjust campaign name, active employees and campaign activity period for <strong>' + esc(campaignTitle) + '</strong>.';
                campaignTitleInput.disabled = false;
                campaignTitleInput.value = campaignTitle;
                campaignTitleInput.placeholder = 'Enter campaign name';
                renderEmployees(payload.company_employees || [], payload.employees || []);
                renderSalesNavigatorProfiles(payload.company_employees || [], getSalesNavigatorProfileId(payload));
                renderActivity(payload.activity_period || {});
            } catch (error) {
                campaignTitleInput.value = '';
                campaignTitleInput.disabled = true;
                salesNavigatorProfileInput.innerHTML = '<option value="">Could not load profiles</option>';
                salesNavigatorProfileInput.disabled = true;
                salesNavigatorProfileHelp.textContent = 'Could not load Sales Navigator profiles.';
                employeesGrid.innerHTML = '<div class="empty-state">' + esc(error.message) + '</div>';
                activityGrid.innerHTML = '<div class="empty-state">Could not load activity period.</div>';
                saveBtn.disabled = true;
                deleteBtn.disabled = true;
            }
        }
        async function saveSettings(){
            const campaignTitle = campaignTitleInput.value.trim();
            const employeeIds = Array.from(document.querySelectorAll('.employee-checkbox:checked')).map(el => Number(el.value));
            const salesNavigatorProfileId = Number(salesNavigatorProfileInput.value || 0);
            const activityPeriod = {};
            activityGrid.querySelectorAll('.day-row').forEach(row => {
                const day = row.dataset.day;
                activityPeriod[day] = {
                    enabled: row.querySelector('.day-enabled').checked ? 1 : 0,
                    start: row.querySelector('.day-start').value,
                    end: row.querySelector('.day-end').value
                };
            });
            if (!campaignTitle) {
                alert('Campaign name is required.');
                campaignTitleInput.focus();
                return;
            }
            try {
                saveBtn.classList.add('is-loading');
                const payload = {
                    campaign_id: campaignId,
                    title: campaignTitle,
                    campaign_name: campaignTitle,
                    name: campaignTitle,
                    employee_ids: employeeIds,
                    activity_period: activityPeriod,
                    navigator: salesNavigatorProfileId || null,
                    sales_navigator: salesNavigatorProfileId || null,
                    sales_navigator_id: salesNavigatorProfileId || null,
                    sales_navigator_profile_id: salesNavigatorProfileId || null,
                    sales_navigator_employee_id: salesNavigatorProfileId || null,
                    sales_navigator_user_id: salesNavigatorProfileId || null,
                    navigator_employee_id: salesNavigatorProfileId || null,
                    navigator_user_id: salesNavigatorProfileId || null,
                    linkedin_search_employee_id: salesNavigatorProfileId || null,
                    lead_search_employee_id: salesNavigatorProfileId || null
                };
                await api.request('/campaigns/settings', {
                    method: 'PATCH',
                    body: JSON.stringify(payload)
                });
                document.getElementById('campaignSettingsCopy').innerHTML = 'Adjust campaign name, active employees and campaign activity period for <strong>' + esc(campaignTitle) + '</strong>.';
                showToast('Settings saved');
            } catch (error) {
                alert(error.message);
            } finally {
                saveBtn.classList.remove('is-loading');
            }
        }
        async function deleteCampaign(){
            try {
                acceptDeleteBtn.disabled = true;
                deleteBtn.classList.add('is-loading');
                await api.request(`/campaigns?id=${campaignId}`, {
                    method: 'DELETE',
                    body: JSON.stringify({ id: campaignId })
                });
                closeDeleteModal();
                showToast('Campaign deleted');
                window.location.href = 'campaigns-dashboard.php';
            } catch (error) {
                alert(error.message);
            } finally {
                acceptDeleteBtn.disabled = false;
                deleteBtn.classList.remove('is-loading');
            }
        }

        saveBtn.addEventListener('click', saveSettings);
        deleteBtn.addEventListener('click', openDeleteModal);
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        acceptDeleteBtn.addEventListener('click', deleteCampaign);
        deleteModal.addEventListener('click', function(event){
            if (event.target === deleteModal) closeDeleteModal();
        });
        document.addEventListener('keydown', function(event){
            if (event.key === 'Escape' && deleteModal.style.display === 'flex') closeDeleteModal();
        });

        loadSettings();
    })();
</script>
<?php include('../partial/footer.php'); include('../partial/scripts.php'); include('../partial/footer-end.php'); ?>
