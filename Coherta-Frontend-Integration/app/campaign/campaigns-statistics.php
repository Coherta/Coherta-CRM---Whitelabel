<?php
$PageTitle = 'Campaign Statistics';
require_once 'campaigns-shared.php';
$campaignId = isset($_GET['campaign_id']) ? (int)$_GET['campaign_id'] : 0;
$hasCampaignContext = $campaignId > 0;
?>
<style>
    .stats-shell{display:flex;flex-direction:column;gap:22px;}
    .campaign-overview-visual{background:#fff;border:1px solid rgba(148,163,184,.18);border-radius:18px;overflow:hidden;box-shadow:0 12px 30px rgba(15,23,42,.04);color:#0f172a;}
    .campaign-kpi-scroll{overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none;}
    .campaign-kpi-scroll::-webkit-scrollbar{display:none;}
    .campaign-kpi-row{min-width:1180px;display:grid;grid-template-columns:repeat(6,minmax(175px,1fr));gap:0;padding:18px;}
    .campaign-kpi-card{display:flex;align-items:center;gap:13px;min-width:0;padding:0 18px;border-right:1px solid rgba(148,163,184,.16);}
    .campaign-kpi-card:first-child{padding-left:0;}
    .campaign-kpi-card:last-child{border-right:0;}
    .campaign-kpi-card.is-missing{background:#fff7ed;box-shadow:inset 0 0 0 1px rgba(255,106,26,.20);border-radius:14px;padding-top:10px;padding-bottom:10px;}
    .campaign-kpi-icon{width:38px;height:38px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,106,26,.12);color:#ff6a1a;font-weight:900;font-size:16px;flex:0 0 auto;}
    .campaign-kpi-icon.is-teal{background:rgba(255,106,26,.12);color:#ff6a1a;}
    .campaign-kpi-label{display:flex;align-items:center;gap:4px;color:var(--muted);font-size:12px;font-weight:800;line-height:1.2;}
    .campaign-kpi-value{margin-top:4px;color:#0f172a;font-size:26px;line-height:1;font-weight:900;letter-spacing:-.02em;}
    .campaign-kpi-value.is-missing{font-size:14px;line-height:1.2;color:#9a3412;letter-spacing:0;}
    .campaign-kpi-note{margin-top:4px;font-size:11px;font-weight:700;color:#9a3412;line-height:1.25;}
    .campaign-kpi-note.is-info{color:var(--muted);}
    .campaign-data-warning{display:none;margin:0 18px 16px;padding:10px 12px;border-radius:12px;background:#fff7ed;border:1px solid rgba(255,106,26,.22);color:#9a3412;font-size:12px;font-weight:800;line-height:1.45;}
    .campaign-data-warning.is-visible{display:block;}
    .campaign-timeline-shell{padding:0 18px 18px;}
    .campaign-days-scroll{overflow-x:auto;padding-bottom:0;scrollbar-width:none;-ms-overflow-style:none;}
    .campaign-days-scroll::-webkit-scrollbar{display:none;}
    .campaign-days-row{display:flex;gap:10px;min-width:max-content;padding-right:2px;}
    .campaign-day-card{width:150px;border-radius:14px;background:#f8fafc;border:1px solid rgba(148,163,184,.16);padding:12px;flex:0 0 auto;}
    .campaign-day-card.is-latest{background:#fff7ed;border-color:rgba(255,106,26,.28);box-shadow:0 12px 26px rgba(255,106,26,.08);}
    .campaign-day-date{display:flex;justify-content:space-between;gap:8px;align-items:center;font-size:12px;font-weight:900;color:#0f172a;}
    .campaign-day-date span{color:var(--muted);font-weight:800;}
    .campaign-day-main{font-size:24px;font-weight:900;margin-top:8px;letter-spacing:-.02em;color:#0f172a;}
    .campaign-day-sub{font-size:11px;color:var(--muted);font-weight:700;margin-top:2px;}
    .campaign-day-bars{display:flex;gap:4px;align-items:flex-end;height:54px;margin-top:10px;}
    .campaign-day-bar{width:14px;min-height:0;border-radius:7px 7px 4px 4px;background:currentColor;color:#ff6a1a;opacity:.92;}
    .campaign-day-bar.is-accepted{opacity:.72;}
    .campaign-day-bar.is-messages{opacity:.56;}
    .campaign-day-bar.is-replied{opacity:.40;}
    .chart-scroll-x{overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none;}
    .chart-scroll-x::-webkit-scrollbar{display:none;}
    #graphSection{width:100%;}
    .daily-activity-category-card{width:100%;}
    .chart-scroll-x.is-focused-right{scroll-behavior:smooth;}
    .stats-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;}
    .stats-toolbar-left,.stats-toolbar-right{display:flex;gap:12px;align-items:center;flex-wrap:wrap;}
    .segmented{display:inline-flex;gap:6px;padding:6px;border:1px solid rgba(148,163,184,.18);border-radius:999px;background:#fff;flex-wrap:wrap;}
    .segmented button{border:0;background:transparent;padding:9px 14px;border-radius:999px;font-size:13px;font-weight:700;color:var(--muted);cursor:pointer;transition:.18s ease;}
    .segmented button.is-active{background:#111827;color:#fff;box-shadow:0 10px 24px rgba(15,23,42,.16);}
    .toolbar-select,.toolbar-input{min-height:42px;padding:0 14px;border:1px solid rgba(148,163,184,.22);border-radius:12px;background:#fff;color:#0f172a;font-size:14px;}
    .date-range-group{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .date-range-label{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;}
    .date-range-sep{font-size:13px;color:var(--muted);font-weight:700;}
    .summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;}
    .summary-card{background:#fff;border:1px solid rgba(148,163,184,.18);border-radius:18px;padding:16px;box-shadow:0 12px 30px rgba(15,23,42,.04);}
    .summary-label{font-size:12px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em;}
    .summary-value{margin-top:8px;font-size:28px;font-weight:800;color:#0f172a;line-height:1.1;}
    .summary-copy{margin-top:6px;font-size:13px;color:var(--muted);line-height:1.45;}
    .stats-layout{display:grid;grid-template-columns:minmax(300px,360px) minmax(0,1fr);gap:18px;align-items:start;}
    .employee-list{display:flex;flex-direction:column;gap:10px;}
    .employee-stat-card{padding:14px;border:1px solid rgba(148,163,184,.18);border-radius:16px;background:#fff;cursor:pointer;transition:.18s ease;display:flex;gap:12px;align-items:flex-start;}
    .employee-stat-card:hover{transform:translateY(-1px);box-shadow:0 12px 26px rgba(15,23,42,.06);}
    .employee-stat-card.is-active{border-color:rgba(37,99,235,.34);box-shadow:0 16px 36px rgba(37,99,235,.12);}
    .employee-avatar{width:42px;height:42px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#eff6ff;color:#1d4ed8;font-weight:800;font-size:14px;flex:0 0 auto;}
    .employee-meta{min-width:0;}
    .employee-meta strong{display:block;color:#0f172a;font-size:14px;line-height:1.35;}
    .employee-meta span{display:block;color:var(--muted);font-size:12px;line-height:1.4;margin-top:2px;}
    .employee-mini-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:10px;}
    .employee-mini-stat{padding:8px 10px;border-radius:12px;background:#f8fafc;border:1px solid rgba(148,163,184,.16);}
    .employee-mini-stat b{display:block;font-size:14px;color:#0f172a;}
    .employee-mini-stat span{display:block;margin-top:2px;font-size:11px;color:var(--muted);}
    .detail-card{position:relative;background:#fff;border:1px solid rgba(148,163,184,.18);border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.04);overflow:hidden;}
    .detail-overlay{position:absolute;inset:0;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(248,250,252,.86);backdrop-filter:blur(2px);z-index:5;}
    .detail-overlay.is-visible{display:flex;}
    .detail-overlay-message{max-width:420px;padding:20px 24px;border-radius:18px;background:#fff;border:1px solid rgba(148,163,184,.18);box-shadow:0 18px 40px rgba(15,23,42,.08);font-size:18px;font-weight:800;line-height:1.4;color:#0f172a;text-align:center;}
    .detail-head{padding:18px 18px 14px;border-bottom:1px solid rgba(148,163,184,.14);display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;}
    .detail-head h3{margin:0;font-size:20px;color:#0f172a;}
    .detail-head p{margin:6px 0 0;color:var(--muted);font-size:13px;line-height:1.5;max-width:760px;}
    .detail-actions{display:flex;gap:10px;flex-wrap:wrap;}
    .detail-body{padding:18px;display:flex;flex-direction:column;gap:18px;}
    .metric-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;}
    .metric-card{padding:14px;border-radius:16px;background:#f8fafc;border:1px solid rgba(148,163,184,.16);}
    .metric-card .label{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;}
    .metric-card .value{font-size:26px;font-weight:800;color:#0f172a;margin-top:8px;}
    .metric-card .sub{font-size:12px;color:var(--muted);margin-top:6px;}
    .chart-card{padding:16px;border-radius:16px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);}
    .chart-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
    .chart-title{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px;}
    .chart-title strong{font-size:14px;color:#0f172a;}
    .chart-title span{font-size:12px;color:var(--muted);}
    .bar-chart{display:flex;align-items:flex-end;gap:10px;min-height:220px;padding-top:14px;}
    .bar-col{flex:1;min-width:0;display:flex;flex-direction:column;align-items:center;gap:8px;}
    .bar-wrap{height:190px;width:100%;display:flex;align-items:flex-end;justify-content:center;}
    .bar{width:min(36px,90%);border-radius:14px 14px 8px 8px;background:linear-gradient(180deg,#3b82f6 0%,#1d4ed8 100%);box-shadow:0 14px 30px rgba(37,99,235,.18);min-height:8px;position:relative;}
    .multi-bar-chart{display:flex;align-items:flex-end;gap:12px;min-height:240px;padding-top:14px;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none;}
    .multi-bar-chart::-webkit-scrollbar{display:none;}
    .multi-bar-group{flex:1;min-width:92px;display:flex;flex-direction:column;align-items:center;gap:8px;}
    .multi-bar-wrap{height:210px;width:100%;display:flex;align-items:flex-end;justify-content:center;gap:6px;}
    .multi-bar{flex:1;max-width:12px;border-radius:10px 10px 6px 6px;min-height:6px;box-shadow:0 10px 24px rgba(15,23,42,.12);}
    .multi-bar.is-connection_requests{background:linear-gradient(180deg,#3b82f6 0%,#1d4ed8 100%);}
    .multi-bar.is-messages{background:linear-gradient(180deg,#10b981 0%,#047857 100%);}
    .multi-bar.is-profile_views{background:linear-gradient(180deg,#8b5cf6 0%,#6d28d9 100%);}
    .multi-bar.is-like_posts{background:linear-gradient(180deg,#f59e0b 0%,#d97706 100%);}
    .multi-bar.is-endorse_skills{background:linear-gradient(180deg,#ef4444 0%,#dc2626 100%);}
    .multi-bar.is-find_contact_details{background:linear-gradient(180deg,#14b8a6 0%,#0f766e 100%);}
    .chart-legend{display:flex;flex-wrap:wrap;gap:10px;margin:10px 0 14px;}
    .legend-item{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:#fff;border:1px solid rgba(148,163,184,.16);font-size:12px;font-weight:700;color:#334155;}
    .legend-swatch{width:10px;height:10px;border-radius:999px;display:inline-block;}
    .legend-swatch.is-connection_requests{background:#2563eb;}
    .legend-swatch.is-messages{background:#059669;}
    .legend-swatch.is-profile_views{background:#7c3aed;}
    .legend-swatch.is-like_posts{background:#d97706;}
    .legend-swatch.is-endorse_skills{background:#dc2626;}
    .legend-swatch.is-find_contact_details{background:#0f766e;}
    .bar-label{font-size:11px;color:var(--muted);text-align:center;line-height:1.2;}
    .bar-value{font-size:11px;font-weight:700;color:#0f172a;}
    .heatmap{display:grid;gap:8px;}
    .heatmap-head{font-size:12px;font-weight:700;color:var(--muted);padding:0 0 6px;}
    .heatmap-row-label{display:flex;align-items:center;font-size:12px;font-weight:700;color:#0f172a;padding:12px 10px;border-radius:12px;background:#f8fafc;border:1px solid rgba(148,163,184,.16);}
    .heat-cell{position:relative;padding:12px 10px;border-radius:12px;border:1px solid rgba(148,163,184,.14);background:#fff;overflow:hidden;min-height:58px;}
    .heat-cell::before{content:'';position:absolute;inset:auto 0 0 0;height:var(--fill, 0%);background:linear-gradient(180deg,rgba(59,130,246,.18) 0%,rgba(29,78,216,.30) 100%);}
    .heat-cell strong,.heat-cell span{position:relative;z-index:1;display:block;}
    .heat-cell strong{font-size:14px;color:#0f172a;}
    .heat-cell span{font-size:11px;color:#475467;line-height:1.3;margin-top:3px;}
    .table-shell.compact{overflow:auto;border:1px solid rgba(148,163,184,.16);border-radius:16px;}
    .stats-table{width:100%;border-collapse:separate;border-spacing:0;background:#fff;min-width:980px;}
    .stats-table th,.stats-table td{padding:12px 14px;border-bottom:1px solid rgba(148,163,184,.12);text-align:left;font-size:13px;vertical-align:top;}
    .stats-table thead th{position:sticky;top:0;background:#f8fafc;color:#475467;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;z-index:1;}
    .stats-table tbody tr:hover td{background:#fafcff;}
    .progress-row{display:flex;align-items:center;gap:10px;}
    .progress-track{height:8px;border-radius:999px;background:#e5e7eb;flex:1;overflow:hidden;}
    .progress-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#3b82f6 0%,#1d4ed8 100%);width:0;}
    .chip{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:700;}
    .copy-feedback{font-size:12px;color:#16a34a;font-weight:700;display:none;}
    .copy-feedback.show{display:inline-block;}
    @media (max-width: 1280px){
        .summary-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
        .metric-grid,.chart-grid{grid-template-columns:1fr;}
    }
    @media (max-width: 1024px){
        .stats-layout{grid-template-columns:1fr;}
    }
    @media (max-width: 760px){
        .summary-grid{grid-template-columns:1fr;}
        .segmented{width:100%;}
        .stats-toolbar-left,.stats-toolbar-right{width:100%;}
        .toolbar-select,.toolbar-input{width:100%;}
        .date-range-group{width:100%;}
        .heatmap{grid-template-columns:minmax(120px,140px) repeat(7,minmax(56px,1fr));}
    }
</style>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include('../partial/sidebar.php'); ?>
        <div class="page-body no-top-margin">
            <div class="app-shell">
                <main class="content">
                    <?php campaign_topbar('statistics'); ?>

                    <section class="panel hero">
                        <div class="panel-inner">
                            <div class="hero-grid">
                                <div>
                                    <h2>Employee statistics</h2>
                                    <p>Track daily activity per employee across outreach categories, switch between all employees and one employee, and copy tables or graph data directly into Excel or PowerPoint.</p>
                                </div>
                                <div class="hero-stats">
                                    <div class="hero-stat"><strong id="heroEmployeeCount">0</strong><span>Employees</span></div>
                                    <div class="hero-stat"><strong id="heroDaysCount">7</strong><span>Days shown</span></div>
                                    <div class="hero-stat"><strong id="heroTotalActions">0</strong><span>Total actions</span></div>
                                    <div class="hero-stat"><strong id="heroAvgDaily">0</strong><span>Average daily</span></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <?php if ($hasCampaignContext): ?>
                        <section class="campaign-overview-visual" id="campaignOverviewVisual" data-campaign-id="<?php echo htmlspecialchars((string)$campaignId, ENT_QUOTES, 'UTF-8'); ?>" style="margin-top:22px;">
                            <div class="campaign-kpi-scroll" id="campaignKpiScroll">
                                <div class="campaign-kpi-row" id="campaignKpiRow"></div>
                            </div>
                            <div class="campaign-data-warning" id="campaignMissingFields"></div>
                            <div class="campaign-timeline-shell">
                                <div class="campaign-days-scroll is-focused-right" id="campaignDaysScroll">
                                    <div class="campaign-days-row" id="campaignDaysRow"></div>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                    <div class="stats-shell" style="margin-top:22px;">
                        <section class="panel">
                            <div class="panel-inner">
                                <div class="stats-toolbar">
                                    <div class="stats-toolbar-left">
                                        <div class="segmented" id="viewModeSegment">
                                            <button type="button" class="is-active" data-view-mode="all">All employees</button>
                                            <button type="button" data-view-mode="single">Single employee</button>
                                        </div>
                                        <div class="segmented" id="displayModeSegment">
                                            <button type="button" class="is-active" data-display-mode="graph">Graph</button>
                                            <button type="button" data-display-mode="table">Table</button>
                                        </div>
                                    </div>
                                    <div class="stats-toolbar-right">
                                        <div class="date-range-group" aria-label="Select period">
                                            <span class="date-range-label">Period</span>
                                            <input class="toolbar-input" type="date" id="startDateInput" aria-label="Start date">
                                            <span class="date-range-sep">to</span>
                                            <input class="toolbar-input" type="date" id="endDateInput" aria-label="End date">
                                        </div>
                                        <span class="count-pill" id="statisticsStatusPill">Loading…</span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="panel">
                            <div class="panel-inner">
                                <div class="section-head">
                                    <div>
                                        <h3>Aggregated overview</h3>
                                        <p>Combined activity across all loaded employees for the selected time window.</p>
                                    </div>
                                    <div class="chip" id="aggregatePeriodChip">Last 7 days</div>
                                </div>
                                <div class="summary-grid" id="aggregateSummaryGrid"></div>
                            </div>
                        </section>

                        <div class="stats-layout">
                            <section class="panel">
                                <div class="panel-inner">
                                    <div class="section-head">
                                        <div>
                                            <h3>Employees</h3>
                                        </div>
                                        <div class="count-pill" id="employeeListPill">0 loaded</div>
                                    </div>
                                    <div class="employee-list" id="employeeList">
                                        <div class="empty-state">Loading employee statistics…</div>
                                    </div>
                                </div>
                            </section>

                            <section class="detail-card" id="statisticsDetailCard">
                                <div class="detail-overlay" id="statisticsDetailOverlay">
                                    <div class="detail-overlay-message">No Statistics is available on this employee</div>
                                </div>
                                <div class="detail-head">
                                    <div>
                                        <h3 id="detailTitle">Employee overview</h3>
                                        <p id="detailCopy">Daily activity and target coverage will appear here when the statistics are loaded.</p>
                                    </div>
                                    <div class="detail-actions">
                                        <button class="btn btn-outline" type="button" id="copyGraphBtn">Copy graph data</button>
                                        <button class="btn btn-outline" type="button" id="copyTableBtn">Copy table</button>
                                        <span class="copy-feedback" id="copyFeedback">Copied</span>
                                    </div>
                                </div>
                                <div class="detail-body">
                                    <div class="metric-grid" id="detailMetricGrid"></div>
                                    <div id="graphSection"></div>
                                    <div id="tableSection" style="display:none;"></div>
                                </div>
                            </section>
                        </div>
                    </div>

                </main>
            </div>
        </div>
        <?php include('../partial/footer.php'); ?>
    </div>
</div>
<?php include('../partial/scripts.php'); ?>
<script>
    (function(){
        const api = window.CohertaCampaignsApi;
        const employeeListEl = document.getElementById('employeeList');
        const employeeListPill = document.getElementById('employeeListPill');
        const statusPill = document.getElementById('statisticsStatusPill');
        const detailTitle = document.getElementById('detailTitle');
        const detailCopy = document.getElementById('detailCopy');
        const detailMetricGrid = document.getElementById('detailMetricGrid');
        const graphSection = document.getElementById('graphSection');
        const tableSection = document.getElementById('tableSection');
        const statisticsDetailOverlay = document.getElementById('statisticsDetailOverlay');
        const copyGraphBtn = document.getElementById('copyGraphBtn');
        const copyTableBtn = document.getElementById('copyTableBtn');
        const copyFeedback = document.getElementById('copyFeedback');
        const aggregateSummaryGrid = document.getElementById('aggregateSummaryGrid');
        const aggregatePeriodChip = document.getElementById('aggregatePeriodChip');
        const startDateInput = document.getElementById('startDateInput');
        const endDateInput = document.getElementById('endDateInput');
        const campaignContext = {
            enabled: <?php echo $hasCampaignContext ? 'true' : 'false'; ?>,
            id: <?php echo (int)$campaignId; ?>
        };

        const categoryMeta = [
            { key:'connection_requests', label:'Connection requests' },
            { key:'messages', label:'Messages' },
            { key:'profile_views', label:'Profile views' },
            { key:'like_posts', label:'Like posts' },
            { key:'endorse_skills', label:'Endorse skills' },
            { key:'find_contact_details', label:'Find contact details' }
        ];

        const state = {
            viewMode: 'all',
            displayMode: 'graph',
            employees: [],
            selectedEmployeeId: null,
            aggregate: null,
            startDate: '',
            endDate: '',
            campaign_id: campaignContext.enabled ? campaignContext.id : null,
            campaignFullPeriod: null,
            campaignReplyTotals: null
        };

        function setEmployeeUrl(employeeId){
            const url = new URL(window.location.href);
            if (employeeId) {
                url.searchParams.set('employee_id', String(employeeId));
            } else {
                url.searchParams.delete('employee_id');
            }
            window.history.replaceState({}, '', url);
        }

        function getUrlEmployeeId(){
            const employeeId = new URLSearchParams(window.location.search).get('employee_id');
            const numericEmployeeId = employeeId ? Number(employeeId) : null;
            return Number.isFinite(numericEmployeeId) && numericEmployeeId > 0 ? numericEmployeeId : null;
        }

        function syncViewModeButtons(){
            document.querySelectorAll('#viewModeSegment [data-view-mode]').forEach(node => {
                node.classList.toggle('is-active', node.dataset.viewMode === state.viewMode);
            });
        }

        function selectEmployee(employeeId, updateUrl = true){
            const matchingEmployee = state.employees.find(employee => Number(employee.id) === Number(employeeId));
            if (!matchingEmployee) return false;
            state.selectedEmployeeId = Number(matchingEmployee.id);
            state.viewMode = 'single';
            syncViewModeButtons();
            if (updateUrl) setEmployeeUrl(state.selectedEmployeeId);
            renderEmployeeList();
            renderDetail();
            return true;
        }

        function esc(value){
            return String(value ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#39;');
        }

        function initials(name){
            return String(name || '').split(/\s+/).filter(Boolean).slice(0,2).map(part => part.charAt(0).toUpperCase()).join('');
        }

        function fmt(num){
            return Number(num || 0).toLocaleString('en-US');
        }

        function fmtPercentValue(num){
            const value = Number(num || 0);
            if (!Number.isFinite(value)) return '0%';
            const options = Number.isInteger(value) ? {} : { maximumFractionDigits: 1 };
            return value.toLocaleString('en-US', options) + '%';
        }

        function pct(num){
            return Math.round(Number(num || 0)) + '%';
        }

        function dayLabel(dateString){
            const date = new Date(dateString + 'T12:00:00');
            return date.toLocaleDateString('en-GB', { weekday:'short', day:'2-digit', month:'2-digit' });
        }

        function showCopied(){
            copyFeedback.classList.remove('show');
            void copyFeedback.offsetWidth;
            copyFeedback.classList.add('show');
            setTimeout(() => copyFeedback.classList.remove('show'), 1600);
        }

        async function copyText(text){
            try {
                await navigator.clipboard.writeText(text);
                showCopied();
            } catch (error) {
                alert('Could not copy to clipboard');
            }
        }

        function formatIsoDate(date){
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function getDefaultDateRange(){
            const end = new Date();
            const start = new Date();
            start.setDate(end.getDate() - 6);
            return {
                startDate: formatIsoDate(start),
                endDate: formatIsoDate(end)
            };
        }

        function getDateRange(startDate, endDate){
            const start = new Date(startDate + 'T12:00:00');
            const end = new Date(endDate + 'T12:00:00');
            const list = [];
            if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || start > end) return list;
            const cursor = new Date(start);
            while (cursor <= end) {
                list.push(formatIsoDate(cursor));
                cursor.setDate(cursor.getDate() + 1);
            }
            return list;
        }

        function formatPeriodLabel(startDate, endDate){
            if (!startDate || !endDate) return 'Last 7 days';
            const start = new Date(startDate + 'T12:00:00');
            const end = new Date(endDate + 'T12:00:00');
            if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return 'Custom period';
            const sameYear = start.getFullYear() === end.getFullYear();
            const startLabel = start.toLocaleDateString('en-GB', sameYear ? { day:'2-digit', month:'short' } : { day:'2-digit', month:'short', year:'numeric' });
            const endLabel = end.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
            return `${startLabel} – ${endLabel}`;
        }

        function focusLatestColumns(scope){
            const root = scope || document;
            requestAnimationFrame(() => {
                root.querySelectorAll('.is-focused-right').forEach(node => {
                    node.scrollLeft = node.scrollWidth;
                });
            });
        }

        function firstDefinedNumber(){
            for (let index = 0; index < arguments.length; index++) {
                const raw = arguments[index];
                if (raw === undefined || raw === null || raw === '') continue;
                const num = Number(raw);
                if (Number.isFinite(num)) return num;
            }
            return null;
        }

        function hasOwn(obj, key){
            return !!obj && Object.prototype.hasOwnProperty.call(obj, key);
        }

        function hasAnyOwn(obj, keys){
            return keys.some(key => hasOwn(obj, key));
        }

        function periodHasAnyDayField(period, categoryKeys, rawKeys){
            const periodEmployees = Array.isArray(period?.statistics) ? period.statistics : [];
            return periodEmployees.some(employee => {
                const days = Array.isArray(employee?.days) ? employee.days : [];
                return days.some(day => {
                    const categories = day?.categories || {};
                    if (hasAnyOwn(categories, categoryKeys)) return true;
                    return rawKeys.some(key => hasOwn(day, key) || hasOwn(day, key + '_actual'));
                });
            });
        }

        function periodHasMetric(period, categoryKeys, rawKeys){
            const totals = period?.totals || {};
            const categories = totals.categories || {};
            const contactTotals = period?.campaign_contact_totals || {};
            const replyTotals = period?.campaign_reply_totals || {};
            return hasAnyOwn(categories, categoryKeys)
                || hasAnyOwn(totals, rawKeys.concat(categoryKeys))
                || hasAnyOwn(contactTotals, rawKeys.concat(categoryKeys))
                || hasAnyOwn(replyTotals, rawKeys.concat(categoryKeys))
                || hasAnyOwn(period || {}, rawKeys.concat(categoryKeys))
                || periodHasAnyDayField(period, categoryKeys, rawKeys.concat(categoryKeys));
        }

        function collectCampaignDailyFieldPresence(period){
            return {
                profileViews: periodHasAnyDayField(period, ['profile_views'], ['profile_views']),
                requestedConnections: periodHasAnyDayField(period, ['connection_requests'], ['connection_requests']),
                approvedConnections: periodHasAnyDayField(period, ['approved_connections','accepted_connections','accepted'], ['approved_connections','accepted_connections','accepted','total_contacts_approved_connection_request']),
                messages: periodHasAnyDayField(period, ['messages'], ['messages'])
            };
        }

        function readDayMetric(day, categoryKeys, rawKeys){
            const values = [];
            categoryKeys.forEach(key => values.push(day?.categories?.[key]?.actual));
            rawKeys.forEach(key => values.push(day?.[key]?.actual, day?.[key + '_actual'], day?.[key]));
            return firstDefinedNumber(...values);
        }

        function collectCampaignFieldPresence(period){
            return {
                totalOpportunities: periodHasMetric(period, [], ['total_attached_contacts','attached_contacts','total_opportunities','total_prospects']),
                profileViews: periodHasMetric(period, ['profile_views'], ['profile_views']),
                requestedConnections: periodHasMetric(period, ['connection_requests'], ['connection_requests']),
                approvedConnections: periodHasMetric(period, ['approved_connections','accepted_connections','accepted'], ['total_contacts_approved_connection_request','approved_connections','accepted_connections','accepted']),
                messages: periodHasMetric(period, ['messages'], ['messages']),
                replyPercentage: periodHasMetric(period, [], ['automated_message_threads','replied_message_threads','reply_percentage'])
            };
        }

        function normalizedCampaignBarHeight(value, maxValue){
            const numericValue = Number(value || 0);
            if (numericValue <= 0 || maxValue <= 0) return 0;
            return Math.round((numericValue / maxValue) * 100);
        }

        function extractStatisticsPayload(payload){
            if (Array.isArray(payload)) {
                return {
                    statistics: payload,
                    campaignFullPeriod: null,
                    campaignReplyTotals: null,
                    hasLiveStatistics: payload.some(item => Array.isArray(item?.days))
                };
            }
            if (payload && typeof payload === 'object') {
                const rawCampaignFullPeriod = payload.campaign_full_period || null;
                const statistics = Array.isArray(payload.statistics)
                    ? payload.statistics
                    : (Array.isArray(rawCampaignFullPeriod?.statistics) ? rawCampaignFullPeriod.statistics : []);
                let campaignFullPeriod = rawCampaignFullPeriod
                    ? { ...rawCampaignFullPeriod }
                    : ((payload.campaign_contact_totals || payload.campaign_reply_totals) ? { statistics, totals: {} } : null);

                if (campaignFullPeriod) {
                    const campaignContactTotals = payload.campaign_contact_totals || campaignFullPeriod.campaign_contact_totals || null;
                    const campaignReplyTotals = payload.campaign_reply_totals || campaignFullPeriod.campaign_reply_totals || null;
                    campaignFullPeriod.statistics = Array.isArray(campaignFullPeriod.statistics) ? campaignFullPeriod.statistics : statistics;
                    campaignFullPeriod.totals = {
                        ...(campaignContactTotals || {}),
                        ...(campaignReplyTotals || {}),
                        ...(campaignFullPeriod.totals || {})
                    };
                    if (campaignContactTotals) campaignFullPeriod.campaign_contact_totals = campaignContactTotals;
                    if (campaignReplyTotals) campaignFullPeriod.campaign_reply_totals = campaignReplyTotals;
                }

                return {
                    statistics,
                    campaignFullPeriod,
                    campaignReplyTotals: payload.campaign_reply_totals || campaignFullPeriod?.campaign_reply_totals || campaignFullPeriod?.totals || null,
                    hasLiveStatistics: true
                };
            }
            return {
                statistics: [],
                campaignFullPeriod: null,
                campaignReplyTotals: null,
                hasLiveStatistics: false
            };
        }

        function sumCampaignFullPeriodDays(period, fieldPresence){
            const presence = fieldPresence || collectCampaignFieldPresence(period);
            const daysMap = new Map();
            const periodEmployees = Array.isArray(period?.statistics) ? period.statistics : [];
            periodEmployees.forEach(employee => {
                const days = Array.isArray(employee?.days) ? employee.days : [];
                days.forEach(day => {
                    if (!day || !day.date) return;
                    if (!daysMap.has(day.date)) {
                        daysMap.set(day.date, {
                            iso: day.date,
                            categories: Object.fromEntries(categoryMeta.map(meta => [meta.key, 0])),
                            requestedConnections: presence.requestedConnections ? 0 : null,
                            approvedConnections: presence.approvedConnections ? 0 : null,
                            profileViews: presence.profileViews ? 0 : null,
                            messages: presence.messages ? 0 : null,
                            total: 0
                        });
                    }
                    const row = daysMap.get(day.date);
                    let computedTotal = 0;
                    categoryMeta.forEach(meta => {
                        const actual = Number(day.categories?.[meta.key]?.actual ?? day[meta.key]?.actual ?? day[meta.key + '_actual'] ?? 0);
                        row.categories[meta.key] += actual;
                        computedTotal += actual;
                    });
                    if (presence.requestedConnections) {
                        const value = readDayMetric(day, ['connection_requests'], ['connection_requests']);
                        row.requestedConnections += Number(value || 0);
                    }
                    if (presence.approvedConnections) {
                        const value = readDayMetric(day, ['approved_connections','accepted_connections','accepted'], ['approved_connections','accepted_connections','accepted']);
                        row.approvedConnections += Number(value || 0);
                    }
                    if (presence.profileViews) {
                        const value = readDayMetric(day, ['profile_views'], ['profile_views']);
                        row.profileViews += Number(value || 0);
                    }
                    if (presence.messages) {
                        const value = readDayMetric(day, ['messages'], ['messages']);
                        row.messages += Number(value || 0);
                    }
                    row.total += Number(day.total_actual ?? computedTotal);
                });
            });

            const dates = period?.date_from && period?.date_to
                ? getDateRange(period.date_from, period.date_to)
                : Array.from(daysMap.keys()).sort();

            return dates.map(date => {
                const row = daysMap.get(date) || {
                    iso: date,
                    categories: Object.fromEntries(categoryMeta.map(meta => [meta.key, 0])),
                    requestedConnections: presence.requestedConnections ? 0 : null,
                    approvedConnections: presence.approvedConnections ? 0 : null,
                    profileViews: presence.profileViews ? 0 : null,
                    messages: presence.messages ? 0 : null,
                    total: 0
                };
                return {
                    iso: date,
                    requestedConnections: row.requestedConnections,
                    approvedConnections: row.approvedConnections,
                    profileViews: row.profileViews,
                    messages: row.messages,
                    total: Number(row.total || 0)
                };
            });
        }

        function renderCampaignOverviewFromFullPeriod(period){
            const totals = period?.totals || {};
            const categories = totals.categories || {};
            const contactTotals = period?.campaign_contact_totals || {};
            const replyTotals = period?.campaign_reply_totals || {};
            const fieldPresence = collectCampaignFieldPresence(period);
            const dailyFieldPresence = collectCampaignDailyFieldPresence(period);
            const totalOpportunities = firstDefinedNumber(
                totals.total_attached_contacts,
                contactTotals.total_attached_contacts,
                totals.attached_contacts,
                totals.total_opportunities,
                totals.total_prospects,
                period?.total_attached_contacts,
                period?.attached_contacts,
                period?.total_opportunities,
                period?.total_prospects
            );
            const profileViews = firstDefinedNumber(
                categories.profile_views,
                totals.profile_views,
                period?.profile_views
            );
            const requestedConnections = firstDefinedNumber(
                categories.connection_requests,
                totals.connection_requests,
                period?.connection_requests
            );
            const approvedConnections = firstDefinedNumber(
                totals.total_contacts_approved_connection_request,
                contactTotals.total_contacts_approved_connection_request,
                categories.approved_connections,
                categories.accepted_connections,
                categories.accepted,
                totals.approved_connections,
                totals.accepted_connections,
                totals.accepted,
                period?.total_contacts_approved_connection_request,
                period?.approved_connections,
                period?.accepted_connections,
                period?.accepted
            );
            const messages = firstDefinedNumber(
                categories.messages,
                totals.messages,
                period?.messages
            );
            const automatedMessageThreads = firstDefinedNumber(
                totals.automated_message_threads,
                replyTotals.automated_message_threads,
                period?.automated_message_threads
            );
            const repliedMessageThreads = firstDefinedNumber(
                totals.replied_message_threads,
                replyTotals.replied_message_threads,
                period?.replied_message_threads
            );
            const calculatedReplyPercentage = automatedMessageThreads !== null
                ? (automatedMessageThreads > 0 ? Math.round((Number(repliedMessageThreads || 0) / automatedMessageThreads) * 1000) / 10 : 0)
                : null;
            const replyPercentage = firstDefinedNumber(
                totals.reply_percentage,
                replyTotals.reply_percentage,
                period?.reply_percentage,
                calculatedReplyPercentage
            );
            const replyNote = automatedMessageThreads !== null && repliedMessageThreads !== null
                ? `${fmt(repliedMessageThreads)} of ${fmt(automatedMessageThreads)} automated threads replied`
                : '';
            const kpis = [
                { label:'Total Opportunities (Attached Contacts)', value: totalOpportunities, missing: !fieldPresence.totalOpportunities || totalOpportunities === null, icon:'◎' },
                { label:'Profile views', value: profileViews, missing: !fieldPresence.profileViews || profileViews === null, icon:'◉' },
                { label:'Requested Connections', value: requestedConnections, missing: !fieldPresence.requestedConnections || requestedConnections === null, icon:'+' },
                { label:'Approved Connections', value: approvedConnections, missing: !fieldPresence.approvedConnections || approvedConnections === null, icon:'✓' },
                { label:'Messages', value: messages, missing: !fieldPresence.messages || messages === null, icon:'▶' },
                { label:'Reply percentage', value: replyPercentage, missing: !fieldPresence.replyPercentage || replyPercentage === null, icon:'↩', formatter:'percent', note: replyNote }
            ];
            const dailyMissingFields = [
                { key:'profileViews', label:'Daily Profile views' },
                { key:'requestedConnections', label:'Daily Requested Connections' },
                { key:'approvedConnections', label:'Daily Approved Connections' },
                { key:'messages', label:'Daily Messages' }
            ].filter(item => !dailyFieldPresence[item.key]).map(item => item.label);
            return {
                kpis,
                days: sumCampaignFullPeriodDays(period, dailyFieldPresence),
                missingFields: kpis.filter(item => item.missing).map(item => item.label),
                dailyMissingFields,
                fieldPresence,
                dailyFieldPresence
            };
        }

        function applyCampaignContextText(){
            if (!campaignContext.enabled) return;
            const heroTitle = document.querySelector('.hero h2');
            const heroCopy = document.querySelector('.hero p');
            if (heroTitle) heroTitle.textContent = 'Campaign statistics';
            if (heroCopy) heroCopy.textContent = `Track employee activity for campaign #${campaignContext.id}. The overview above uses the full campaign period, while the detailed statistics below follow the selected period.`;
            const allModeBtn = document.querySelector('#viewModeSegment [data-view-mode="all"]');
            if (allModeBtn) allModeBtn.textContent = 'Campaign employees';
            const sectionHeads = document.querySelectorAll('.stats-shell .section-head');
            if (sectionHeads[0]) {
                const title = sectionHeads[0].querySelector('h3');
                const copy = sectionHeads[0].querySelector('p');
                if (title) title.textContent = 'Campaign activity overview';
                if (copy) copy.textContent = 'Combined activity for users with activity in this campaign during the selected period.';
            }
            if (sectionHeads[1]) {
                const title = sectionHeads[1].querySelector('h3');
                const copy = sectionHeads[1].querySelector('p');
                if (title) title.textContent = 'Campaign employees';
                if (copy) copy.textContent = 'Only users with activity in this campaign and selected period are shown.';
            }
        }

        function renderCampaignOverview(periodPayload){
            if (!campaignContext.enabled) return;
            const kpiRow = document.getElementById('campaignKpiRow');
            const daysRow = document.getElementById('campaignDaysRow');
            const daysScroll = document.getElementById('campaignDaysScroll');
            const missingFieldsEl = document.getElementById('campaignMissingFields');
            if (!kpiRow || !daysRow || !daysScroll) return;

            let overview = null;
            if (periodPayload || state.campaignFullPeriod) {
                overview = renderCampaignOverviewFromFullPeriod(periodPayload || state.campaignFullPeriod);
            }

            if (!overview) {
                kpiRow.innerHTML = '<div class="empty-state" style="grid-column:1 / -1;margin:0;">Loading campaign overview…</div>';
                daysRow.innerHTML = '';
                if (missingFieldsEl) {
                    missingFieldsEl.classList.remove('is-visible');
                    missingFieldsEl.textContent = '';
                }
                return;
            }

            const kpis = overview.kpis || [];
            kpiRow.innerHTML = kpis.map(item => `
                <div class="campaign-kpi-card ${item.missing ? 'is-missing' : ''}">
                    <span class="campaign-kpi-icon ${item.teal ? 'is-teal' : ''}">${esc(item.icon)}</span>
                    <div>
                        <div class="campaign-kpi-label">${esc(item.label)} ⓘ</div>
                        <div class="campaign-kpi-value ${item.missing ? 'is-missing' : ''}">${item.missing ? 'Missing' : (item.formatter === 'percent' ? fmtPercentValue(item.value) : fmt(item.value))}</div>
                        ${item.missing ? '<div class="campaign-kpi-note">Not provided by endpoint</div>' : (item.note ? `<div class="campaign-kpi-note is-info">${esc(item.note)}</div>` : '')}
                    </div>
                </div>
            `).join('');

            const missingFields = Array.isArray(overview.missingFields) ? overview.missingFields : [];
            const dailyMissingFields = Array.isArray(overview.dailyMissingFields) ? overview.dailyMissingFields : [];
            if (missingFieldsEl) {
                const warnings = [];
                if (missingFields.length) {
                    warnings.push('Missing endpoint fields: ' + missingFields.map(field => esc(field)).join(', ') + '. These values are not estimated.');
                }
                if (warnings.length) {
                    missingFieldsEl.innerHTML = warnings.join('<br>');
                    missingFieldsEl.classList.add('is-visible');
                } else {
                    missingFieldsEl.classList.remove('is-visible');
                    missingFieldsEl.textContent = '';
                }
            }

            const campaignDays = overview.days || [];
            if (!campaignDays.length) {
                daysRow.innerHTML = '<div class="empty-state" style="min-width:320px;margin:0;">No daily campaign activity returned for the full campaign period.</div>';
                return;
            }

            const dailySeries = [
                { key:'profileViews', label:'Profile views', className:'', available: overview.dailyFieldPresence?.profileViews !== false },
                { key:'requestedConnections', label:'Requested Connections', className:'is-accepted', available: overview.dailyFieldPresence?.requestedConnections !== false },
                { key:'approvedConnections', label:'Approved Connections', className:'is-messages', available: overview.dailyFieldPresence?.approvedConnections !== false },
                { key:'messages', label:'Messages', className:'is-replied', available: overview.dailyFieldPresence?.messages !== false }
            ].filter(item => item.available);
            const maxValue = Math.max(1, ...campaignDays.flatMap(day => dailySeries.map(item => Number(day[item.key] || 0))));
            daysRow.innerHTML = campaignDays.map((day, index) => {
                const date = new Date(day.iso + 'T12:00:00');
                const label = date.toLocaleDateString('en-GB', { day:'2-digit', month:'short' });
                const weekday = date.toLocaleDateString('en-GB', { weekday:'short' });
                const total = Number(day.total ?? dailySeries.reduce((sum, item) => sum + Number(day[item.key] || 0), 0));
                const bars = dailySeries.map(item => `
                    <span class="campaign-day-bar ${esc(item.className)}" title="${esc(item.label)}: ${fmt(day[item.key])}" style="height:${normalizedCampaignBarHeight(day[item.key], maxValue)}%;"></span>
                `).join('');
                return `
                    <div class="campaign-day-card ${index === campaignDays.length - 1 ? 'is-latest' : ''}">
                        <div class="campaign-day-date"><strong>${esc(label)}</strong><span>${esc(weekday)}</span></div>
                        <div class="campaign-day-main">${fmt(total)}</div>
                        <div class="campaign-day-sub">Daily actions</div>
                        <div class="campaign-day-bars" aria-label="Campaign activity for ${esc(day.iso)}">
                            ${bars || '<span class="campaign-kpi-note">Daily fields missing</span>'}
                        </div>
                    </div>
                `;
            }).join('');
            focusLatestColumns(document.getElementById('campaignOverviewVisual'));
        }

        function defaultSettings(raw){
            return {
                connection_requests: Number(raw?.connection_requests ?? 45),
                messages: Number(raw?.messages ?? 70),
                profile_views: Number(raw?.profile_views ?? 60),
                like_posts: Number(raw?.like_posts ?? 25),
                endorse_skills: Number(raw?.endorse_skills ?? 15),
                find_contact_details: Number(raw?.find_contact_details ?? 5)
            };
        }

        function seededNumber(seed){
            let h = 2166136261;
            const str = String(seed);
            for (let i = 0; i < str.length; i++) {
                h ^= str.charCodeAt(i);
                h += (h << 1) + (h << 4) + (h << 7) + (h << 8) + (h << 24);
            }
            return Math.abs(h >>> 0) / 4294967295;
        }

        function createMockDays(employee, startDate, endDate){
            const settings = defaultSettings(employee.settings || {});
            return getDateRange(startDate, endDate).map((date, dayIndex) => {
                const categories = {};
                let totalActual = 0;
                let totalTarget = 0;
                categoryMeta.forEach((meta, index) => {
                    const target = Number(settings[meta.key] || 0);
                    const ratio = 0.55 + (seededNumber(employee.id + '-' + meta.key + '-' + date) * 0.65);
                    const weekdayLift = (dayIndex % 7 === 5 || dayIndex % 7 === 6) ? 0.72 : 1;
                    const actual = Math.max(0, Math.round(target * ratio * weekdayLift));
                    categories[meta.key] = { actual, target };
                    totalActual += actual;
                    totalTarget += target;
                });
                return {
                    date,
                    categories,
                    total_actual: totalActual,
                    total_target: totalTarget,
                    target_hit_pct: totalTarget ? Math.round((totalActual / totalTarget) * 100) : 0
                };
            });
        }

        function normalizeEmployee(raw, startDate, endDate){
            const settings = defaultSettings(raw.settings || raw.daily_limits || raw.standard_usage || {});
            const requestedDates = new Set(getDateRange(startDate, endDate));
            const hasDaysArray = Array.isArray(raw.days);
            const sourceDays = hasDaysArray
                ? raw.days.filter(day => day && day.date && (!requestedDates.size || requestedDates.has(day.date)))
                : [];
            const shouldUseMockDays = !hasDaysArray && !campaignContext.enabled;
            const days = sourceDays.length ? sourceDays.map(day => {
                const categories = {};
                let totalActual = 0;
                let totalTarget = 0;
                categoryMeta.forEach(meta => {
                    const row = day.categories?.[meta.key] || day[meta.key] || {};
                    const actual = Number(row.actual ?? row.used ?? row.value ?? day[meta.key + '_actual'] ?? 0);
                    const target = Number(row.target ?? settings[meta.key] ?? 0);
                    categories[meta.key] = { actual, target };
                    totalActual += actual;
                    totalTarget += target;
                });
                return {
                    date: day.date,
                    categories,
                    total_actual: Number(day.total_actual ?? totalActual),
                    total_target: Number(day.total_target ?? totalTarget),
                    target_hit_pct: Number(day.target_hit_pct ?? (totalTarget ? Math.round((totalActual / totalTarget) * 100) : 0))
                };
            }) : (shouldUseMockDays ? createMockDays({ id: raw.id, settings }, startDate, endDate) : []);
            days.sort((a,b) => String(a.date || '').localeCompare(String(b.date || '')));
            const totals = categoryMeta.reduce((acc, meta) => {
                acc[meta.key] = days.reduce((sum, day) => sum + Number(day.categories[meta.key]?.actual || 0), 0);
                return acc;
            }, {});
            const totalActual = days.reduce((sum, day) => sum + Number(day.total_actual || 0), 0);
            const totalTarget = days.reduce((sum, day) => sum + Number(day.total_target || 0), 0);
            return {
                id: Number(raw.id),
                full_name: raw.full_name || raw.name || 'Unknown user',
                email: raw.email || '',
                settings,
                days,
                totals,
                total_actual: totalActual,
                total_target: totalTarget,
                target_hit_pct: totalTarget ? Math.round((totalActual / totalTarget) * 100) : 0,
                best_day: days.slice().sort((a,b) => Number(b.total_actual || 0) - Number(a.total_actual || 0))[0] || null,
                average_daily: days.length ? Math.round(totalActual / days.length) : 0
            };
        }

        function buildAggregate(employees){
            const aggregateDaysMap = new Map();
            employees.forEach(employee => {
                employee.days.forEach(day => {
                    if (!aggregateDaysMap.has(day.date)) {
                        aggregateDaysMap.set(day.date, {
                            date: day.date,
                            categories: Object.fromEntries(categoryMeta.map(meta => [meta.key, { actual: 0, target: 0 }])),
                            total_actual: 0,
                            total_target: 0,
                            target_hit_pct: 0
                        });
                    }
                    const row = aggregateDaysMap.get(day.date);
                    categoryMeta.forEach(meta => {
                        row.categories[meta.key].actual += Number(day.categories[meta.key]?.actual || 0);
                        row.categories[meta.key].target += Number(day.categories[meta.key]?.target || 0);
                    });
                    row.total_actual += Number(day.total_actual || 0);
                    row.total_target += Number(day.total_target || 0);
                    row.target_hit_pct = row.total_target ? Math.round((row.total_actual / row.total_target) * 100) : 0;
                });
            });
            const days = Array.from(aggregateDaysMap.values()).sort((a,b) => a.date.localeCompare(b.date));
            const totals = categoryMeta.reduce((acc, meta) => {
                acc[meta.key] = days.reduce((sum, day) => sum + Number(day.categories[meta.key]?.actual || 0), 0);
                return acc;
            }, {});
            const totalActual = days.reduce((sum, day) => sum + Number(day.total_actual || 0), 0);
            const totalTarget = days.reduce((sum, day) => sum + Number(day.total_target || 0), 0);
            return {
                id: 'aggregate',
                full_name: campaignContext.enabled ? 'All campaign employees' : 'All employees',
                email: campaignContext.enabled ? (employees.length + ' campaign employees with activity') : (employees.length + ' employees combined'),
                days,
                totals,
                total_actual: totalActual,
                total_target: totalTarget,
                target_hit_pct: totalTarget ? Math.round((totalActual / totalTarget) * 100) : 0,
                average_daily: days.length ? Math.round(totalActual / days.length) : 0,
                best_day: days.slice().sort((a,b) => Number(b.total_actual || 0) - Number(a.total_actual || 0))[0] || null
            };
        }

        function updateHero(employees, aggregate){
            document.getElementById('heroEmployeeCount').textContent = String(employees.length);
            document.getElementById('heroDaysCount').textContent = String(aggregate?.days?.length || 0);
            document.getElementById('heroTotalActions').textContent = fmt(aggregate?.total_actual || 0);
            document.getElementById('heroAvgDaily').textContent = fmt(aggregate?.average_daily || 0);
            aggregatePeriodChip.textContent = formatPeriodLabel(state.startDate, state.endDate);
        }

        function buildCampaignReplySummaryCard(){
            if (!campaignContext.enabled || state.viewMode !== 'all') return '';
            const replyTotals = state.campaignReplyTotals || {};
            const automatedMessageThreads = firstDefinedNumber(replyTotals.automated_message_threads);
            const repliedMessageThreads = firstDefinedNumber(replyTotals.replied_message_threads);
            const calculatedReplyPercentage = automatedMessageThreads !== null
                ? (automatedMessageThreads > 0 ? Math.round((Number(repliedMessageThreads || 0) / automatedMessageThreads) * 1000) / 10 : 0)
                : null;
            const replyPercentage = firstDefinedNumber(replyTotals.reply_percentage, calculatedReplyPercentage);
            if (replyPercentage === null && automatedMessageThreads === null && repliedMessageThreads === null) return '';
            const replyCopy = automatedMessageThreads !== null && repliedMessageThreads !== null
                ? `${fmt(repliedMessageThreads)} of ${fmt(automatedMessageThreads)} automated message threads replied.`
                : 'Selected period reply rate after the automated campaign message.';
            return `
            <div class="summary-card">
                <div class="summary-label">Reply percentage</div>
                <div class="summary-value">${fmtPercentValue(replyPercentage || 0)}</div>
                <div class="summary-copy">${esc(replyCopy)}</div>
            </div>`;
        }

        function renderAggregateSummary(subject){
            const bestDayLabel = subject.best_day ? dayLabel(subject.best_day.date) : '—';
            const scopeLabel = state.viewMode === 'all'
                ? (campaignContext.enabled ? 'Summed across active users in this campaign and selected period.' : 'Summed across all employees and all selected days.')
                : 'Summed across all selected days for this employee.';
            const averageLabel = state.viewMode === 'all'
                ? (campaignContext.enabled ? 'Combined campaign daily average across users with activity.' : 'Combined daily average across the full team.')
                : 'Average actions per day for this employee.';
            const bestDayCopy = state.viewMode === 'all'
                ? (campaignContext.enabled ? 'Peak campaign activity day in the selected period.' : 'Peak activity day with the highest combined usage.')
                : 'Peak activity day for this employee.';
            aggregateSummaryGrid.innerHTML = `
            <div class="summary-card">
                <div class="summary-label">Total actions</div>
                <div class="summary-value">${fmt(subject.total_actual)}</div>
                <div class="summary-copy">${scopeLabel}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Average per day</div>
                <div class="summary-value">${fmt(subject.average_daily)}</div>
                <div class="summary-copy">${averageLabel}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Days shown</div>
                <div class="summary-value">${fmt(subject.days.length)}</div>
                <div class="summary-copy">Daily breakdown for the selected period.</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Best day</div>
                <div class="summary-value">${esc(bestDayLabel)}</div>
                <div class="summary-copy">${bestDayCopy}</div>
            </div>
            ${buildCampaignReplySummaryCard()}
        `;
        }

        function renderEmployeeList(){
            employeeListPill.textContent = state.employees.length + ' loaded';
            if (!state.employees.length) {
                employeeListEl.innerHTML = '<div class="empty-state">No employees found.</div>';
                return;
            }
            employeeListEl.innerHTML = state.employees.map(employee => `
            <div class="employee-stat-card ${Number(state.selectedEmployeeId) === Number(employee.id) ? 'is-active' : ''}" data-employee-id="${employee.id}">
                <span class="employee-avatar">${esc(initials(employee.full_name))}</span>
                <div class="employee-meta" style="flex:1;">
                    <strong>${esc(employee.full_name)}</strong>
                    <span>${esc(employee.email || 'No email')}</span>
                    <div class="employee-mini-grid">
                        <div class="employee-mini-stat"><b>${fmt(employee.total_actual)}</b><span>Total actions</span></div>
                        <div class="employee-mini-stat"><b>${fmt(employee.average_daily)}</b><span>Average daily</span></div>
                    </div>
                </div>
            </div>
        `).join('');
            employeeListEl.querySelectorAll('[data-employee-id]').forEach(card => {
                card.addEventListener('click', function(){
                    selectEmployee(this.dataset.employeeId);
                });
            });
        }


        function currentSubject(){
            if (state.viewMode === 'all') return state.aggregate;
            return state.employees.find(employee => Number(employee.id) === Number(state.selectedEmployeeId)) || state.employees[0] || state.aggregate;
        }

        function renderMetrics(subject){
            const bestDayText = subject.best_day ? `${dayLabel(subject.best_day.date)} · ${fmt(subject.best_day.total_actual)} actions` : 'No data';
            detailMetricGrid.innerHTML = `
            <div class="metric-card">
                <div class="label">Total actions</div>
                <div class="value">${fmt(subject.total_actual)}</div>
                <div class="sub">Across ${subject.days.length} days</div>
            </div>
            <div class="metric-card">
                <div class="label">Average daily usage</div>
                <div class="value">${fmt(subject.average_daily)}</div>
                <div class="sub">Average actions per day</div>
            </div>
            <div class="metric-card">
                <div class="label">Best day</div>
                <div class="value" style="font-size:20px;">${esc(bestDayText)}</div>
                <div class="sub">Highest total usage day</div>
            </div>
        `;
        }

        function renderGraph(subject){
            const maxCategoryTotal = Math.max(1, ...categoryMeta.map(meta => Number(subject.totals[meta.key] || 0)));
            const maxCategoryDay = Math.max(1, ...subject.days.flatMap(day => categoryMeta.map(meta => Number(day.categories[meta.key]?.actual || 0))));
            const categoryBars = categoryMeta.map(meta => {
                const total = Number(subject.totals[meta.key] || 0);
                const height = Math.max(8, Math.round((total / maxCategoryTotal) * 100));
                return `
                <div class="bar-col">
                    <div class="bar-wrap"><div class="bar" style="height:${height}%;"></div></div>
                    <div class="bar-value">${fmt(total)}</div>
                    <div class="bar-label">${esc(meta.label)}</div>
                </div>`;
            }).join('');

            const groupedDailyBars = subject.days.map(day => {
                const bars = categoryMeta.map(meta => {
                    const actual = Number(day.categories[meta.key]?.actual || 0);
                    const height = Math.max(actual > 0 ? 8 : 0, Math.round((actual / maxCategoryDay) * 100));
                    return `<div class="multi-bar is-${esc(meta.key)}" title="${esc(meta.label)}: ${fmt(actual)}" style="height:${height}%;"></div>`;
                }).join('');
                return `
                <div class="multi-bar-group">
                    <div class="multi-bar-wrap">${bars}</div>
                    <div class="bar-value">${fmt(day.total_actual)}</div>
                    <div class="bar-label">${esc(day.date)}</div>
                </div>`;
            }).join('');

            const heatmapRows = categoryMeta.map(meta => {
                const cells = subject.days.map(day => {
                    const actual = Number(day.categories[meta.key]?.actual || 0);
                    const fill = maxCategoryDay ? Math.min(100, Math.round((actual / maxCategoryDay) * 100)) : 0;
                    return `<div class="heat-cell" style="--fill:${fill}%;"><strong>${fmt(actual)}</strong><span>${esc(meta.label)}</span></div>`;
                }).join('');
                return `<div class="heatmap-row-label">${esc(meta.label)}</div>${cells}`;
            }).join('');

            const dailyMinWidth = Math.max(520, subject.days.length * 104);
            const heatmapMinWidth = Math.max(720, 180 + (subject.days.length * 86));
            const heatmapTemplateColumns = `minmax(140px, 180px) repeat(${subject.days.length}, minmax(72px, 86px))`;

            graphSection.innerHTML = `
            <div class="chart-grid">
                <div class="chart-card">
                    <div class="chart-title"><strong>Usage by category</strong><span>Total for selected period</span></div>
                    <div class="bar-chart">${categoryBars}</div>
                </div>
                <div class="chart-card">
                    <div class="chart-title"><strong>Daily total activity by category</strong><span>All categories in one view</span></div>
                    <div class="chart-legend">
                        ${categoryMeta.map(meta => `<span class="legend-item"><span class="legend-swatch is-${esc(meta.key)}"></span>${esc(meta.label)}</span>`).join('')}
                    </div>
                    <div class="chart-scroll-x is-focused-right">
                        <div class="multi-bar-chart" style="min-width:${dailyMinWidth}px;">${groupedDailyBars}</div>
                    </div>
                </div>
            </div>
            <div class="chart-card daily-activity-category-card" style="margin-top:14px;">
                <div class="chart-title"><strong>Daily activity by category</strong><span>Days are columns and categories are rows</span></div>
                <div class="chart-scroll-x is-focused-right">
                    <div class="heatmap" style="min-width:${heatmapMinWidth}px;grid-template-columns:${heatmapTemplateColumns};">
                        <div></div>
                        ${subject.days.map(day => `<div class="heatmap-head">${esc(dayLabel(day.date))}</div>`).join('')}
                        ${heatmapRows}
                    </div>
                </div>
            </div>
        `;
            focusLatestColumns(graphSection);
        }

        function progressCell(actual, target){
            const percent = target ? Math.min(100, Math.round((actual / target) * 100)) : 0;
            return `<div class="progress-row"><span style="min-width:38px;font-weight:700;color:#0f172a;">${percent}%</span><div class="progress-track"><div class="progress-fill" style="width:${percent}%;"></div></div></div>`;
        }

        function renderTable(subject){
            tableSection.innerHTML = `
            <div class="table-shell compact">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            ${categoryMeta.map(meta => `<th>${esc(meta.label)}</th>`).join('')}
                            <th>Total actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${subject.days.map(day => `
                            <tr>
                                <td><strong>${esc(day.date)}</strong></td>
                                ${categoryMeta.map(meta => {
                const actual = Number(day.categories[meta.key]?.actual || 0);
                return `<td>${fmt(actual)}</td>`;
            }).join('')}
                                <td><strong>${fmt(day.total_actual)}</strong></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        }

        function buildTableCopy(subject){
            const headers = ['Date'].concat(categoryMeta.map(meta => meta.label), ['Total actions']);
            const rows = subject.days.map(day => {
                const values = [day.date];
                categoryMeta.forEach(meta => {
                    values.push(String(Number(day.categories[meta.key]?.actual || 0)));
                });
                values.push(String(Number(day.total_actual || 0)));
                return values.join('\t');
            });
            return [headers.join('\t')].concat(rows).join('\n');
        }

        function buildGraphCopy(subject){
            const lines = [
                `${subject.full_name}`,
                `Total actions,${subject.total_actual}`,
                `Average daily,${subject.average_daily}`,
                '',
                `Daily totals,${categoryMeta.map(meta => meta.label).join(',')}`
            ];
            subject.days.forEach(day => lines.push(`${day.date},${day.total_actual},${categoryMeta.map(meta => Number(day.categories[meta.key]?.actual || 0)).join(',')}`));
            lines.push('', 'Category totals');
            categoryMeta.forEach(meta => lines.push(`${meta.label},${subject.totals[meta.key] || 0}`));
            return lines.join('\n');
        }

        function toggleDetailOverlay(visible){
            statisticsDetailOverlay.classList.toggle('is-visible', !!visible);
        }

        function renderDetail(){
            const subject = currentSubject();
            if (!subject) {
                detailTitle.textContent = 'No data';
                detailCopy.textContent = 'Statistics could not be loaded.';
                detailMetricGrid.innerHTML = '';
                graphSection.innerHTML = '';
                tableSection.innerHTML = '';
                toggleDetailOverlay(false);
                return;
            }
            renderAggregateSummary(subject);
            detailTitle.textContent = subject.full_name;
            detailCopy.textContent = state.viewMode === 'all'
                ? 'Combined team activity, shown day-by-day across all six activity categories.'
                : 'Individual daily usage split across all six activity categories.';
            renderMetrics(subject);
            renderGraph(subject);
            renderTable(subject);
            toggleDetailOverlay(!Array.isArray(subject.days) || !subject.days.length);
            graphSection.style.display = state.displayMode === 'graph' ? '' : 'none';
            tableSection.style.display = state.displayMode === 'table' ? '' : 'none';
            copyGraphBtn.onclick = () => copyText(buildGraphCopy(subject));
            copyTableBtn.onclick = () => copyText(buildTableCopy(subject));
        }

        function bindControls(){
            document.querySelectorAll('#viewModeSegment [data-view-mode]').forEach(btn => {
                btn.addEventListener('click', function(){
                    state.viewMode = this.dataset.viewMode;
                    if (state.viewMode === 'single') {
                        const nextEmployeeId = state.selectedEmployeeId || state.employees[0]?.id;
                        if (nextEmployeeId && selectEmployee(nextEmployeeId)) return;
                    } else {
                        state.selectedEmployeeId = null;
                        setEmployeeUrl(null);
                    }
                    syncViewModeButtons();
                    renderEmployeeList();
                    renderDetail();
                });
            });
            document.querySelectorAll('#displayModeSegment [data-display-mode]').forEach(btn => {
                btn.addEventListener('click', function(){
                    state.displayMode = this.dataset.displayMode;
                    document.querySelectorAll('#displayModeSegment [data-display-mode]').forEach(node => node.classList.toggle('is-active', node === this));
                    renderDetail();
                });
            });
            [startDateInput, endDateInput].forEach(input => {
                input.addEventListener('change', function(){
                    const nextStart = startDateInput.value;
                    const nextEnd = endDateInput.value;
                    if (!nextStart || !nextEnd) return;
                    if (nextStart > nextEnd) {
                        if (this === startDateInput) {
                            endDateInput.value = nextStart;
                        } else {
                            startDateInput.value = nextEnd;
                        }
                    }
                    state.startDate = startDateInput.value;
                    state.endDate = endDateInput.value;
                    loadStatistics();
                });
            });
        }

        async function loadStatistics(){
            if (!state.startDate || !state.endDate) {
                const defaults = getDefaultDateRange();
                state.startDate = defaults.startDate;
                state.endDate = defaults.endDate;
            }
            startDateInput.value = state.startDate;
            endDateInput.value = state.endDate;
            statusPill.textContent = 'Loading…';
            employeeListEl.innerHTML = '<div class="empty-state">Loading employee statistics…</div>';
            try {
                let statisticsPayload = [];
                const query = `?start_date=${encodeURIComponent(state.startDate)}&end_date=${encodeURIComponent(state.endDate)}${state.campaign_id ? `&campaign_id=${encodeURIComponent(state.campaign_id)}` : ''}`;
                try {
                    statisticsPayload = await api.request('/campaigns/statistics' + query);
                } catch (statsError) {
                    if (campaignContext.enabled) throw statsError;
                    statisticsPayload = await api.request('/campaigns/employee_settings');
                }
                const extractedPayload = extractStatisticsPayload(statisticsPayload);
                state.campaignFullPeriod = campaignContext.enabled ? extractedPayload.campaignFullPeriod : null;
                state.campaignReplyTotals = campaignContext.enabled ? extractedPayload.campaignReplyTotals : null;
                if (campaignContext.enabled) {
                    renderCampaignOverview(state.campaignFullPeriod);
                }

                let employees = extractedPayload.statistics.map(item => normalizeEmployee(item, state.startDate, state.endDate));
                if (campaignContext.enabled) {
                    employees = employees.filter(employee => employee.days.some(day => {
                        const total = Number(day.total_actual || 0);
                        return total > 0 || categoryMeta.some(meta => Number(day.categories?.[meta.key]?.actual || 0) > 0);
                    }));
                }
                state.employees = employees;
                state.selectedEmployeeId = null;
                state.aggregate = buildAggregate(employees);

                updateHero(employees, state.aggregate);
                const urlEmployeeId = getUrlEmployeeId();
                if (urlEmployeeId && employees.some(employee => Number(employee.id) === Number(urlEmployeeId))) {
                    state.selectedEmployeeId = urlEmployeeId;
                    state.viewMode = 'single';
                }
                syncViewModeButtons();
                renderEmployeeList();
                renderDetail();
                statusPill.textContent = employees.length
                    ? (campaignContext.enabled ? 'Campaign statistics' : (extractedPayload.hasLiveStatistics ? 'Live statistics' : 'Preview statistics'))
                    : 'No data';
            } catch (error) {
                statusPill.textContent = 'Could not load';
                employeeListEl.innerHTML = '<div class="empty-state">' + esc(error.message || 'Could not load employee statistics') + '</div>';
                aggregateSummaryGrid.innerHTML = '<div class="empty-state">Statistics overview is unavailable.</div>';
                detailTitle.textContent = 'Could not load statistics';
                detailCopy.textContent = error.message || 'Unknown error';
                detailMetricGrid.innerHTML = '';
                graphSection.innerHTML = '';
                tableSection.innerHTML = '';
                toggleDetailOverlay(false);
            }
        }

        applyCampaignContextText();
        renderCampaignOverview();
        bindControls();
        loadStatistics();
    })();
</script>
<?php include('../partial/footer-end.php'); ?>
