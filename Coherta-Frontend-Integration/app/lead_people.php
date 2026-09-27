<?php
require_once "./base.php";

function people_card_h($value): string
{
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

function people_card_array($value): array
{
    return is_array($value) ? $value : [];
}

function people_card_owner_id(array $person): string
{
    if (isset($person["owner_id"]) && $person["owner_id"] !== "") {
        return (string)$person["owner_id"];
    }

    $owner = $person["owner"] ?? null;
    if (is_array($owner)) {
        return (string)($owner["id"] ?? "");
    }

    return is_scalar($owner) ? (string)$owner : "";
}

function people_card_owner_name(array $person): string
{
    $owner = $person["owner"] ?? null;
    $ownerName = (string)($person["owner_name"] ?? $person["owner_full_name"] ?? $person["owner_email"] ?? "");

    if ($ownerName === "" && is_array($owner)) {
        $ownerName = (string)($owner["name"] ?? $owner["full_name"] ?? $owner["email"] ?? "");
    }

    return trim($ownerName);
}

$leadId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$pipelineId = isset($_GET["pipeline_id"]) ? (int)$_GET["pipeline_id"] : 0;
$person = null;
$pipelineTitle = "";
$missingEndpoint = false;

if ($leadId > 0 && $pipelineId > 0) {
    $page = 1;
    $maxPages = 100;
    do {
        $payload = $client->get("pipeline/all/leads/people?pipeline_id={$pipelineId}&page={$page}");
        if (!is_array($payload)) {
            break;
        }

        $pipelineTitle = (string)($payload["pipeline"]["title"] ?? $pipelineTitle);
        $rows = people_card_array($payload["data"] ?? []);
        foreach ($rows as $row) {
            if ((int)($row["id"] ?? 0) === $leadId || (int)($row["pipeline_company_id"] ?? 0) === $leadId) {
                $person = $row;
                break 2;
            }
        }

        $totalPages = (int)($payload["pagination"]["total_pages"] ?? $payload["pagination"]["last_page"] ?? 0);
        $hasNext = $totalPages > 0 ? $page < $totalPages : count($rows) > 0;
        $page++;
    } while ($hasNext && $page <= $maxPages);
} elseif ($leadId > 0) {
    $missingEndpoint = true;
}

if ($missingEndpoint) {
    $PageTitle = "Missing People lead endpoint";
    include("partial/header.php");
    include("partial/loader.php");
    ?>
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <div class="page-body-wrapper">
            <?php include("partial/sidebar.php"); ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">Missing endpoint for People lead card</h4>
                        <p>The card can open from People Lists because the row carries both <code>id</code> and <code>pipeline_id</code>. A direct card URL with only <code>id</code> needs a single-record endpoint.</p>
                        <pre class="mb-0">GET /pipeline/leads/people/details/{pipeline_company_id}
Authorization: Bearer &lt;token&gt;

Response 200:
{
  "id": 123,
  "pipeline_id": 456,
  "participant_id": 789,
  "name": "Jane Doe",
  "job_title": "VP Sales",
  "email": "jane@example.com",
  "phone_number": "+4512345678",
  "linkedin_url": "https://www.linkedin.com/in/...",
  "company_name": "Example A/S",
  "company_linkedin_url": "https://www.linkedin.com/company/...",
  "location": "Copenhagen",
  "city": "Copenhagen",
  "region": "Capital Region",
  "skills": [{"skill": "Sales"}],
  "stage": 1,
  "added": "2026-06-07T12:00:00Z",
  "last_updated": "2026-06-07T12:00:00Z"
}</pre>
                    </div>
                </div>
                <?php include("partial/footer.php"); ?>
            </div>
        </div>
    </div>
    <?php
    include("partial/scripts.php");
    include("partial/footer-end.php");
    exit;
}

$notes = $leadId > 0 ? $client->get("pipeline/leads/notes?id=" . urlencode((string)$leadId)) : [];
$tasksPayload = $leadId > 0 ? $client->get("pipeline/leads/todo/" . urlencode((string)$leadId)) : [];
$tasks = people_card_array($tasksPayload["todos"] ?? $tasksPayload);
$notes = people_card_array($notes);
$users = people_card_array($client->get("company/users"));
$name = (string)($person["name"] ?? "People lead");
$currentStageId = (int)($person["stage"] ?? 1);
$currentStageTitle = $stages[$currentStageId]["title"] ?? "Unknown";
$currentOwnerId = $person ? people_card_owner_id($person) : "";
$currentOwnerName = $person ? people_card_owner_name($person) : "";
$currentOwnerInUsers = false;
foreach ($users as $user) {
    if ((string)($user["id"] ?? "") === $currentOwnerId) {
        $currentOwnerInUsers = true;
        $currentOwnerName = (string)($user["name"] ?? $user["full_name"] ?? $user["email"] ?? $currentOwnerName);
        break;
    }
}
if ($currentOwnerName === "") {
    $currentOwnerName = "Unassigned";
}
$showCurrentOwnerOption = !$currentOwnerInUsers && $currentOwnerId !== "" && $currentOwnerId !== "0" && $currentOwnerName !== "Unassigned";
$unassignedSelected = $currentOwnerId === "" || $currentOwnerId === "0" || (!$currentOwnerInUsers && !$showCurrentOwnerOption);
$PageTitle = $name;
$stylesheets[] = "assets/css/vendors/todo.css";

include("partial/header.php");
?>
<?php include("partial/loader.php"); ?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include("partial/sidebar.php"); ?>
        <div class="page-body">
            <div class="container-fluid people-card-page">
                <?php if (!$person) { ?>
                    <div class="alert alert-warning">This person could not be found in the selected People List.</div>
                <?php } else { ?>
                    <div class="people-card-hero">
                        <div class="people-card-identity">
                            <?php if (!empty($person["image"])) { ?>
                                <img src="https://api.coherta.com/images/<?php echo people_card_h($person["image"]); ?>" alt="">
                            <?php } else { ?>
                                <span><?php echo people_card_h(substr($name, 0, 1)); ?></span>
                            <?php } ?>
                            <div>
                                <h1><?php echo people_card_h($name); ?></h1>
                                <p><?php echo people_card_h($person["job_title"] ?? ""); ?></p>
                            </div>
                        </div>
                        <div class="people-card-actions">
                            <?php if (!empty($person["linkedin_url"])) { ?>
                                <a class="btn btn-primary" target="_blank" href="<?php echo people_card_h($person["linkedin_url"]); ?>"><i class="fa fa-linkedin"></i> LinkedIn</a>
                            <?php } ?>
                            <?php if ($pipelineId > 0) { ?>
                                <a class="btn btn-light" href="leads_people.php?pipeline_id=<?php echo (int)$pipelineId; ?>"><i class="fa fa-arrow-left"></i> People List</a>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-xl-4">
                            <div class="people-card-panel">
                                <div class="people-card-section-head">
                                    <h2>Status & Owner</h2>
                                    <span class="people-save-status" id="peopleStatusSaveState" aria-live="polite"></span>
                                </div>
                                <div class="people-current-state">
                                    <span>Stage: <strong id="peopleCurrentStageLabel"><?php echo people_card_h($currentStageTitle); ?></strong></span>
                                    <span>Owner: <strong id="peopleCurrentOwnerLabel"><?php echo people_card_h($currentOwnerName); ?></strong></span>
                                </div>
                                <div class="people-status-form">
                                    <label for="peopleStageSelect">Stage</label>
                                    <select id="peopleStageSelect" class="form-control">
                                        <?php foreach ($stages as $stageId => $stage) { ?>
                                            <option value="<?php echo (int)$stageId; ?>"<?php echo (int)$stageId === $currentStageId ? " selected" : ""; ?>>
                                                <?php echo people_card_h($stage["title"] ?? $stageId); ?>
                                            </option>
                                        <?php } ?>
                                    </select>

                                    <label for="peopleOwnerSelect">Owner</label>
                                    <select id="peopleOwnerSelect" class="form-control">
                                        <option value="0"<?php echo $unassignedSelected ? " selected" : ""; ?>>Unassigned</option>
                                        <?php if ($showCurrentOwnerOption) { ?>
                                            <option value="<?php echo people_card_h($currentOwnerId); ?>" selected>
                                                <?php echo people_card_h($currentOwnerName); ?>
                                            </option>
                                        <?php } ?>
                                        <?php foreach ($users as $user) {
                                            if($user["disabled"] ?? false) continue;
                                            $userId = (string)($user["id"] ?? "");
                                            if ($userId === "") continue;
                                            ?>
                                            <option value="<?php echo people_card_h($userId); ?>"<?php echo $userId === $currentOwnerId ? " selected" : ""; ?>>
                                                <?php echo people_card_h($user["full_name"] ?? $userId); ?>
                                            </option>
                                        <?php } ?>
                                    </select>

                                    <button class="btn btn-primary btn-sm" type="button" id="peopleSaveStatus">Save changes</button>
                                </div>
                            </div>

                            <div class="people-card-panel">
                                <h2>Person</h2>
                                <dl class="people-card-facts">
                                    <?php
                                    $facts = [
                                        "Email" => $person["email"] ?? "",
                                        "Phone" => $person["phone_number"] ?? $person["phonenumber"] ?? $person["phonenumber_2"] ?? "",
                                        "Location" => $person["location"] ?? "",
                                        "City" => $person["city"] ?? "",
                                        "Region" => $person["region"] ?? "",
                                        "Followers" => $person["followers"] ?? "",
                                        "Connections" => $person["connections"] ?? "",
                                        "List" => $pipelineTitle,
                                    ];
                                    foreach ($facts as $label => $value) {
                                        if ($value === "" || $value === null) continue;
                                        echo "<div><dt>" . people_card_h($label) . "</dt><dd>" . people_card_h($value) . "</dd></div>";
                                    }
                                    ?>
                                </dl>
                                <?php $skills = people_card_array($person["skills"] ?? []); ?>
                                <?php if ($skills) { ?>
                                    <div class="people-card-skills">
                                        <?php foreach (array_slice($skills, 0, 12) as $skill) {
                                            $label = $skill["skill"] ?? "";
                                            if ($label !== "") echo "<span>" . people_card_h($label) . "</span>";
                                        } ?>
                                    </div>
                                <?php } ?>
                            </div>

                            <?php if (!empty($person["company_name"]) || !empty($person["company_linkedin_url"])) { ?>
                                <div class="people-card-panel">
                                    <h2>Company</h2>
                                    <dl class="people-card-facts">
                                        <?php if (!empty($person["company_name"])) { ?>
                                            <div><dt>Name</dt><dd><?php echo people_card_h($person["company_name"]); ?></dd></div>
                                        <?php } ?>
                                        <?php if (!empty($person["company_linkedin_url"])) { ?>
                                            <div><dt>LinkedIn</dt><dd><a target="_blank" href="<?php echo people_card_h($person["company_linkedin_url"]); ?>">Open company</a></dd></div>
                                        <?php } ?>
                                    </dl>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="col-xl-8">
                            <div class="people-card-panel">
                                <div class="people-card-section-head">
                                    <h2>Ongoing Tasks</h2>
                                    <button class="btn btn-primary btn-sm" type="button" id="peopleToggleTask">Add task</button>
                                </div>
                                <div class="people-task-form" id="peopleTaskForm" hidden>
                                    <textarea id="peopleTaskBody" class="form-control" placeholder="Enter new task"></textarea>
                                    <input id="peopleTaskDeadline" class="form-control" type="datetime-local" value="<?php echo date("Y-m-d\T00:00", strtotime("+10 days")); ?>">
                                    <input id="peopleTaskEditId" type="hidden" value="">
                                    <div class="people-task-form-actions">
                                        <button class="btn btn-success btn-sm" type="button" id="peopleAddTask">Save task</button>
                                        <button class="btn btn-light btn-sm" type="button" id="peopleCancelTaskEdit" hidden>Cancel</button>
                                    </div>
                                </div>
                                <div id="peopleTaskList" class="people-task-list">
                                    <?php if (!$tasks) { ?>
                                        <div class="people-empty">No active tasks yet.</div>
                                    <?php } ?>
                                    <?php foreach ($tasks as $task) {
                                        $completed = !empty($task["completed_by"]) || !empty($task["completed_time"]);
                                        $taskBody = (string)($task["body"] ?? "Task");
                                        $taskDeadline = (string)($task["deadline"] ?? "");
                                        $taskDeadlineInput = $taskDeadline !== "" ? date("Y-m-d\TH:i", strtotime($taskDeadline)) : "";
                                        ?>
                                        <div class="people-task<?php echo $completed ? " is-complete" : ""; ?>" data-task-id="<?php echo people_card_h($task["id"] ?? ""); ?>" data-task-body="<?php echo people_card_h($taskBody); ?>" data-task-deadline="<?php echo people_card_h($taskDeadlineInput); ?>">
                                            <div>
                                                <strong><?php echo people_card_h($taskBody); ?></strong>
                                                <span><?php echo $taskDeadline !== "" ? "Deadline: " . people_card_h(date("Y-m-d H:i", strtotime($taskDeadline))) : "No deadline"; ?></span>
                                            </div>
                                            <div class="people-task-actions">
                                                <button class="btn btn-light btn-sm people-edit-task" type="button">Edit</button>
                                                <button class="btn btn-light btn-sm people-complete-task" type="button"><?php echo $completed ? "Reopen" : "Complete"; ?></button>
                                                <button class="btn btn-light btn-sm people-delete-task" type="button" aria-label="Delete task" title="Delete task"><i class="icon-trash"></i></button>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="people-card-panel">
                                <h2>Notes</h2>
                                <form id="peopleNoteForm" class="people-note-form">
                                    <input type="hidden" name="id" value="<?php echo (int)$leadId; ?>">
                                    <textarea name="note" class="form-control" placeholder="Save your internal notes about <?php echo people_card_h($name); ?>"></textarea>
                                    <button class="btn btn-success" type="submit">Save note</button>
                                </form>
                                <div id="peopleNotes" class="people-notes">
                                    <?php if (!$notes) { ?>
                                        <div class="people-empty">No notes yet.</div>
                                    <?php } ?>
                                    <?php foreach ($notes as $note) { ?>
                                        <article class="people-note">
                                            <div class="people-note-meta"><?php echo people_card_h($note["created"] ?? ""); ?></div>
                                            <div><?php echo $note["note"] ?? ""; ?></div>
                                        </article>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php include("partial/footer.php"); ?>
        </div>
    </div>
</div>
<?php include("partial/scripts.php"); ?>
<script>
    (function() {
        const leadId = <?php echo json_encode((string)$leadId); ?>;
        const token = document.body.getAttribute('session_id');
        const taskForm = document.getElementById('peopleTaskForm');
        const taskBody = document.getElementById('peopleTaskBody');
        const taskDeadline = document.getElementById('peopleTaskDeadline');
        const taskEditId = document.getElementById('peopleTaskEditId');
        const taskSaveButton = document.getElementById('peopleAddTask');
        const taskCancelEditButton = document.getElementById('peopleCancelTaskEdit');
        const stageSelect = document.getElementById('peopleStageSelect');
        const ownerSelect = document.getElementById('peopleOwnerSelect');
        const saveStatusButton = document.getElementById('peopleSaveStatus');
        const saveStatusState = document.getElementById('peopleStatusSaveState');
        const currentStageLabel = document.getElementById('peopleCurrentStageLabel');
        const currentOwnerLabel = document.getElementById('peopleCurrentOwnerLabel');

        function peopleSetStatusMessage(type, text) {
            if (!saveStatusState) return;
            saveStatusState.className = 'people-save-status' + (type ? ' is-' + type : '');
            saveStatusState.textContent = text || '';
        }

        async function peopleFetchJson(url, options) {
            const response = await fetch(url, options);
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        }

        function peopleDefaultTaskDeadline() {
            const date = new Date();
            date.setDate(date.getDate() + 10);
            date.setHours(0, 0, 0, 0);
            const offsetDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
            return offsetDate.toISOString().slice(0, 16);
        }

        function peopleResetTaskForm() {
            if (taskEditId) taskEditId.value = '';
            if (taskBody) taskBody.value = '';
            if (taskDeadline) taskDeadline.value = peopleDefaultTaskDeadline();
            if (taskSaveButton) taskSaveButton.textContent = 'Save task';
            if (taskCancelEditButton) taskCancelEditButton.hidden = true;
        }

        function peopleStartTaskEdit(task) {
            if (!task || !taskForm || !taskBody || !taskDeadline || !taskEditId) return;
            taskEditId.value = task.getAttribute('data-task-id') || '';
            taskBody.value = task.getAttribute('data-task-body') || '';
            taskDeadline.value = task.getAttribute('data-task-deadline') || '';
            if (taskSaveButton) taskSaveButton.textContent = 'Update task';
            if (taskCancelEditButton) taskCancelEditButton.hidden = false;
            taskForm.hidden = false;
            taskBody.focus();
        }

        saveStatusButton?.addEventListener('click', async function() {
            const stageId = stageSelect ? stageSelect.value : '';
            const ownerId = ownerSelect ? ownerSelect.value : '0';
            if (!leadId || !stageId) return;

            saveStatusButton.disabled = true;
            peopleSetStatusMessage('', 'Saving...');

            try {
                await peopleFetchJson('https://api.coherta.com/pipeline/leads/stage/' + encodeURIComponent(stageId), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token},
                    body: JSON.stringify({id: leadId, pipeline_company_id: leadId})
                });

                await peopleFetchJson('https://api.coherta.com/pipeline/leads/owner', {
                    method: 'PATCH',
                    headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token},
                    body: JSON.stringify({id: leadId, pipeline_company_id: leadId, owner_id: ownerId})
                });

                if (currentStageLabel && stageSelect) {
                    currentStageLabel.textContent = stageSelect.options[stageSelect.selectedIndex]?.text || '';
                }
                if (currentOwnerLabel && ownerSelect) {
                    currentOwnerLabel.textContent = ownerSelect.options[ownerSelect.selectedIndex]?.text || 'Unassigned';
                }
                peopleSetStatusMessage('success', 'Saved');
            } catch (error) {
                peopleSetStatusMessage('error', 'Could not save');
            } finally {
                saveStatusButton.disabled = false;
            }
        });

        document.getElementById('peopleToggleTask')?.addEventListener('click', function() {
            peopleResetTaskForm();
            taskForm.hidden = !taskForm.hidden;
            if (!taskForm.hidden) taskBody.focus();
        });

        taskCancelEditButton?.addEventListener('click', function() {
            peopleResetTaskForm();
            taskForm.hidden = true;
        });

        taskSaveButton?.addEventListener('click', function() {
            const body = taskBody.value.trim();
            if (!body) return;
            const tid = taskEditId ? taskEditId.value : '';
            const isEditing = tid !== '';
            fetch('https://api.coherta.com/pipeline/leads/todo/' + encodeURIComponent(leadId), {
                method: isEditing ? 'PATCH' : 'POST',
                headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token},
                body: JSON.stringify(isEditing
                    ? {id: leadId, tid: tid, body: body, deadline: taskDeadline.value}
                    : {id: leadId, body: body, deadline: taskDeadline.value}
                )
            }).then(function() { window.location.reload(); });
        });

        document.querySelectorAll('.people-edit-task').forEach(function(button) {
            button.addEventListener('click', function() {
                peopleStartTaskEdit(button.closest('.people-task'));
            });
        });

        document.querySelectorAll('.people-complete-task').forEach(function(button) {
            button.addEventListener('click', function() {
                const task = button.closest('.people-task');
                fetch('https://api.coherta.com/pipeline/leads/todo/' + encodeURIComponent(leadId), {
                    method: 'PATCH',
                    headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token},
                    body: JSON.stringify({
                        id: leadId,
                        tid: task.getAttribute('data-task-id'),
                        completed: !task.classList.contains('is-complete')
                    })
                }).then(function() { window.location.reload(); });
            });
        });

        document.querySelectorAll('.people-delete-task').forEach(function(button) {
            button.addEventListener('click', function() {
                const task = button.closest('.people-task');
                if (!task) return;
                fetch('https://api.coherta.com/pipeline/leads/todo/' + encodeURIComponent(leadId), {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token},
                    body: JSON.stringify({
                        id: leadId,
                        tid: task.getAttribute('data-task-id'),
                        body: task.getAttribute('data-task-body') || ''
                    })
                }).then(function() { window.location.reload(); });
            });
        });

        document.getElementById('peopleNoteForm')?.addEventListener('submit', function(event) {
            event.preventDefault();
            const textarea = event.currentTarget.querySelector('textarea[name="note"]');
            const note = textarea.value.trim();
            if (!note) return;
            fetch('https://api.coherta.com/pipeline/leads/notes', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token},
                body: JSON.stringify({id: leadId, note: note})
            }).then(function() { window.location.reload(); });
        });
    })();
