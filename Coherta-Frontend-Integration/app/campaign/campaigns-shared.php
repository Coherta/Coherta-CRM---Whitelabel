<?php
require_once '../base.php';
$sidebar_close = true;
$PageTitle = $PageTitle ?? 'Coherta Campaigns';

$campaignEmployees = $client->get('company/users') ?? [];
$campaignEmployees = array_values(array_filter($campaignEmployees, function ($employee) {
    return empty($employee['disabled']);
}));
$client->getCurrentUser();
$campaignEmployees = array_map(function ($employee) use ($client) {
    $employee['image'] = $client->getColleagueImage($employee['id'] ?? null);
    return $employee;
}, $campaignEmployees);

$dayLabels = [
    'monday' => 'Monday',
    'tuesday' => 'Tuesday',
    'wednesday' => 'Wednesday',
    'thursday' => 'Thursday',
    'friday' => 'Friday',
    'saturday' => 'Saturday',
    'sunday' => 'Sunday',
];

function campaign_find_employee($employees, $id) {
    foreach ($employees as $employee) {
        if ((int)$employee['id'] === (int)$id) return $employee;
    }
    return null;
}

function campaign_employee_initials($name) {
    $parts = preg_split('/\s+/', trim((string)$name));
    $initials = '';
    foreach ($parts as $part) {
        if ($part !== '') $initials .= mb_substr($part,0,1);
    }
    return mb_strtoupper(mb_substr($initials,0,2));
}

function campaign_topbar($active = 'dashboard') {
    $tabs = [
        'dashboard' => ['label'=>'Dashboard','href'=>'campaigns-dashboard.php'],
        'employees' => ['label'=>'Activity Settings','href'=>'campaigns-employee-settings.php'],
        'messages' => ['label'=>'Messages','href'=>'campaigns-messages.php'],
        'statistics' => ['label'=>'Statistics','href'=>'campaigns-statistics.php'],
        'settings' => ['label'=>'Settings','href'=>'/app/my-account'],
    ];
    echo '<div class="topbar" data-campaign-topbar>';
    echo '<div class="topbar-left"><div class="page-copy"><h1>Coherta Campaigns</h1><p>Campaign operations, employee pacing and inbox overview.</p></div></div>';
    echo '<button class="campaign-menu-toggle" type="button" aria-label="Open campaign menu" aria-expanded="false" data-campaign-menu-toggle><span></span><span></span><span></span></button>';
    echo '<div class="topbar-right">';
    foreach ($tabs as $key => $tab) {
        echo '<a class="top-link'.($active === $key ? ' active' : '').'" href="'.$tab['href'].'">'.htmlspecialchars($tab['label']).'</a>';
    }
    echo '</div></div>';
}

