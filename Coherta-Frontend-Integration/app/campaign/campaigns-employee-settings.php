<?php
require_once 'campaigns-shared.php';
?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include('../partial/sidebar.php'); ?>
        <div class="page-body no-top-margin">
            <div class="app-shell">
                <main class="content">
                    <?php campaign_topbar('employees'); ?>

                    <section class="panel hero">
                        <div class="panel-inner">
                            <div class="hero-grid">
                                <div>
                                    <h2>Activity Settings</h2>
                                    <p>Default values are shown even when no row exists in the backend yet. A settings row is only created when a slider is changed and saved.</p>
                                </div>
                                <div class="hero-stats">
                                    <div class="hero-stat"><strong id="employeeCount">0</strong><span>Active employees</span></div>
                                    <div class="hero-stat"><strong>5</strong><span>Daily activity controls</span></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="panel" style="margin-top:22px;">
                        <div class="panel-inner">
                            <div class="section-head">
                                <div>
                                    <h3>Employee pacing</h3>
                                    <p>Each slider shows the current daily target and its allowed maximum.</p>
                                </div>
                                <div class="count-pill" id="employeeSettingsStatus">Loading…</div>
                            </div>
                            <div id="employeeSettingsList" class="grid-2"></div>
                        </div>
                    </section>

                </main>
            </div>
        </div>
        <?php include('../partial/footer.php'); ?>
    </div>
</div>
<div class="toast" id="settingsToast">Settings saved</div>
<?php include('../partial/scripts.php'); ?>
<script>
(function(){
    const api = window.CohertaCampaignsApi;
    const listEl = document.getElementById('employeeSettingsList');
    const statusEl = document.getElementById('employeeSettingsStatus');
    const countEl = document.getElementById('employeeCount');
    const toastEl = document.getElementById('settingsToast');
    const settingMeta = [
        { key:'connection_requests', label:'Connection requests', maxLabel:'max 70', min:0, defaultValue:45 },
        { key:'messages', label:'Messages', maxLabel:'max 80', min:0, defaultValue:70 },
        { key:'profile_views', label:'Profile views', maxLabel:'max 100', min:0, defaultValue:60 },
        { key:'like_posts', label:'Like posts', maxLabel:'max 50', min:0 },
        { key:'endorse_skills', label:'Endorse skills', maxLabel:'max 250', min:0 }
    ];

    function initials(name){
        return String(name || '').split(/\s+/).filter(Boolean).slice(0,2).map(x => x.charAt(0).toUpperCase()).join('');
    }

    function showToast(text){
        toastEl.textContent = text || 'Settings saved';
        toastEl.classList.remove('show');
        void toastEl.offsetWidth;
        toastEl.classList.add('show');
    }

    function sliderRow(employee, meta, value, maxValue){
        return `
            <div class="slider-row">
                <div>
                    <div class="slider-label">${meta.label}</div>
                    <div class="page-copy" style="margin-top:4px;"><p>${value} per day · ${meta.maxLabel}</p></div>
                </div>
                <input type="range" min="${meta.min}" max="${maxValue}" step="1" value="${value}" data-key="${meta.key}" data-employee-id="${employee.id}" />
                <div class="slider-value" data-value-for="${employee.id}-${meta.key}">${value}</div>
            </div>`;
    }

    function renderEmployees(employees){
        countEl.textContent = String(employees.length);
        statusEl.textContent = employees.length + ' employees loaded';
        listEl.innerHTML = employees.map(employee => {
            const settings = employee.settings || {};
            const maximums = employee.setting_maximums || {};
            const stateText = employee.has_custom_settings ? 'Custom settings saved' : 'Using default settings';
            return `
                <section class="card" data-employee-card="${employee.id}">
                    <div class="employee-stack" style="margin-bottom:16px;">
                        <span class="employee-avatar" style="margin-right:0;">${initials(employee.full_name)}</span>
                        <div>
                            <h4>${employee.full_name}</h4>
                            <p>${employee.email || ''}<br>${stateText}</p>
                        </div>
                    </div>
                    ${settingMeta.map(meta => sliderRow(employee, meta, Number(settings[meta.key] ?? meta.defaultValue ?? 0), Number(maximums[meta.key] ?? 100))).join('')}
                </section>`;
        }).join('');

        listEl.querySelectorAll('input[type="range"]').forEach(range => {
            range.addEventListener('input', function(){
                const target = document.querySelector('[data-value-for="' + this.dataset.employeeId + '-' + this.dataset.key + '"]');
                if (target) target.textContent = this.value;
                const labelP = this.closest('.slider-row').querySelector('.page-copy p');
                const meta = settingMeta.find(item => item.key === this.dataset.key);
                if (labelP && meta) labelP.textContent = this.value + ' per day · ' + meta.maxLabel;
            });
            range.addEventListener('change', saveEmployeeSettings);
        });
    }

    async function saveEmployeeSettings(event){
        const employeeId = Number(event.target.dataset.employeeId || 0);
        const card = document.querySelector('[data-employee-card="' + employeeId + '"]');
        if (!employeeId || !card) return;
        const payload = { employee_id: employeeId };
        card.querySelectorAll('input[type="range"]').forEach(range => payload[range.dataset.key] = Number(range.value));
        card.classList.add('is-loading');
        try {
            await api.request('/campaigns/employee_settings', { method:'PATCH', body: JSON.stringify(payload) });
            const sub = card.querySelector('.employee-stack p');
            if (sub) sub.innerHTML = `${(sub.innerHTML.split('<br>')[0] || '')}<br>Custom settings saved`;
            showToast('Settings saved');
        } catch (error) {
            alert(error.message || 'Could not save settings');
        } finally {
            card.classList.remove('is-loading');
        }
    }

    async function loadEmployeeSettings(){
        listEl.innerHTML = '<div class="empty-state">Loading employee settings…</div>';
        try {
            const employees = await api.request('/campaigns/employee_settings');
            renderEmployees(Array.isArray(employees) ? employees : []);
        } catch (error) {
            statusEl.textContent = 'Could not load';
            listEl.innerHTML = '<div class="empty-state">' + (error.message || 'Could not load employee settings') + '</div>';
        }
    }

    loadEmployeeSettings();
})();
</script>
<?php include('../partial/footer-end.php'); ?>
