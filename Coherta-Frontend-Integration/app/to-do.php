<?php
require_once "./base.php";
$PageTitle = "To-do";
include('partial/header.php');
$sidebar_close = True;
function todo_h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function todo_ts($value) { if (!$value) return null; $ts = strtotime($value); return $ts !== false ? $ts : null; }
function todo_bucket($ts, $now) {
    if (!$ts) return 'no_task';
    if ($ts < $now) return 'overdue';
    if ($ts <= $now + 86400) return 'within_24h';
    if ($ts <= $now + (86400 * 7)) return 'within_7d';
    return 'later';
}
function todo_api($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . ($_SESSION['access_token'] ?? ''),
            "Accept: application/json"
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($res, true);
    return is_array($json) ? $json : [];
}

$selectedStatus = isset($_GET['s']) ? (string)$_GET['s'] : '0';
$selectedOwnerId = isset($_GET['owner_id']) && $_GET['owner_id'] !== '' ? (int)$_GET['owner_id'] : null;
$selectedPipelineId = isset($_GET['pipeline_id']) && ctype_digit((string)$_GET['pipeline_id']) ? (int)$_GET['pipeline_id'] : null;
$now = time();

$users = [];
foreach (todo_api("https://api.coherta.com/company/users") as $u) {
    if (!isset($u['id'])) continue;
    $users[(int)$u['id']] = $u['full_name'] ?? ('User #' . (int)$u['id']);
}

$rows = [];
$ownerSummary = [];
$pipelineSummary = [];
$globalSummary = ['created'=>0, 'overdue'=>0, 'within_24h'=>0, 'within_7d'=>0, 'no_task'=>0];

foreach (todo_api("https://api.coherta.com/users/todo/overview") as $post) {
    if ((string)($post['status'] ?? '') !== $selectedStatus) continue;

    $ownerId = isset($post['owner']) && $post['owner'] !== null ? (int)$post['owner'] : 0;
    $ownerName = $ownerId && isset($users[$ownerId]) ? $users[$ownerId] : 'Unassigned';
    $pipelineId = (int)($post['pipeline_id'] ?? 0);
    $pipelineTitle = (string)($post['title'] ?? 'Unknown pipeline');
    $taskDate = $post['newest_task_created_at'] ?? null;
    $taskTs = todo_ts($taskDate);
    $bucket = todo_bucket($taskTs, $now);

    $row = [
            'id' => (int)($post['id'] ?? 0),
            'owner_id' => $ownerId,
            'owner_name' => $ownerName,
            'pipeline_id' => $pipelineId,
            'pipeline_title' => $pipelineTitle,
            'company_name' => (string)($post['company_name'] ?? 'Unknown company'),
            'country' => (string)($post['country'] ?? ''),
            'vat_code' => (string)($post['company_id'] ?? ''),
            'next_task' => $taskDate,
            'next_task_ts' => $taskTs,
            'next_task_text' => (string)($post['newest_task'] ?? ''),
            'bucket' => $bucket,
            'coherta_url' => 'https://www.coherta.com/app/lead.php?id=' . (int)($post['id'] ?? 0)
    ];
    $rows[] = $row;

    $globalSummary['created']++;
    if (isset($globalSummary[$bucket])) $globalSummary[$bucket]++;

    if (!isset($ownerSummary[$ownerId])) {
        $ownerSummary[$ownerId] = ['owner_id'=>$ownerId, 'owner_name'=>$ownerName, 'created'=>0, 'overdue'=>0, 'within_24h'=>0, 'within_7d'=>0, 'no_task'=>0, 'pipelines'=>[]];
    }
    $ownerSummary[$ownerId]['created']++;
    if (isset($ownerSummary[$ownerId][$bucket])) $ownerSummary[$ownerId][$bucket]++;
    $ownerSummary[$ownerId]['pipelines'][$pipelineId] = $pipelineTitle;

    if (!isset($pipelineSummary[$pipelineId])) {
        $pipelineSummary[$pipelineId] = ['pipeline_id'=>$pipelineId, 'pipeline_title'=>$pipelineTitle, 'created'=>0, 'overdue'=>0, 'within_24h'=>0, 'within_7d'=>0, 'no_task'=>0];
    }
    $pipelineSummary[$pipelineId]['created']++;
    if (isset($pipelineSummary[$pipelineId][$bucket])) $pipelineSummary[$pipelineId][$bucket]++;
}