include('../partial/header.php');
?>
<style>
:root{--bg:#f5f7fb;--panel:#fff;--panel-2:#f9fbff;--line:#e7ebf3;--line-strong:#d7deeb;--text:#101828;--muted:#667085;--muted-2:#98a2b3;--primary:#4f46e5;--primary-2:#7c3aed;--primary-soft:rgba(79,70,229,.08);--success:#12b76a;--danger:#f04438;--warning:#f79009;--shadow-lg:0 20px 50px rgba(15,23,42,.08);--shadow-md:0 10px 30px rgba(15,23,42,.06);--radius-xl:28px;--radius-lg:20px;--radius-md:16px;--radius-sm:12px}
*{box-sizing:border-box}body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:radial-gradient(circle at top right,rgba(124,58,237,.10),transparent 24%),linear-gradient(180deg,#f8faff 0%,var(--bg) 100%);color:var(--text)}a{text-decoration:none;color:inherit}button,input,select,textarea{font:inherit}.app-shell{min-height:100vh}.content{min-width:0;padding:24px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 22px;border:1px solid rgba(255,255,255,.7);background:rgba(255,255,255,.74);backdrop-filter:blur(18px);box-shadow:var(--shadow-md);border-radius:24px;margin-bottom:22px}.topbar-left,.topbar-right{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.page-copy h1{margin:0;font-size:28px;letter-spacing:-.04em}.page-copy p{margin:4px 0 0;color:var(--muted);font-size:14px}.top-link{display:inline-flex;align-items:center;min-height:44px;padding:0 16px;border-radius:14px;border:1px solid var(--line);background:#fff;font-weight:700;color:#475467}.top-link.active{background:linear-gradient(180deg,rgba(79,70,229,.10),rgba(79,70,229,.04));border-color:rgba(79,70,229,.18);color:#2439c8}.panel{border-radius:var(--radius-xl);background:rgba(255,255,255,.88);border:1px solid rgba(255,255,255,.75);box-shadow:var(--shadow-lg);overflow:hidden}.panel-inner{padding:24px}.hero{position:relative;overflow:hidden;background:radial-gradient(circle at 0% 0%,rgba(255,255,255,.36),transparent 30%),linear-gradient(135deg,#1f2a6e 0%,#3547c8 45%,#6d48e5 100%);color:#fff}.hero::after{content:'';position:absolute;inset:auto -50px -70px auto;width:260px;height:260px;background:rgba(255,255,255,.10);border-radius:50%;filter:blur(4px)}.hero-grid{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(260px,.9fr);gap:18px;align-items:end;position:relative;z-index:1}.hero h2{margin:0;font-size:36px;line-height:1.05;letter-spacing:-.05em;max-width:12ch}.hero p{margin:14px 0 0;color:rgba(255,255,255,.78);line-height:1.6;max-width:60ch}.hero-actions{margin-top:22px;display:flex;gap:12px;flex-wrap:wrap}.hero-stats{display:grid;grid-template-columns:1fr 1fr;gap:12px}.hero-stat{padding:18px;border-radius:22px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.14);backdrop-filter:blur(14px)}.hero-stat strong{display:block;font-size:30px;line-height:1;letter-spacing:-.05em;margin-bottom:6px}.hero-stat span{display:block;color:rgba(255,255,255,.8);font-size:13px;line-height:1.4}.btn{appearance:none;border:0;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:48px;padding:0 18px;border-radius:16px;font-weight:700;transition:transform .15s ease,box-shadow .15s ease,background .15s ease}.btn:hover{transform:translateY(-1px)}.btn-primary{color:#fff;background:linear-gradient(135deg,#111827,#344054);box-shadow:0 12px 24px rgba(15,23,42,.18)}.btn-outline{color:#344054;background:transparent;border:1px solid var(--line-strong)}.btn-light{color:#1d2a7a;background:#fff;border:1px solid rgba(79,70,229,.10)}.btn-success{color:#fff;background:linear-gradient(135deg,#12b76a,#039855);box-shadow:0 12px 24px rgba(18,183,106,.24)}.section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}.section-head h3{margin:0;font-size:22px;letter-spacing:-.03em}.section-head p{margin:6px 0 0;color:var(--muted);line-height:1.6}.count-pill{white-space:nowrap;border-radius:999px;padding:10px 14px;background:var(--primary-soft);color:#3447c7;border:1px solid rgba(79,70,229,.10);font-weight:700;font-size:13px}.table-shell{overflow:auto}.data-table{width:100%;border-collapse:collapse}.data-table th,.data-table td{padding:16px 14px;border-bottom:1px solid var(--line);text-align:left;vertical-align:middle}.data-table th{font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:var(--muted-2)}.data-table td{font-size:14px;color:#344054}.data-table tbody tr:hover{background:rgba(79,70,229,.03)}.row-actions{display:flex;gap:8px;flex-wrap:wrap}.pill{display:inline-flex;align-items:center;gap:6px;min-height:30px;padding:0 10px;border-radius:999px;background:rgba(79,70,229,.08);color:#3447c7;font-size:12px;font-weight:700;border:0}.pill[disabled]{opacity:.55}.card{border:1px solid var(--line);border-radius:22px;background:linear-gradient(180deg,#fff,#f8fbff);padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.04)}.card h4{margin:0 0 8px;font-size:18px;letter-spacing:-.03em}.card p{margin:0;color:var(--muted);font-size:14px;line-height:1.55}.field{display:grid;gap:8px}.field label,.slider-label{font-size:13px;font-weight:700;color:#344054}.input,.select,.textarea{width:100%;min-height:48px;padding:12px 14px;border-radius:16px;border:1px solid var(--line);background:#fff;color:var(--text);outline:0}.textarea{min-height:140px;resize:vertical}.input:focus,.select:focus,.textarea:focus{border-color:rgba(79,70,229,.25);box-shadow:0 0 0 4px rgba(79,70,229,.08)}.grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.checkbox-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.checkbox-card{display:flex;gap:10px;align-items:flex-start;padding:14px;border-radius:16px;border:1px solid var(--line);background:#fff}.slider-row{display:grid;grid-template-columns:minmax(180px,220px) minmax(0,1fr) 62px;gap:14px;align-items:center;padding:14px 0;border-bottom:1px solid var(--line)}.slider-row:last-child{border-bottom:0}.slider-value{display:inline-flex;justify-content:center;align-items:center;min-height:40px;border-radius:12px;border:1px solid var(--line);background:#fff;font-weight:700}input[type=range]{width:100%}.modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.56);display:none;align-items:flex-start;justify-content:center;padding:24px;z-index:1200}.modal-overlay.show{display:flex}.modal-card{width:min(760px,100%);background:#fff;border-radius:28px;border:1px solid rgba(15,23,42,.08);box-shadow:0 30px 80px rgba(15,23,42,.18);overflow:hidden}.modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:28px 32px 20px;border-bottom:1px solid rgba(15,23,42,.08)}.modal-head h3{margin:0 0 6px;font-size:24px;line-height:1.2}.modal-head p{margin:0;color:#64748b;font-size:14px;line-height:1.6}.modal-close{width:42px;height:42px;border-radius:999px;border:1px solid rgba(15,23,42,.12);background:#f8fafc;color:#0f172a;font-size:24px;line-height:1;cursor:pointer}.modal-body{padding:28px 32px 32px}.modal-actions{display:flex;justify-content:flex-end;gap:12px;margin-top:24px;flex-wrap:wrap}.day-grid{display:grid;gap:12px}.day-row{display:grid;grid-template-columns:140px 100px 1fr 1fr;gap:12px;align-items:center;padding:14px;border-radius:18px;border:1px solid var(--line);background:linear-gradient(180deg,#fff,#fbfcff)}.day-row.disabled{opacity:.55}.day-toggle{display:inline-flex;align-items:center;gap:8px;font-weight:700}.toast{position:fixed;right:24px;bottom:24px;z-index:1400;padding:14px 18px;border-radius:16px;background:#111827;color:#fff;box-shadow:var(--shadow-lg);display:none;font-weight:700}.toast.show{display:block;animation:fadeInOut 2.2s ease}.threads-layout{display:grid;grid-template-columns:340px minmax(0,1fr);gap:18px;min-height:760px}.thread-list{border:1px solid var(--line);border-radius:22px;background:#fff;overflow:hidden}.thread-item{display:block;width:100%;box-sizing:border-box;padding:16px 18px;border:0;border-bottom:1px solid var(--line);background:#fff;text-align:left;outline:0;box-shadow:none;appearance:none;-webkit-appearance:none}.thread-item:hover,.thread-item:focus,.thread-item:focus-visible{border:0;outline:0;box-shadow:none}.thread-item.active{background:rgba(79,70,229,.06)}.thread-item strong{display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;font-size:15px;color:#101828}.thread-item span,.thread-item small{display:block;color:var(--muted);line-height:1.45}.thread-item.unread{background:rgba(79,70,229,.05)}.thread-item.read{background:#fff}.thread-item.unanswered{background:rgba(247,144,9,.08)}.thread-state{display:inline-flex;align-items:center;min-height:26px;padding:0 9px;border-radius:999px;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;margin-top:8px}.thread-state.unread{background:rgba(79,70,229,.12);color:#3447c7}.thread-state.read{background:#eef2f6;color:#667085}.thread-state.unanswered{background:rgba(247,144,9,.16);color:#b54708}.thread-view{border:1px solid var(--line);border-radius:22px;background:linear-gradient(180deg,#fff,#f8fbff);overflow:hidden;display:flex;flex-direction:column}.thread-view-head{padding:20px 22px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:12px}.thread-messages{padding:22px;display:flex;flex-direction:column;justify-content:flex-end;align-items:flex-start;gap:14px;min-height:520px;overflow:auto}.bubble{flex:0 0 auto;max-width:72%;padding:14px 16px;border-radius:18px;line-height:1.5;height:auto!important;align-self:flex-start}.bubble.left{justify-self:start;background:#fff;border:1px solid var(--line)}.bubble.right{align-self:flex-end;background:rgba(79,70,229,.10);border:1px solid rgba(79,70,229,.14)}.bubble small{display:block;margin-top:8px;color:var(--muted)}.bubble-status{display:block;margin-top:8px;font-size:12px;font-weight:700;color:#3447c7}.bubble-edit{margin-top:6px;border:0;background:transparent;color:#667085;font-size:12px;font-weight:700;padding:0;cursor:pointer}.thread-compose{padding:18px 22px;border-top:1px solid var(--line);display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px}.footer-note{margin-top:22px;text-align:center;color:var(--muted);font-size:13px}.employee-stack{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.avatar-stack{display:flex;align-items:center}.employee-avatar{width:32px;height:32px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-right:-6px;background:linear-gradient(135deg,#111827,#344054);color:#fff;font-size:11px;font-weight:800;border:2px solid #fff;box-shadow:0 8px 22px rgba(15,23,42,.10)}.is-loading{opacity:.65;pointer-events:none}.empty-state{padding:18px;border:1px dashed var(--line);border-radius:18px;background:#fbfcfe;color:var(--muted)}@keyframes fadeInOut{0%{opacity:0;transform:translateY(10px)}15%,85%{opacity:1;transform:translateY(0)}100%{opacity:0;transform:translateY(10px)}}@media (max-width:1100px){.threads-layout,.grid-2,.hero-grid,.day-row{grid-template-columns:1fr}}@media (max-width:767px){.content{padding:16px}.topbar,.panel-inner{padding:18px}.modal-overlay{padding:16px}.modal-card{border-radius:24px}.modal-head,.modal-body{padding-left:20px;padding-right:20px}.slider-row{grid-template-columns:1fr}.checkbox-grid{grid-template-columns:1fr}.thread-compose{grid-template-columns:1fr}}
</style>
<style>
.page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper{z-index:1100}
.footer{display:none!important}
.topbar{position:relative;z-index:50}
.campaign-menu-toggle{display:none;width:44px;height:44px;border-radius:14px;border:1px solid var(--line);background:#fff;color:#101828;align-items:center;justify-content:center;flex-direction:column;gap:5px;cursor:pointer;box-shadow:0 8px 20px rgba(15,23,42,.06)}
.campaign-menu-toggle span{display:block;width:18px;height:2px;border-radius:999px;background:currentColor}
@media (max-width:991px){
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover{transform:none!important;left:0!important;display:block!important;visibility:visible!important;opacity:1!important;z-index:1100}
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon ~ .page-body,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon ~ footer{margin-left:90px!important}
}
@media (max-width:767px){
  .content{padding:12px}
  .topbar{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:12px;padding:12px 14px;border-radius:18px;margin-bottom:12px}
  .topbar-left{min-width:0}
  .page-copy h1{font-size:20px;line-height:1.15;letter-spacing:0}
  .page-copy p{font-size:12px;line-height:1.35}
  .campaign-menu-toggle{display:inline-flex}
  .section-head{display:none}
  .topbar.menu-open{z-index:1000}
  .topbar-right{display:none;position:absolute;top:calc(100% + 8px);right:0;z-index:1001;width:min(260px,calc(100vw - 24px));padding:8px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:var(--shadow-lg);align-items:stretch;gap:6px}
  .topbar.menu-open .topbar-right{display:grid}
  .topbar.menu-open .campaign-menu-toggle{background:#111827;color:#fff}
  .top-link{width:100%;justify-content:flex-start;min-height:42px;border-radius:12px}
  .hero{max-height:20vh;border-radius:18px}
  .hero .panel-inner{padding:12px 14px}
  .hero::after{display:none}
  .hero-grid{gap:10px}
  .hero h2{font-size:22px;line-height:1.1;letter-spacing:0;max-width:none}
  .hero p{display:none}
  .hero-actions{margin-top:10px}
  .hero-actions .btn{min-height:38px;padding:0 14px;border-radius:12px}
  .hero-stats{grid-template-columns:repeat(4,minmax(0,1fr));gap:6px}
  .hero-stat{padding:8px 6px;border-radius:12px}
  .hero-stat strong{font-size:18px;letter-spacing:0}
  .hero-stat span{font-size:10px;line-height:1.2}
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const topbar = document.querySelector('[data-campaign-topbar]');
  const toggle = document.querySelector('[data-campaign-menu-toggle]');
  if (!topbar || !toggle) return;
  function setOpen(isOpen){
    topbar.classList.toggle('menu-open', isOpen);
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }
  toggle.addEventListener('click', function(event){
    event.stopPropagation();
    setOpen(!topbar.classList.contains('menu-open'));
  });
  document.addEventListener('click', function(event){
    if (!topbar.contains(event.target)) setOpen(false);
  });
  document.addEventListener('keydown', function(event){
    if (event.key === 'Escape') setOpen(false);
  });
});
window.CohertaCampaignsApi = (function(){
  const API_BASE = 'https://api.coherta.com';
  function getToken(){
    return document.body.getAttribute('session_id')
      || document.documentElement.getAttribute('session_id')
      || window.sessionStorage.getItem('session_id')
      || window.localStorage.getItem('session_id')
      || window.localStorage.getItem('access_token')
      || '';
  }
  async function request(path, options = {}){
    const headers = Object.assign({'Accept':'application/json'}, options.headers || {});
    const token = getToken();
    if (token) headers['Authorization'] = 'Bearer ' + token;
    if (options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';
    const response = await fetch(API_BASE + path, Object.assign({}, options, {headers}));
    let payload = null;
    const text = await response.text();
    try { payload = text ? JSON.parse(text) : null; } catch (e) { payload = {detail: text || 'Unknown error'}; }
    if (!response.ok) {
      throw new Error(payload && payload.detail ? payload.detail : ('Request failed with status ' + response.status));
    }
    return payload;
  }
  return { request, getToken, base: API_BASE };
})();
</script>
<?php include('../partial/loader.php'); ?>