</script>
<style>
    .people-card-page { padding-top: 24px; }
    .people-card-hero, .people-card-panel { background: #fff; border: 1px solid rgba(15,23,42,.08); border-radius: 18px; box-shadow: 0 14px 40px rgba(15,23,42,.06); }
    .people-card-hero { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 22px; margin-bottom: 24px; }
    .people-card-identity { display: flex; align-items: center; gap: 16px; min-width: 0; }
    .people-card-identity img, .people-card-identity > span { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; background: #eef2ff; color: #3730a3; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; }
    .people-card-identity h1 { margin: 0; color: #111827; font-size: 30px; line-height: 1.2; }
    .people-card-identity p { margin: 4px 0 0; color: #667085; }
    .people-card-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .people-card-panel { padding: 20px; margin-bottom: 24px; }
    .people-card-panel h2 { margin: 0 0 16px; font-size: 18px; color: #111827; }
    .people-card-section-head { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 14px; }
    .people-current-state { display: grid; gap: 8px; padding: 12px; margin-bottom: 14px; border-radius: 14px; background: #f8fafc; color: #667085; }
    .people-current-state strong { color: #111827; }
    .people-status-form { display: grid; gap: 9px; }
    .people-status-form label { margin: 0; color: #667085; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .people-save-status { color: #667085; font-size: 12px; font-weight: 700; min-height: 18px; }
    .people-save-status.is-success { color: #15803d; }
    .people-save-status.is-error { color: #b91c1c; }
    .people-card-facts { display: grid; gap: 12px; margin: 0; }
    .people-card-facts div { display: grid; grid-template-columns: minmax(90px, 130px) minmax(0, 1fr); gap: 10px; padding-bottom: 12px; border-bottom: 1px solid rgba(15,23,42,.06); }
    .people-card-facts dt { color: #667085; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .people-card-facts dd { margin: 0; color: #111827; overflow-wrap: anywhere; }
    .people-card-skills { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 16px; }
    .people-card-skills span { padding: 6px 10px; border-radius: 999px; background: #eef2ff; color: #3730a3; font-size: 12px; font-weight: 700; }
    .people-task-form, .people-note-form { display: grid; gap: 10px; margin-bottom: 16px; }
    .people-task-form-actions, .people-task-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .people-delete-task { width: 34px; min-width: 34px; padding-left: 0; padding-right: 0; color: #b91c1c; }
    .people-delete-task:hover { color: #991b1b; background: #fee2e2; border-color: #fecaca; }
    .people-task-list, .people-notes { display: grid; gap: 10px; }
    .people-task { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px; border: 1px solid rgba(15,23,42,.08); border-radius: 14px; background: #f8fafc; }
    .people-task strong, .people-task span { display: block; }
    .people-task span { margin-top: 3px; color: #667085; font-size: 12px; }
    .people-task.is-complete { opacity: .68; }
    .people-task.is-complete strong { text-decoration: line-through; }
    .people-note { padding: 14px; border: 1px solid rgba(15,23,42,.08); border-radius: 14px; background: #fff; }
    .people-note-meta { color: #667085; font-size: 12px; font-weight: 700; margin-bottom: 8px; }
    .people-empty { padding: 14px; border-radius: 14px; background: #f8fafc; color: #667085; }
    @media (max-width: 768px) {
        .people-card-hero { align-items: flex-start; flex-direction: column; }
        .people-card-actions, .people-card-actions .btn { width: 100%; }
        .people-card-actions .btn { justify-content: center; }
        .people-card-facts div { grid-template-columns: 1fr; gap: 4px; }
        .people-task { align-items: flex-start; flex-direction: column; }
        .people-task-actions { width: 100%; }
        .people-task-actions .btn { flex: 1 1 120px; }
        .people-task-actions .people-delete-task { flex: 0 0 34px; }
    }
</style>
<?php include("partial/footer-end.php"); ?>