usort($rows, fn($a, $b) => (($a['next_task_ts'] ?? PHP_INT_MAX) === ($b['next_task_ts'] ?? PHP_INT_MAX)) ? strcmp($a['company_name'], $b['company_name']) : (($a['next_task_ts'] ?? PHP_INT_MAX) <=> ($b['next_task_ts'] ?? PHP_INT_MAX)));
usort($ownerSummary, fn($a, $b) => $a['overdue'] === $b['overdue'] ? ($b['created'] <=> $a['created']) : ($b['overdue'] <=> $a['overdue']));
usort($pipelineSummary, fn($a, $b) => $a['overdue'] === $b['overdue'] ? ($b['created'] <=> $a['created']) : ($b['overdue'] <=> $a['overdue']));

$filteredRows = array_values(array_filter($rows, function ($row) use ($selectedOwnerId, $selectedPipelineId) {
    if ($selectedOwnerId !== null && $row['owner_id'] !== $selectedOwnerId) return false;
    if ($selectedPipelineId !== null && $row['pipeline_id'] !== $selectedPipelineId) return false;
    return true;
}));

$selectedOwnerName = null;
if ($selectedOwnerId !== null) {
    foreach ($ownerSummary as $owner) {
        if ($owner['owner_id'] === $selectedOwnerId) { $selectedOwnerName = $owner['owner_name']; break; }
    }
    if ($selectedOwnerName === null) $selectedOwnerName = $selectedOwnerId === 0 ? 'Unassigned' : ('Owner #' . $selectedOwnerId);
}
?>

<?php include('partial/loader.php'); ?>

<style>
    :root { --todo-bg:#f5f7fb; --todo-card:#fff; --todo-text:#111827; --todo-muted:#6b7280; --todo-border:#e5e7eb; --todo-shadow:0 14px 34px rgba(17,24,39,.06); }
    .page-body.no-top-margin{margin-top:0!important;padding-top:0!important;}
    .todo-overview-shell{padding:22px;background:var(--todo-bg);min-height:calc(100vh - 150px)}
    .todo-hero{position:relative;overflow:hidden;border-radius:24px;border:1px solid var(--todo-border);background:radial-gradient(circle at top right,rgba(99,102,241,.16),transparent 34%),linear-gradient(180deg,#fff 0%,#f8faff 100%);box-shadow:var(--todo-shadow);padding:26px;margin-bottom:20px}
    .todo-hero-grid{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(280px,.55fr);gap:20px;align-items:center}.todo-hero h1{margin:0;font-size:34px;line-height:1.08;letter-spacing:-.03em;color:var(--todo-text)}.todo-hero p{margin:10px 0 0;color:var(--todo-muted);font-size:14px;max-width:760px}.todo-filter-row{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .todo-chip,.todo-filter-link{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:12px;background:#fff;border:1px solid var(--todo-border);color:#374151;text-decoration:none;font-size:13px;font-weight:700;transition:all .18s ease}.todo-filter-link.active,.todo-chip.dark{background:#111827;color:#fff;border-color:#111827}.todo-chip:hover,.todo-filter-link:hover{text-decoration:none;transform:translateY(-1px)}
    .todo-metric-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:20px}.todo-metric,.todo-section-card{background:var(--todo-card);border:1px solid var(--todo-border);border-radius:20px;box-shadow:0 8px 24px rgba(17,24,39,.04)}.todo-metric{padding:18px}.todo-metric-label{font-size:13px;color:var(--todo-muted);margin-bottom:8px;font-weight:700}.todo-metric-value{font-size:34px;color:var(--todo-text);font-weight:800;line-height:1;letter-spacing:-.03em}.todo-metric-note{margin-top:10px;font-size:12px;color:var(--todo-muted)}.todo-metric.neutral{border-left:4px solid #6b7280}.todo-metric.danger{border-left:4px solid #dc2626}.todo-metric.warning{border-left:4px solid #f59e0b}.todo-metric.soon{border-left:4px solid #2563eb}
    .todo-two-col{display:grid;grid-template-columns:1.15fr 1fr;gap:20px}.todo-section-card{padding:18px;margin-bottom:20px}.todo-section-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}.todo-section-head h2{margin:0;font-size:20px;color:var(--todo-text);letter-spacing:-.02em}.todo-section-head p{margin:6px 0 0;color:var(--todo-muted);font-size:13px}
    .todo-owner-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px}.todo-owner-card{display:block;padding:16px;border:1px solid var(--todo-border);border-radius:18px;text-decoration:none;color:inherit;background:linear-gradient(180deg,#fff 0%,#fafbff 100%);transition:all .18s ease}.todo-owner-card:hover{border-color:#cbd5e1;transform:translateY(-1px);text-decoration:none}.todo-owner-card.active{border-color:#111827;box-shadow:0 0 0 2px rgba(17,24,39,.06) inset}.todo-owner-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:12px}.todo-owner-name{font-size:16px;font-weight:800;color:var(--todo-text)}.todo-owner-sub{font-size:12px;color:var(--todo-muted);margin-top:4px}.todo-owner-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-bottom:12px}.todo-mini-stat{background:#f8fafc;border-radius:13px;padding:10px 8px;text-align:center}.todo-mini-stat strong{display:block;font-size:18px;color:var(--todo-text)}.todo-mini-stat span{display:block;font-size:11px;color:var(--todo-muted);margin-top:4px}.todo-overdue-bar{height:10px;background:#eef2f7;border-radius:999px;overflow:hidden;display:flex;margin-bottom:10px}.todo-seg-overdue{background:#dc2626}.todo-seg-24h{background:#f59e0b}.todo-seg-7d{background:#2563eb}.todo-seg-no-task{background:#9ca3af}.todo-owner-foot{font-size:12px;color:var(--todo-muted);display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}
    .todo-table-wrap{width:100%;overflow-x:auto}.todo-pipeline-table,.todo-task-table{width:100%;border-collapse:collapse}.todo-pipeline-table th,.todo-pipeline-table td,.todo-task-table th,.todo-task-table td{padding:12px 10px;border-bottom:1px solid #eef2f7;text-align:left;vertical-align:top;font-size:14px}.todo-pipeline-table th,.todo-task-table th{font-size:12px;color:var(--todo-muted);text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}.todo-table-link{color:var(--todo-text);text-decoration:none;font-weight:700}.todo-table-link:hover{text-decoration:underline}.todo-status-pill{display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:800;white-space:nowrap}.todo-status-overdue{background:#fee2e2;color:#991b1b}.todo-status-24h{background:#fef3c7;color:#92400e}.todo-status-7d{background:#dbeafe;color:#1d4ed8}.todo-status-later{background:#e0f2fe;color:#075985}.todo-status-no-task{background:#e5e7eb;color:#374151}.todo-task-line{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.todo-timeline-cell{min-width:210px}.todo-timeline-track{height:12px;width:100%;background:#edf2f7;border-radius:999px;overflow:hidden;margin:7px 0 6px}.todo-fill-red,.todo-fill-yellow,.todo-fill-blue,.todo-fill-gray,.todo-fill-cyan{height:100%;border-radius:999px}.todo-fill-red{background:#dc2626}.todo-fill-yellow{background:#f59e0b}.todo-fill-blue{background:#2563eb}.todo-fill-gray{background:#9ca3af}.todo-fill-cyan{background:#06b6d4}.todo-timeline-caption{font-size:12px;color:var(--todo-muted)}.todo-empty-state{padding:28px;border:1px dashed #cbd5e1;border-radius:16px;color:var(--todo-muted);text-align:center;background:#f8fafc}
    @media(max-width:1280px){.todo-hero-grid,.todo-two-col{grid-template-columns:1fr}.todo-filter-row{justify-content:flex-start}.todo-metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:768px){.todo-overview-shell{padding:14px}.todo-hero{padding:18px;border-radius:18px}.todo-hero h1{font-size:28px}.todo-metric-grid,.todo-owner-grid{grid-template-columns:1fr}.todo-owner-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include('partial/sidebar.php'); ?>
        <div class="page-body no-top-margin">
            <div class="container-fluid">
                <div class="todo-overview-shell">
                    <div class="todo-hero">
                        <div class="todo-hero-grid">
                            <div><h1>Overblik over opgaver</h1><p>Få et samlet overblik på tværs af alle pipelines og owners, og gå direkte videre til selve emnekortet i Coherta.</p></div>
                            <div class="todo-filter-row">
                                <a class="todo-filter-link <?php echo $selectedStatus === '0' ? 'active' : ''; ?>" href="?s=0">Aktive</a>
                                <a class="todo-filter-link <?php echo $selectedStatus === '1' ? 'active' : ''; ?>" href="?s=1">Lukkede</a>
                                <?php if ($selectedOwnerId !== null || $selectedPipelineId !== null): ?><a class="todo-chip" href="?s=<?php echo todo_h($selectedStatus); ?>">Nulstil filtre</a><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="todo-metric-grid">
                        <div class="todo-metric neutral"><div class="todo-metric-label">Oprettet</div><div class="todo-metric-value"><?php echo number_format($globalSummary['created'],0,',','.'); ?></div><div class="todo-metric-note">Samlet antal opgaver i den valgte status.</div></div>
                        <div class="todo-metric danger"><div class="todo-metric-label">Overdue</div><div class="todo-metric-value"><?php echo number_format($globalSummary['overdue'],0,',','.'); ?></div><div class="todo-metric-note">Opgaver med deadline før nu.</div></div>
                        <div class="todo-metric warning"><div class="todo-metric-label">Forfalder indenfor 24 timer</div><div class="todo-metric-value"><?php echo number_format($globalSummary['within_24h'],0,',','.'); ?></div><div class="todo-metric-note">Kræver hurtig opmærksomhed.</div></div>
                        <div class="todo-metric soon"><div class="todo-metric-label">Forfalder indenfor 7 dage</div><div class="todo-metric-value"><?php echo number_format($globalSummary['within_7d'],0,',','.'); ?></div><div class="todo-metric-note">Kommende opgaver i nær fremtid.</div></div>
                    </div>

                    <div class="todo-two-col">
                        <div class="todo-section-card">
                            <div class="todo-section-head"><div><h2>Owners på tværs af pipelines</h2><p>Klik dig ind på en person for at se alle vedkommendes opgaver samlet på tværs.</p></div></div>
                            <?php if (empty($ownerSummary)): ?>
                                <div class="todo-empty-state">Der er ingen opgaver at vise i den valgte status.</div>
                            <?php else: ?>
                                <div class="todo-owner-grid">
                                    <?php foreach ($ownerSummary as $owner): ?>
                                        <?php $total=max(1,$owner['created']); $ownerUrl='?s='.urlencode($selectedStatus).'&owner_id='.urlencode((string)$owner['owner_id']).'#todo-details'; ?>
                                        <a class="todo-owner-card <?php echo $selectedOwnerId === $owner['owner_id'] ? 'active' : ''; ?>" href="<?php echo todo_h($ownerUrl); ?>">
                                            <div class="todo-owner-top"><div><div class="todo-owner-name"><?php echo todo_h($owner['owner_name']); ?></div><div class="todo-owner-sub"><?php echo count($owner['pipelines']); ?> pipeline<?php echo count($owner['pipelines']) === 1 ? '' : 's'; ?></div></div><span class="todo-status-pill <?php echo $owner['overdue'] > 0 ? 'todo-status-overdue' : 'todo-status-later'; ?>"><?php echo (int)$owner['overdue']; ?> overdue</span></div>
                                            <div class="todo-owner-stats"><div class="todo-mini-stat"><strong><?php echo (int)$owner['created']; ?></strong><span>Oprettet</span></div><div class="todo-mini-stat"><strong><?php echo (int)$owner['overdue']; ?></strong><span>Overdue</span></div><div class="todo-mini-stat"><strong><?php echo (int)$owner['within_24h']; ?></strong><span>24 timer</span></div><div class="todo-mini-stat"><strong><?php echo (int)$owner['within_7d']; ?></strong><span>7 dage</span></div></div>
                                            <div class="todo-overdue-bar">
                                                <?php if($owner['overdue']>0): ?><div class="todo-seg-overdue" style="width:<?php echo ($owner['overdue']/$total)*100; ?>%"></div><?php endif; ?>
                                                <?php if($owner['within_24h']>0): ?><div class="todo-seg-24h" style="width:<?php echo ($owner['within_24h']/$total)*100; ?>%"></div><?php endif; ?>
                                                <?php if($owner['within_7d']>0): ?><div class="todo-seg-7d" style="width:<?php echo ($owner['within_7d']/$total)*100; ?>%"></div><?php endif; ?>
                                                <?php if($owner['no_task']>0): ?><div class="todo-seg-no-task" style="width:<?php echo ($owner['no_task']/$total)*100; ?>%"></div><?php endif; ?>
                                            </div>
                                            <div class="todo-owner-foot"><span>Rød = overdue · Gul = 24 timer · Blå = 7 dage</span><span>Vis detaljer →</span></div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="todo-section-card">
                            <div class="todo-section-head"><div><h2>Pipelines</h2><p>Se hvor presset opgavebilledet er i hver pipeline.</p></div></div>
                            <?php if (empty($pipelineSummary)): ?>
                                <div class="todo-empty-state">Ingen pipelines at vise.</div>
                            <?php else: ?>
                                <div class="todo-table-wrap"><table class="todo-pipeline-table"><thead><tr><th>Pipeline</th><th>Oprettet</th><th>Overdue</th><th>24 timer</th><th>7 dage</th></tr></thead><tbody>
                                        <?php foreach ($pipelineSummary as $pipeline): ?>
                                            <tr><td><a class="todo-table-link" href="?s=<?php echo todo_h($selectedStatus); ?>&pipeline_id=<?php echo (int)$pipeline['pipeline_id']; ?>#todo-details"><?php echo todo_h($pipeline['pipeline_title']); ?></a></td><td><?php echo (int)$pipeline['created']; ?></td><td><?php echo (int)$pipeline['overdue']; ?></td><td><?php echo (int)$pipeline['within_24h']; ?></td><td><?php echo (int)$pipeline['within_7d']; ?></td></tr>
                                        <?php endforeach; ?>
                                        </tbody></table></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="todo-section-card" id="todo-details">
                        <div class="todo-section-head"><div><h2><?php echo $selectedOwnerId !== null ? 'Opgaver for '.todo_h($selectedOwnerName) : ($selectedPipelineId !== null ? 'Opgaver i valgt pipeline' : 'Alle opgaver'); ?></h2><p><?php echo $selectedOwnerId !== null ? 'Viser alle opgaver på tværs af pipelines for den valgte owner.' : ($selectedPipelineId !== null ? 'Viser alle opgaver i den valgte pipeline.' : 'Samlet liste over opgaver sorteret efter nærmeste deadline.'); ?></p></div></div>
                        <?php if (empty($filteredRows)): ?>
                            <div class="todo-empty-state">Ingen opgaver matcher det valgte filter.</div>
                        <?php else: ?>
                            <div class="todo-table-wrap"><table class="todo-task-table"><thead><tr><th>Owner</th><th>Pipeline</th><th>Lead</th><th>Forfald</th><th>Beskrivelse</th><th>Coherta</th></tr></thead><tbody>
                                    <?php foreach ($filteredRows as $row): ?>
                                        <?php
                                        $class='todo-fill-cyan'; $statusClass='todo-status-later'; $statusText='Senere'; $width=18; $caption='Ikke akut';
                                        if($row['bucket']==='no_task'){ $class='todo-fill-gray'; $statusClass='todo-status-no-task'; $statusText='Ingen opgave'; $width=100; $caption='Der mangler næste opgave'; }
                                        elseif($row['bucket']==='overdue'){ $class='todo-fill-red'; $statusClass='todo-status-overdue'; $statusText='Overdue'; $width=100; $days=max(1,(int)floor(($now-$row['next_task_ts'])/86400)); $caption=$days.' dag'.($days===1?'':'e').' forsinket'; }
                                        elseif($row['bucket']==='within_24h'){ $class='todo-fill-yellow'; $statusClass='todo-status-24h'; $statusText='24 timer'; $width=70; $caption='Forfalder indenfor 24 timer'; }
                                        elseif($row['bucket']==='within_7d'){ $class='todo-fill-blue'; $statusClass='todo-status-7d'; $statusText='7 dage'; $width=42; $caption='Forfalder indenfor 7 dage'; }
                                        $vat = trim($row['country'].$row['vat_code']);
                                        ?>
                                        <tr>
                                            <td><a class="todo-table-link" href="?s=<?php echo todo_h($selectedStatus); ?>&owner_id=<?php echo (int)$row['owner_id']; ?>#todo-details"><?php echo todo_h($row['owner_name']); ?></a></td>
                                            <td><a class="todo-table-link" href="?s=<?php echo todo_h($selectedStatus); ?>&pipeline_id=<?php echo (int)$row['pipeline_id']; ?>#todo-details"><?php echo todo_h($row['pipeline_title']); ?></a></td>
                                            <td><div><strong><?php echo todo_h($row['company_name']); ?></strong></div><?php if($vat !== ''): ?><div style="font-size:12px;color:#6b7280"><?php echo todo_h($vat); ?></div><?php endif; ?></td>
                                            <td class="todo-timeline-cell"><div class="todo-task-line"><span class="todo-status-pill <?php echo $statusClass; ?>"><?php echo todo_h($statusText); ?></span><span style="font-size:12px;color:#6b7280"><?php echo $row['next_task'] ? todo_h($row['next_task']) : 'Ingen dato'; ?></span></div><div class="todo-timeline-track"><div class="<?php echo $class; ?>" style="width:<?php echo $width; ?>%"></div></div><div class="todo-timeline-caption"><?php echo todo_h($caption); ?></div></td>
                                            <td><?php echo todo_h($row['next_task_text']); ?></td>
                                            <td><a class="todo-chip dark" href="<?php echo todo_h($row['coherta_url']); ?>" target="_blank">Åbn emnekort</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody></table></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php include('partial/footer.php'); ?>
    </div>
</div>

<?php include('partial/scripts.php'); ?>
<script src="assets/js/tooltip-init.js"></script>
<?php include('partial/footer-end.php'); ?>
