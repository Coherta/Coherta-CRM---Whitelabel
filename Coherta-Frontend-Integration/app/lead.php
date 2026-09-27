<?php
require_once "./base.php";
$employee_data  = $client->get("pipeline/leads/employees/".$_GET['id']);
$financial_data  = $client->get("pipeline/leads/financials?id=".$_GET['id']);
if($financial_data == null) {
    $financial_data  = $client->get("pipeline/leads/financials?id=".$_GET['id']);
}

$lead_data  = $client->get("pipeline/leads/details/".$_GET['id']);

$owner      = $lead_data["pipeline"][0]['owner'] ?? 0;
$name_entry = $utils->active_record($lead_data['name']);
$email      = $utils->active_record($lead_data['email']);
$phone      = $utils->active_record($lead_data['phone']);
$address    = $utils->active_record($lead_data['address']);
$website    = $utils->active_record($lead_data['website']);
$root       = $utils->active_record($lead_data['root']);
$pipeline   = $utils->active_record($lead_data['pipeline']);
$google_maps= $utils->active_record($lead_data['google_maps']);
$users      = $client->get("company/users");

$win_deal_topbar   = true;
$PageTitle = $name_entry["name"];
$stylesheets[] = "assets/css/vendors/todo.css";
if(is_null($lead_data)) {
    header("Location: /app/");
    exit;
}
include('partial/header.php');

?>
<?php include('partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <?php include('partial/topbar.php'); ?>
    <!-- Page Header Ends -->
    <!-- Page Body Start-->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <?php include('partial/sidebar.php');?>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
            <?php include('partial/breadcrumb.php'); ?>
            <!-- Container-fluid starts-->
            <div class="container-fluid">
                <div class="user-profile social-app-profile">
                    <div class="row">
                        <!-- user profile first-style start-->
                        <div class="col-sm-12 box-col-12">
                            <div class="card hovercard text-center">
                                <div class="info market-tabs p-0">
                                    <ul class="nav nav-tabs border-tab tabs-scoial" id="top-tab" role="tablist">
                                        <li class="nav-item"><a class="nav-link active" id="top-timeline"
                                                                data-bs-toggle="tab" href="#timeline" role="tab"
                                                                aria-controls="timeline" aria-selected="true">Timeline</a></li>
                                        <li class="nav-item"><a class="nav-link" id="top-about" data-bs-toggle="tab"
                                                                href="#about" role="tab" aria-controls="about"
                                                                aria-selected="false">About</a></li>
                                        <li class="nav-item">
                                            <div class="user-designation"></div>
                                            <div class="title"><?php echo $name_entry["name"]; ?></div>
                                            <div class="desc mt-2"><?php echo $address["address"].", ".$address["postal"].", ".$address["city"]; ?></div>
                                        </li>
                                        <li class="nav-item"><a class="nav-link" id="top-friends" data-bs-toggle="tab"
                                                                href="#friends" role="tab" aria-controls="friends"
                                                                aria-selected="false">LinkedIn Insights</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" id="top-tabContent">
                        <div class="col-md-12 project-list">
                            <div class="card">
                                <div class="row">
                                    <div class="col-md-12">
                                            <?php
                                            if($lead_data["pipeline"][0]["status"] == 0) {
                                                echo '<ul class="nav nav-tabs border-tab tablist-full-with" id="top-tab" role="tablist">';
                                                foreach($stages as $id => $stage) {
                                                    $isActive = ($id == $pipeline['stage']);
                                                    echo '<li class="nav-item"><a data-stage-id="'.$id.'" class="nav-link ' . ($isActive ? ' active' : '') . '" id="top-home-tab" data-bs-toggle="tab" href="#top-home" role="tab" aria-controls="top-home" aria-selected="true"><i data-feather="'.$stage["icon"].'"></i>'.$stage["title"].'</a></li>';
                                                }
                                                echo "</ul>";
                                            }
                                            if ($lead_data["pipeline"][0]["status"] == 1) {
                                                echo '<div class="alert alert-success">This deal is marked as won.</div>';
                                            } elseif ($lead_data["pipeline"][0]["status"] == 2) {
                                                echo '<div class="alert alert-danger">This deal is marked as lost with the following reason: ' . htmlspecialchars($lead_data["pipeline"][0]["status_reason"]) . '.</div>';
                                            }
                                            ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade show active" id="timeline" role="tabpanel" aria-labelledby="timeline">
                            <div class="row">
                                <div class="col-xl-3 xl-40 col-lg-12 col-md-5 box-col-4">
                                    <div class="default-according style-1 faq-accordion" id="accordionExample">
                                        <div class="card">
                                            <div class="card-header" id="headingOwner">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-start"
                                                            type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#collapseOwner" aria-expanded="true"
                                                            aria-controls="collapseOwner">Lead Owner</button>
                                                </h2>
                                            </div>
                                            <div class="collapse show" id="collapseOwner" aria-labelledby="headingOwner"
                                                 data-bs-parent="#accordionExampleOwner">
                                                 <select id="LeadOwner" style="margin:5%;width:90%;height:40px;">
                                                     <option value="0">--- Choose Owner ---</option>
                                                     <?php
                                                     foreach($users as $user) {
                                                         if($user['disabled'] == 1) continue;
                                                         $selected = ($user["id"] == $owner) ? "selected" : "";
                                                         echo '<option value="'.$user["id"].'" '.$selected.'>'.$user["full_name"].'</option>';
                                                     }
                                                     ?>
                                                 </select>
                                            </div>
                                            <?php
                                            if(isset($lead_data["social_media"]) && count($lead_data["social_media"]) > 0) {
                                                echo '<div class="card-header" id="headingOne">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-start"
                                                            type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#collapseSocial" aria-expanded="true"
                                                            aria-controls="collapseSocial">Social Media</button>
                                                </h2>
                                                </div><div class="collapse show" id="collapseSocial" aria-labelledby="headingSocial"
                                                 data-bs-parent="#accordionExampleSocial">
                                                <div class="card-body socialprofile filter-cards-view">
                                                ';
                                                echo '<div class="media">';
                                                foreach($lead_data["social_media"]["linkedIn"] as $social) {
                                                    if(isset($social)) {
                                                        echo '<a target="_BLANK" href="'.$social["link"].'"><img
                                                                class="img-50 img-fluid m-r-20 rounded-circle"
                                                                src="assets/images/email-template/linkedin.png" alt="" width="40" height="40">
                                                                </a>
                                                ';
                                                    }
                                                }
                                                echo '</div>';
                                                echo '                                                    </div>
                                                </div>
                                                ';
                                            }
                                            ?>
                                            <div class="card-header" id="headingOne">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-start"
                                                            type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#collapseOne" aria-expanded="true"
                                                            aria-controls="collapseOne">Company Profile</button>
                                                </h2>
                                            </div>
                                            <div class="collapse show" id="collapseOne" aria-labelledby="headingOne"
                                                 data-bs-parent="#accordionExample">
                                                <div class="card-body socialprofile filter-cards-view">
                                                    <div class="media"><img
                                                                class="img-50 img-fluid m-r-20 rounded-circle"
                                                                src="assets/images/user/user.png" alt="" width="48" height="48">
                                                        <div class="media-body">
                                                            <span class="d-block">
                                                                <span class="px-2">Pipeline</span>
                                                                <span class="px-2 pull-right pipeline_title">
                                                                        <?php echo $pipeline["pipeline_title"]; ?>
                                                                <a href="#" id="move-lead-btn" title="Move lead to another pipeline">
                                                                    <i data-feather="corner-up-right" style="width:15px;height:15px;"></i>
                                                                </a>
                                                                </span>
                                                            </span><br />
                                                            <span class="d-block">
                                                                <span class="px-2">VAT</span>
                                                                <span class="px-2 pull-right" ondblclick="copyToClipboard(this)">
                                                                        <?php echo $root["country"].$root["company_id"]; ?>
                                                                </span>
                                                            </span>
                                                            <span class="d-block">
                                                                <span class="px-2">Address</span>
                                                                <span class="px-2 pull-right" ondblclick="copyToClipboard(this)">
                                                                        <?php echo $address["address"]; ?>
                                                                </span>
                                                            </span>
                                                            <span class="d-block">
                                                                <span class="px-2">Postal</span>
                                                                <span class="px-2 pull-right" ondblclick="copyToClipboard(this)">
                                                                        <?php echo $address["postal"]; ?>
                                                                </span>
                                                            </span>
                                                            <span class="d-block">
                                                                <span class="px-2">City</span>
                                                                <span class="px-2 pull-right" ondblclick="copyToClipboard(this)">
                                                                        <?php echo $address["city"]; ?>
                                                                </span>
                                                            </span>
                                                            <?php
                                                            if($google_maps) {
                                                                echo '<span class="d-block">
                                                                <span class="px-2">Maps Name</span>
                                                                <span class="px-2 pull-right pipeline_title">
                                                                '.$lead_data['google_maps']['displayName'].'
                                                                </span>
                                                                    </span>
                                                                    <span class="d-block">
                                                                        <span class="px-2">Maps Phone</span>
                                                                        <span class="px-2 pull-right pipeline_title">
                                                                        '.$lead_data['google_maps']['phone'].'
                                                                        </span>
                                                                    </span>
                                                                    <span class="d-block">
                                                                        <span class="px-2">Maps Currency</span>
                                                                        <span class="px-2 pull-right pipeline_title">
                                                                        '.$lead_data['google_maps']['currency'].'
                                                                        </span>
                                                                    </span>
                                                                    <span class="d-block">
                                                                        <span class="px-2">Maps Price Range</span>
                                                                        <span class="px-2 pull-right pipeline_title">
                                                                        '.$lead_data['google_maps']['startPrice_units'].'-'.$lead_data['google_maps']['endPrice_units'].'
                                                                        </span>
                                                                    </span>
                                                                    <span class="d-block">
                                                                        <span class="px-2">Maps Rating</span>
                                                                        <span class="px-2 pull-right pipeline_title">
                                                                        '.$lead_data['google_maps']['rating'].'
                                                                        </span>
                                                                    </span>
                                                                    <span class="d-block">
                                                                        <span class="px-2">Maps Rating Count</span>
                                                                        <span class="px-2 pull-right pipeline_title">
                                                                        '.$lead_data['google_maps']['rating_count'].'
                                                                        </span>
                                                                    </span>';
                                                            }
                                                            ?>
                                                            <span class="d-block">&nbsp;
                                                            </span>
                                                            <?php if(isset($phone["phone"])) {
                                                                // Original value
                                                                $input = trim($phone["phone"]);

                                                                // Detect if it starts with +45
                                                                $hasCountryCode = strpos($input, '+45') === 0;

                                                                if ($hasCountryCode) {
                                                                    $countryCode = '+45';
                                                                    // Remove everything that's not a digit after +45
                                                                    $number = preg_replace('/\D+/', '', substr($input, 3));
                                                                } else {
                                                                    $countryCode = '';
                                                                    // Remove everything that's not a digit
                                                                    $number = preg_replace('/\D+/', '', $input);
                                                                }

                                                                // Add a space every 2 digits for the national part
                                                                $spacedNumber = trim(chunk_split($number, 2, ' '));

                                                                // Build display string: "+45 20 64 52 35" or "20 64 52 35"
                                                                $displayPhone = trim($countryCode . ' ' . $spacedNumber);

                                                                // tel: link should be clean: +4520645235 or 20645235
                                                                $telPhone = $countryCode . $number;
                                                                ?>
                                                            <span class="d-block">
                                                                <span class="px-2">Phone</span>
                                                                <span class="pull-right" ondblclick="copyToClipboard(this)">
                                                                    <a class="px-2 " href="tel:<?php echo $telPhone; ?>"><?php echo $displayPhone; ?></a>
                                                                </span>
                                                            </span>
                                                            <?php } ?>
                                                            <?php if(isset($email["email"])) { ?>
                                                            <span class="d-block">
                                                                <span class="px-2">Email</span>
                                                                <span class="pull-right">
                                                                    <a class="px-2" href="mailto:<?php echo $email["email"]; ?>"><?php echo $email["email"]; ?></a>
                                                                </span>
                                                            </span>
                                                            <?php } ?>
                                                            <?php if(isset($website["website"])) { ?>
                                                            <span class="d-block">
                                                                <span class="px-2">Website</span>
                                                                <span class="pull-right">
                                                                    <?php
                                                                    $site = $website["website"];
                                                                    if (strpos($site, 'http://') !== 0 && strpos($site, 'https://') !== 0) {
                                                                        $site = 'https://' . $site;
                                                                    }
                                                                    ?>
                                                                    <a class="px-2" target="_BLANK" href="<?php echo $site; ?>"><?php echo $website["website"]; ?></a>
                                                                </span>
                                                            </span>
                                                            <?php } ?>
                                                            <?php if(isset($lead_data["alternative_website"])) { ?>
                                                                <span class="d-block">
                                                                <span class="px-2">Alternative Website</span>
                                                                <span class="pull-right">
                                                                    <?php
                                                                    $altWebsite = $lead_data["alternative_website"];
                                                                    if (strpos($altWebsite, 'http://') !== 0 && strpos($altWebsite, 'https://') !== 0) {
                                                                        $altWebsite = 'https://' . $altWebsite;
                                                                    }
                                                                    ?>
                                                                    <a class="px-2" target="_BLANK" href="<?php echo $altWebsite; ?>"><?php echo $lead_data["alternative_website"]; ?></a>
                                                                </span>
                                                            </span>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header" id="headingTwo">
                                                <h2 class="mb-0">
                                                    <button
                                                            class="btn btn-link btn-block text-start collapsed"
                                                            type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#collapseTwo" aria-expanded="true"
                                                            aria-controls="collapseTwo">Company Relations</button>
                                                </h2>
                                            </div>
                                            <div class="collapse show" id="collapseTwo" aria-labelledby="headingTwo"
                                                 data-bs-parent="#accordionExample">
                                                <div class="card-body social-status filter-cards-view">
                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRelationModal" onClick="emptyModal()">Add New Relation</button>
                                                    <br />&nbsp;<br />
                                                        <?php
                                                        $grouped = [];
                                                        foreach ($lead_data['participants'] as $participant) {
                                                            if($participant["until"] != NULL)
                                                                continue;
                                                            $name = $participant['name'];
                                                            if (!isset($grouped[$name])) {
                                                                $grouped[$name] = [];
                                                            }
                                                            if(!isset($participant['phone1']))
                                                                $participant['phone1'] = null;
                                                            if(!isset($participant['phone2']))
                                                                $participant['phone2'] = null;
                                                            if(!isset($participant['email']))
                                                                $participant['email'] = null;
                                                            $grouped[$name]['details'] = $participant;
                                                            $grouped[$name]['types'][] = $participant['participant_type'];
                                                        }
                                                        foreach ($grouped as $name => $types) {
                                                            $img = "assets/images/user/user.png";
                                                            if(!is_null($types['details']["image"])) {
                                                                $img = "https://api.coherta.com/images/".$types['details']["image"];
                                                            }
                                                            $typesLine = implode(', ', $types['types']);

                                                            echo '<div class="media">
                                                            <img class="img-50 rounded-circle m-r-15" src="'.$img.'" alt="">
                                                            <div class="media-body">
                                                                <span class="f-w-600 d-block" ondblclick="copyToClipboard(this)">'.htmlspecialchars($name).'</span>
                                                                <span class="d-block">'.htmlspecialchars($typesLine).'</span>
                                                                ';

                                                                $hasPhone = !empty($types["details"]['phone1']);
                                                                $hasEmail = !empty($types["details"]['email']);
                                                                $hasLinkedIn = !empty($types["details"]['linkedin_url']);
                                                            echo '<span class="d-block">';
                                                            if($hasLinkedIn) {
                                                                echo ' <a href="'.htmlspecialchars($types["details"]['linkedin_url']).'" target="_blank" title="LinkedIn Profile"><i data-feather="linkedin" style="width:16px;height:16px;"></i></a> ';
                                                            }
                                                                if ($hasPhone || $hasEmail) {
                                                                    if ($hasEmail) {
                                                                        echo '<a href="mailto:' . htmlspecialchars($types["details"]['email']) . '" title="Email"><i data-feather="mail" style="width:16px;height:16px;"></i></a><br />';
                                                                    }
                                                                    if ($hasPhone) {
                                                                        echo '<a href="tel:' . htmlspecialchars($types["details"]['phone1']) . '" title="Call">'.htmlspecialchars($types["details"]['phone1']).'</a>';
                                                                    }
                                                                }
                                                            echo '</span>';
                                                                echo '
                                                            </div>
                                                            ';
                                                            echo '<a href="#" class="m-r-15 btn btn-success edit-relation-btn"
                                                                        data-id="'.htmlspecialchars($types["details"]['id'] ?? '').'"
                                                                        data-l_id="'.htmlspecialchars($types["details"]['l_id'] ?? '').'"
                                                                        data-name="'.htmlspecialchars($name).'"
                                                                        data-phone1="'.htmlspecialchars($types["details"]['phone1'] ?? '').'"
                                                                        data-phone2="'.htmlspecialchars($types["details"]['phone2'] ?? '').'"
                                                                        data-email="'.htmlspecialchars($types["details"]['email'] ?? '').'"
                                                                        data-linkedin="'.htmlspecialchars($types["details"]['linkedin_url'] ?? '').'">
                                                                        Edit Contact
                                                                </a>';
                                                                echo '
                                                          </div>';
                                                                echo '<div class="media">';
                                                            if(isset($company_data["plugins"][4]["installed"]) &&
                                                                    $company_data["plugins"][4]["installed"]) {
                                                                echo '
                                                                    <button
                                                                        data-company-uid="'.$root["uid"].'"
                                                                        data-company-vat="'.$root["country"].$root["company_id"].'"
                                                                        data-id="'.htmlspecialchars($types["details"]['id'] ?? '').'"
                                                                        data-l_id="'.htmlspecialchars($types["details"]['l_id'] ?? '').'"
                                                                        data-name="'.htmlspecialchars($name).'"
                                                                        data-phone1="'.htmlspecialchars($types["details"]['phone1'] ?? '').'"
                                                                        data-phone2="'.htmlspecialchars($types["details"]['phone2'] ?? '').'"
                                                                        data-email="'.htmlspecialchars($types["details"]['email'] ?? '').'"
                                                                        data-linkedin="'.htmlspecialchars($types["details"]['linkedin_url'] ?? '').'"
                                                                        class="btn btn-success plugin4">'.$company_data["plugins"][4]["settings"]["user"][1]["value"].'</button>';
                                                            }
                                                                echo "</div>";
                                                        }
                                                        ?>
                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRelationModal" onClick="emptyModal()">Add New Relation</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-9 xl-60 col-lg-12 col-md-7 box-col-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="mb-0">To-Do</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="todo">
                                                <div class="todo-list-wrapper">
                                                    <div class="todo-list-container">
                                                        <div class="mark-all-tasks">
                                                            <div class="mark-all-tasks-container"><span class="mark-all-btn" id="mark-all-finished" role="button"><span class="btn-label">Mark all as finished</span><span class="action-box completed"><i class="icon"><i class="icon-check"></i></i></span></span><span class="mark-all-btn move-down" id="mark-all-incomplete" role="button"><span class="btn-label">Mark all as Incomplete</span><span class="action-box"><i class="icon"><i class="icon-check"></i></i></span></span></div>
                                                            <div class="todo-list-footer ms-2">
                                                                <div class="add-task-btn-wrapper"><span class="add-task-btn">
                                                            <button class="btn btn-primary"><i class="icon-plus"></i> Add new task</button></span></div>
                                                            </div>
                                                        </div>
                                                        <div class="todo-list-body">
                                                            <div class="todo-list-footer">
                                                                <div class="new-task-wrapper mb-4">
                                                                    <textarea id="new-task" placeholder="Enter new task here. . ."></textarea>
                                                                    <input type="datetime-local" id="new-task-deadline" class="form-control mb-2" value="<?php echo date('Y-m-d\T00:00', strtotime('+10 days')); ?>">
                                                                    <span class="btn btn-danger cancel-btn" id="close-task-panel">Close</span><span class="btn btn-success ms-3 add-new-task-btn" id="add-task">Add Task</span>
                                                                </div>
                                                            </div>
                                                            <ul id="todo-list">
                                                                <ul id="todo-list">
                                                                    <?php
                                                                    $todo_items = $client->get("pipeline/leads/todo/" . $_GET['id']);
                                                                    if (!empty($todo_items["todos"])) {
                                                                        foreach ($todo_items["todos"] as $item) {
                                                                            $statusBadge = 'badge-light-danger';
                                                                            $statusText = 'Pending';
                                                                            if ($item['completed_by'] != null) {
                                                                                $statusBadge = 'badge-light-success';
                                                                                $statusText = 'Done';
                                                                            } elseif ($item['completed_by'] == null) {
                                                                                $statusBadge = 'badge-light-primary';
                                                                                $statusText = 'In Progress';
                                                                            }
                                                                            echo '<li data-id="' . $item["id"] . '" class="task' . ($item['completed_by'] !== null ? ' completed' : '') . '">
                <div class="task-container">
                    <div class="col-sm-1">
                        <img class="img-fluid img-40 rounded-circle" src="' . $client->getColleagueImage($item['creator']) . '" alt="' . htmlspecialchars($client->getColleagueName($item['creator'])) . '">
                    </div>
                    <h4 class="col-sm-4 task-label gap-4" style="padding-right: 10px;">
                                        ' . htmlspecialchars($item['body']) . '
                    </h4>
                    <div class="col-sm-7 d-flex align-items-center">
                        <span class="col-sm-4 badge ' . $statusBadge . '">' . $statusText . '</span>
                        <h5 class="col-sm-4 assign-name m-0">
                            ';
                                                                            echo !empty($item['created_time'])
                                                                                    ? 'Created: &nbsp; ' . date("Y-m-d H:i", strtotime($item['created_time'])) . '<br />'
                                                                                    : '';
                                                                            echo !empty($item['deadline'])
                                                                                    ? 'Deadline: ' . date("Y-m-d H:i", strtotime($item['deadline']))
                                                                                    : '';
                                                                            echo '
                        </h5>
                        <span class="col-sm-4 task-action-btn text-right">
                            <span class="action-box large lead delete-btn" title="Delete Task"><i class="icon"><i class="icon-trash"></i></i></span>
                            <span class="action-box large lead complete-btn" title="Mark Complete"><i class="icon"><i class="icon-check"></i></i></span>
                        </span>
                    </div>
                </div>
            </li>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </ul>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="notification-popup hide">
                                                    <p><span class="task"></span><span class="notification-text"></span></p>
                                                </div>
                                            </div>
                                            <!-- HTML Template for tasks-->
                                            <script id="task-template" type="tect/template">
                                                <li class="task">
                                                    <div class="task-container">
                                                        <h4 class="task-label"></h4>
                                                        <div class="d-flex align-items-center gap-4">
                                                            <span class="badge badge-light-primary">In Progress</span>
                                                            <h5 class="assign-name taskDate"></h5>
                                                            <span class="task-action-btn">
                              <span class="action-box large delete-btn" title="Delete Task">
                                <i class="icon"><i class="icon-trash"></i></i>
                              </span>
                              <span class="action-box large complete-btn" title="Mark Complete">
                                <i class="icon"><i class="icon-check"></i></i>
                              </span>
                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            </script>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <form class="was-validated" method="POST" action="lead.php">
                                                <input type="hidden" name="id" value="<?php echo $_GET["id"]; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label" for="validationTextarea">Notes</label>
                                                    <textarea name="note" class="form-control" id="validationTextarea" placeholder="Save your internal notes about <?php echo $name_entry["name"] ?> "></textarea>
                                                    <button class="btn btn-success active" type="button" title="">Save note</button>
                                                </div>
                                            </form>
                                        </div>
                                        <div id="notes-container"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="about" role="tabpanel" aria-labelledby="about">
                            <div class="row">
                                <div class="col-xl-3 xl-40 col-lg-12 col-md-5 box-col-4">
                                    <div class="default-according style-1 faq-accordion" id="accordionExample1">
                                        <div class="card">
                                            <div class="card">
                                                <div class="card-header" id="headingOne">
                                                    <h2 class="mb-0">
                                                        <button class="btn btn-link btn-block text-start"
                                                                type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapseOne" aria-expanded="true"
                                                                aria-controls="collapseOne">Company Profile</button>
                                                    </h2>
                                                </div>
                                                <div class="collapse show" id="collapseOne" aria-labelledby="headingOne"
                                                     data-bs-parent="#accordionExample">
                                                    <div class="card-body socialprofile filter-cards-view">
                                                        <div class="media"><img
                                                                    class="img-50 img-fluid m-r-20 rounded-circle"
                                                                    src="assets/images/user/user.png" alt="" width="48" height="48">
                                                            <div class="media-body">
                                                            <span class="d-block">
                                                                <span class="px-2">VAT</span>
                                                                <span class="px-2 pull-right">
                                                                        <?php echo $root["country"].$root["company_id"]; ?>
                                                                </span>
                                                            </span>
                                                                <span class="d-block">
                                                                <span class="px-2">Address</span>
                                                                <span class="px-2 pull-right">
                                                                        <?php echo $address["address"]; ?>
                                                                </span>
                                                            </span>
                                                                <span class="d-block">
                                                                <span class="px-2">Postal</span>
                                                                <span class="px-2 pull-right">
                                                                        <?php echo $address["postal"]; ?>
                                                                </span>
                                                            </span>
                                                                <span class="d-block">
                                                                <span class="px-2">City</span>
                                                                <span class="px-2 pull-right">
                                                                        <?php echo $address["city"]; ?>
                                                                </span>
                                                            </span>
                                                                <span class="d-block">&nbsp;
                                                            </span>
                                                                <?php if(isset($phone["phone"])) { ?>
                                                                    <span class="d-block">
                                                                <span class="px-2">Phone</span>
                                                                <span class="pull-right">
                                                                    <a class="badge px-2 text-black-50" href="tel:<?php echo $phone["phone"]; ?>"><?php echo $phone["phone"]; ?></a>
                                                                </span>
                                                            </span>
                                                                <?php } ?>
                                                                <?php if(isset($email["email"])) { ?>
                                                                    <span class="d-block">
                                                                <span class="px-2">Email</span>
                                                                <span class="pull-right">
                                                                    <a class="badge px-2 text-black-50" href="mailto:<?php echo $email["email"]; ?>"><?php echo $email["email"]; ?></a>
                                                                </span>
                                                            </span>
                                                                <?php } ?>
                                                                <?php if(isset($website["website"])) { ?>
                                                                    <span class="d-block">
                                                                <span class="px-2">Website</span>
                                                                <span class="pull-right">
                                                                    <a class="badge px-2 text-black-50" target="_BLANK" href="https://<?php echo $website["website"]; ?>"><?php echo $website["website"]; ?></a>
                                                                </span>
                                                            </span>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                <?php
                                if(count($lead_data['revision']) > 0) {
                                ?>
                                    <div class="default-according style-1 faq-accordion" id="accordionExample1">
                                        <div class="card">
                                            <div class="card">
                                                <div class="card-header" id="headingOne">
                                                    <h2 class="mb-0">
                                                        <button class="btn btn-link btn-block text-start"
                                                                type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapseOne" aria-expanded="true"
                                                                aria-controls="collapseOne">Company Accountants</button>
                                                    </h2>
                                                </div>
                                                <div class="collapse show" id="collapseOne" aria-labelledby="headingOne"
                                                     data-bs-parent="#accordionExample">
                                                    <div class="card-body socialprofile filter-cards-view">
                                                        <?php
                                                        foreach($lead_data["revision"] as $accountant) {
                                                            ?>
                                                            <div class="media">
                                                                <img
                                                                        class="img-50 img-fluid m-r-20 rounded-circle"
                                                                        src="assets/images/user/user.png" alt="" width="48" height="48">
                                                                <div class="media-body">
                                                            <p class="title"><?php echo $accountant["name"]; ?></p>
                                                            <span class="d-block">
                                                                <span class="px-2">From</span>
                                                                <span class="px-2 pull-right">
                                                                        <?php echo $accountant["from"]; ?>
                                                                </span>
                                                            </span>
                                                                    <span class="d-block">
                                                                <span class="px-2">Until</span>
                                                                <span class="px-2 pull-right">
                                                                        <?php echo $accountant["until"]; ?>
                                                                </span>
                                                            </span>
                                                            <span class="d-block">
                                                                <span class="px-2">Address</span>
                                                                <span class="px-2 pull-right">
                                                                        <?php echo $accountant["vejnavn"].", " . $accountant["postnummer"] . "," . $accountant["postdistrikt"]; ?>
                                                                </span>
                                                            </span>
                                                            </div>
                                                        </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                                </div>
                                <div class="col-xl-6 xl-60 col-lg-12 col-md-8 box-col-8">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Pepole You May Know</h5>
                                                </div>
                                                <div class="card-body avatar-showcase">
                                                    <div class="pepole-knows">
                                                        <ul>
                                                            <?php
                                                            foreach ($employee_data["participants"]["active"] as $participant) {
                                                                $img = "assets/images/user/user.png";
                                                                if(!is_null($participant["image"])) {
                                                                    $img = "https://api.coherta.com/images//".$participant["image"];
                                                                }
                                                                echo '<li>
                                                                <div class="add-friend text-center"><img
                                                                            class="img-60 img-fluid rounded-circle" 
                                                                            src="'.$img.'" alt="'.$participant['participant_type'].'"><span
                                                                            class="d-block" ondblclick="copyToClipboard(this)">'.$participant["name"].'</span>
                                                                ';
                                                                if(isset($user["plugins"]["linkedalibi"])) {
                                                                    echo '<button class="btn btn-primary btn-xs">Connect on LinkedIn</button>';
                                                                }
                                                                echo '
                                                                </div>
                                                            </li>';
                                                            }
                                                            foreach ($employee_data["participants"]["former"] as $participant) {
                                                                $img = "assets/images/user/user.png";
                                                                if(!is_null($participant["image"])) {
                                                                    $img = "https://api.coherta.com/images//".$participant["image"];
                                                                }
                                                                echo '<li style="opacity: 0.4">
                                                                <div class="add-friend text-center"><img
                                                                            class="img-60 img-fluid rounded-circle" alt=""
                                                                            src="'.$img.'"><span
                                                                            class="d-block" ondblclick="copyToClipboard(this)">'.$participant["name"].'</span>
                                                                </div>
                                                            </li>';
                                                            }
                                                            ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        if(count($lead_data["employees"]) > 0) {
                                            echo '                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-header social-header">
                                                    <h5> <span>Employee stats</span><span
                                                                class="pull-right"><i
                                                                    data-feather="more-vertical"></i></span></h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row details-about">
                                                        <div class="col-sm-12">
                                                            <div class="your-details"><span class="f-w-600">Monthly growth</span>
                                                                <div class="apache-container" id="employee-monthly-growth"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>';
                                        }
                                        ?>
                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-header social-header">
                                                    <h5> <span>Financial Results</span><span
                                                                class="pull-right"><i
                                                                    data-feather="more-vertical"></i></span></h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row details-about">
                                                        <div class="col-sm-6">
                                                            <div class="your-details"><span class="f-w-600">Gross Profit</span>
                                                                <div class="apache-container" id="gross-profit"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="your-details"><span class="f-w-600">Profit before Tax</span>
                                                                <div class="apache-container" id="profit-before-tax"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row details-about">
                                                        <div class="col-sm-6">
                                                            <div class="your-details"><span class="f-w-600">EBITDA</span>
                                                                <div class="apache-container" id="ebitda"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="your-details"><span class="f-w-600">Result for the year</span>
                                                                <div class="apache-container" id="result-of-the-year"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row details-about">
                                                        <div class="col-sm-6">
                                                            <div class="your-details"><span class="f-w-600">Liquidity Ratio</span>
                                                                <div class="apache-container" id="liquidity-ratio"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="your-details"><span class="f-w-600">Return on equity</span>
                                                                <div class="apache-container" id="return-on-equity"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 xl-100 box-col-12">
                                    <div class="default-according style-1 faq-accordion job-accordion"
                                         id="accordionoc3">
                                        <div class="row">
                                            <div class="col-xl-12 xl-50 box-col-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h2 class="mb-0">
                                                            <button class="btn btn-link btn-block text-start"
                                                                    type="button" data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseOne" aria-expanded="true"
                                                                    aria-controls="collapseOne">Company Info</button>
                                                        </h2>
                                                    </div>
                                                    <div class="collapse show" id="collapseicon7"
                                                         aria-labelledby="collapseicon" data-bs-parent="#accordion"
                                                         aria-controls="collapseicon7">
                                                        <div class="card-body filter-cards-view"><span
                                                                    class="f-w-600 mb-2 d-block">Length of Employment:</span>
                                                            <p>
                                                                <?php
                                                                if(!$employee_data["turnover_months"]["employees"]) {
                                                                    echo "We have not yet identified the employment length. Connect Coherta with our LinkedIn connectivity tool, to get this information.";
                                                                } else {
                                                                    echo "Employees usually stay in this company for ".round($employee_data["turnover_months"]["employees"]/12, 2)." years";
                                                                }
                                                                ?>
                                                            </p><span class="f-w-600 mb-2 d-block">Length of Employment for Management:</span>
                                                            <p>
                                                                Management usually stay in this company for <?php echo round($employee_data["turnover_months"]["management"]/12, 2) ?> years.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 xl-50 box-col-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5 class="mb-0">
                                                            <button class="btn btn-link" data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseicon15" aria-expanded="true"
                                                                    aria-controls="collapseicon15">Employees</button>
                                                        </h5>
                                                    </div>
                                                    <div class="collapse show" id="collapseicon15"
                                                         data-bs-parent="#accordion" aria-labelledby="collapseicon15">
                                                        <div class="card-body avatar-showcase filter-cards-view">
                                                            <?php
                                                            if(!$employee_data["turnover_months"]["employees"]) {
                                                                echo "Connect Coherta with our LinkedIn connectivity tool, to get this information.";
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="friends" role="tabpanel" aria-labelledby="friends">
                            <div class="mb-3">
                                <input type="text" id="participant-search" class="form-control" placeholder="Search participant name...">
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const searchInput = document.getElementById('participant-search');
                                    searchInput.addEventListener('input', function() {
                                        const value = searchInput.value.trim().toLowerCase();
                                        const cards = document.querySelectorAll('.custom-card');
                                        if (value.length > 3) {
                                            cards.forEach(card => {
                                                const nameEl = card.querySelector('.participant_name');
                                                const titleEl = card.querySelector('.participant_title');
                                                const name = nameEl ? nameEl.textContent.trim().toLowerCase() : '';
                                                const title = titleEl ? titleEl.textContent.trim().toLowerCase() : '';
                                                card.parentElement.style.display = (name.includes(value) || title.includes(value)) ? '' : 'none';
                                            });
                                        } else {
                                            cards.forEach(card => card.parentElement.style.display = '');
                                        }
                                    });
                                });
                            </script>
                            <div class="row">
                                <?php
                                    foreach ($employee_data["participants"]["active"] as $participant) {
                                        $followers = "??";
                                        $img = "assets/images/user/user.png";
                                        if(!is_null($participant["image"])) {
                                            $img = "https://api.coherta.com/images/".$participant["image"];
                                        }
                                        if(!is_null($participant["followers"])) {
                                            $followers = number_format($participant["followers"]);
                                        }
                                        echo '
                                            <div class="col-md-6 col-lg-6 col-xl-4">
                                                <div class="card custom-card pb-0">
                                                    <div class="card-header text-end">
                                                    ';
                                        if($participant['connected_crm_user_ids']) {
                                            $split = explode(",", $participant['connected_crm_user_ids']);
                                            foreach($split as $user_id) {
                                                echo '<img class="img-20 rounded-circle colleague-img" 
     src="'.$client->getColleagueImage($user_id).'" 
     alt="'.$client->getColleagueName($user_id).'"
     data-name="'.htmlspecialchars($client->getColleagueName($user_id)).'">';
                                            }
                                        }
                                        echo '
                                                    </div>
                                                    <div class="card-profile"><img class="rounded-circle"
                                                                                   src="'.$img.'" alt=""></div>
                                                    <div class="text-center profile-details">
                                                        <h5 class="participant_name" ondblclick="copyToClipboard(this)">'.$participant["name"].'</h5>
                                                        <h6 class="participant_title">'.$participant["participant_type"].'</h6>
                                                    </div>
                                                    <div class="card-footer row">
                                                        <div class="col-4 col-sm-4">
                                                            <h6>Phone number</h6>
                                                            <h3>';
                                                        if (!empty($participant["phone1"])) {
                                                            echo '<i data-feather="phone" style="width:16px;height:16px;"></i><a href="tel:'.$participant["phone1"].'">' . htmlspecialchars($participant["phone1"])."</a>";
                                                        } elseif (!empty($participant["phone2"])) {
                                                            echo '<i data-feather="phone" style="width:16px;height:16px;"></i><a href="tel:'.$participant["phone2"].'">' . htmlspecialchars($participant["phone2"])."</a>";
                                                        }
                                                        if (!empty($participant["linkedin_url"]) && $participant['id'] !== null && $participant["phone1"] === $phone["phone"]) {
                                                            echo "<br /><a href='#' class='btn btn-info request-phone' data-participant-id='".$participant['id']."' data-company-uid='".$root['uid']."'>Validate phone</a>";
                                                        }
                                                        echo '</h3>
                                                        </div>
                                                        <div class="col-4 col-sm-4">
                                                            <h6>Email</h6>
                                                            <h3>';
                                                                if (!empty($participant["email"])) {
                                                                    echo '<a href="mailto:'.$participant["email"].'"><i data-feather="mail" style="width:16px;height:16px;"></i></a>';
                                                                }
                                                        echo '</h3>
                                                        </div>
                                                        <div class="col-4 col-sm-4">
                                                            <h6>LinkedIn</h6>
                                                            <h3>';
                                                                if (!empty($participant["linkedin_url"])) {
                                                                    echo '<a target="_blank" href="' . htmlspecialchars($participant["linkedin_url"]) . '">
                                                                        <img src="assets/images/email-template/linkedin.png" alt="LinkedIn" style="width:24px;height:24px;" width="40" height="40">
                                                                    </a>';
                                                                } echo '
                                                            </h3>
                                                        </div>
                                                    </div>
                                                </div>
                                                                ';
                                        if(isset($user["plugins"]["linkedalibi"])) {
                                            echo '<button class="btn btn-primary btn-xs">Connect on LinkedIn</button>';
                                        }
                                        echo '
                                                                </div>
                                                            </li>';
                                    }
                                ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="photos" role="tabpanel" aria-labelledby="photos">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="my-gallery card-body row gallery-with-description" itemscope="">
                                            <?php
                                            if(isset($lead_data["summary"])) {
                                                echo preg_replace_callback(
                                                    '/<url>\s*(.*?)\s*<\/url>/i',
                                                    function ($matches) {
                                                        $url = trim($matches[1]);
                                                        return '<a href="' . htmlspecialchars($url) . '" target="_blank">' . htmlspecialchars($url) . '</a>';
                                                    },
                                                    $lead_data["summary"]
                                                );
                                            } else {
                                                echo "<h3>We have not yet identified latest news about this company.</h3>This can happen because:";
                                                echo "<ul>";
                                                    echo "<li>The lead have been added recently.</li>";
                                                    echo "<li>The company could not be identifed correctly.</li>";
                                                    echo "<li>There is no public information about the company.</li>";
                                                echo "</ul>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Container-fluid Ends-->
        </div>

        <?php include('partial/footer.php');?>
    </div>
</div>
    <div class="modal fade" id="addRelationModal" tabindex="-1" aria-labelledby="addRelationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRelationModalLabel">Add New Relation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                        <input type="hidden" id="person_id" name="person_id">
                        <input type="hidden" id="participant_id" name="participant_id">
                        <div class="mb-3">
                            <label for="participant_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="participant_name" name="participant_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="participant_phone1" class="form-label">Phone number</label>
                            <input type="text" class="form-control" id="participant_phone1" name="participant_phone1">
                        </div>
                        <div class="mb-3">
                            <label for="participant_phone1" class="form-label">Secondary phone number</label>
                            <input type="text" class="form-control" id="participant_phone2" name="participant_phone2">
                        </div>
                        <div class="mb-3">
                            <label for="participant_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="participant_email" name="participant_email">
                        </div>
                        <div class="mb-3">
                            <label for="participant_linkedin" class="form-label">LinkedIn Link</label>
                            <input type="url" class="form-control" id="participant_linkedin" name="participant_linkedin">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="SaveRelationbtn">Save Relation</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php include('partial/scripts.php'); ?>
    <!-- Flyt lead modal -->
    <div class="modal fade" id="moveLeadModal" tabindex="-1" aria-labelledby="moveLeadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="move-lead-form">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="moveLeadModalLabel">Move lead</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Luk"></button>
                    </div>
                    <div class="modal-body">
                        <p>Move this lead to another Lead List</p>
                        <select class="form-select" name="pipeline_id" id="move-lead-pipeline" required>
                            <?php foreach($client->getCurrentPipelines() as $pipelines) { ?>
                                <option value="<?php echo $pipelines['id'] ; ?>"><?php echo htmlspecialchars($pipelines["title"]); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="moveLeadPipeline" class="btn btn-success">Move</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<script src="assets/js/counter/jquery.waypoints.min.js"></script>
<script src="assets/js/counter/jquery.counterup.min.js"></script>
<script src="assets/js/counter/counter-custom.js"></script>
    <style>
        .colleague-tooltip {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            background: #111;
            color: #fff;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            margin-top: 4px;
            pointer-events: none;
            z-index: 100;
            opacity: 0.95;
        }
    </style>
    <script>

        (function() {
            let hideTimeout = null;
            document.addEventListener('mouseover', function(e) {
                const img = e.target.closest('.colleague-img');
                if (!img) return;
                clearTimeout(hideTimeout);
                img.classList.add('hovered');
                document.querySelectorAll('.colleague-tooltip').forEach(el => el.remove());
                const tooltip = document.createElement('div');
                tooltip.className = 'colleague-tooltip';
                tooltip.textContent = img.getAttribute('data-name') || '';
                document.body.appendChild(tooltip);
                const rect = img.getBoundingClientRect();
                tooltip.style.top = (rect.bottom + window.scrollY + 2) + 'px';
                tooltip.style.left = (rect.left + rect.width / 2 + window.scrollX) + 'px';
                img._tooltip = tooltip;
            });
            document.addEventListener('mouseout', function(e) {
                const img = e.target.closest('.colleague-img');
                if (!img) return;
                hideTimeout = setTimeout(() => {
                    img.classList.remove('hovered');
                    if (img._tooltip) {
                        img._tooltip.remove();
                        img._tooltip = null;
                    }
                }, 1000);
            });
        })();
    </script>
<script>
    <?php
    if(isset($company_data["plugins"][4]["installed"]) &&
    $company_data["plugins"][4]["installed"]) {
        ?>

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.plugin4');
        if (!btn) return;
        let prevInput = null;
        let sibling = btn.previousElementSibling;
        if (sibling && /^(INPUT|TEXTAREA|SELECT)$/i.test(sibling.tagName)) {
            prevInput = sibling;
        } else if (sibling) {
            prevInput = sibling.querySelector('input, textarea, select');
        }
        const payload = {
            uid: btn.getAttribute('data-company-uid') || '',
            vat: btn.getAttribute('data-company-vat') || '',
            id: btn.getAttribute('data-id') || '',
            l_id: btn.getAttribute('data-l_id') || '',
            name: btn.getAttribute('data-name') || '',
            phone1: btn.getAttribute('data-phone1') || '',
            phone2: btn.getAttribute('data-phone2') || '',
            email: btn.getAttribute('data-email') || '',
            linkedin: btn.getAttribute('data-linkedin') || '',
            custom_input: prevInput ? prevInput.value : ''
        };
        const url = btn.getAttribute('data-company-url') || '<?php echo addslashes($company_data["plugins"][4]["settings"]["company"][0]["value"] ?? ""); ?>';
        fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${document.body.getAttribute('session_id')}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.url) {
                    window.open(data.url, '_blank', 'noopener,noreferrer');
                } else {
                    alert('Request successful');
                }
            })
            .catch(err => {
                alert('Request failed');
            });
    });

    <?php
    }
    ?>
    // delegate click for edit buttons to prefill modal
    function emptyModal() {
        document.getElementById('addRelationModalLabel').innerHTML = "Add New Relation";
        document.getElementById('person_id').value = '';
        document.getElementById('participant_id').value = '';
        document.getElementById('participant_name').value = '';
        document.getElementById('participant_phone1').value = '';
        document.getElementById('participant_phone2').value = '';
        document.getElementById('participant_email').value = '';
        document.getElementById('participant_linkedin').value = '';
    }
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.edit-relation-btn');
        if (!btn) return;
        const modalEl = document.getElementById('addRelationModal');
        document.getElementById('addRelationModalLabel').innerHTML = "Update Relation";
        document.getElementById('person_id').value = btn.getAttribute('data-id') || '';
        document.getElementById('participant_id').value = btn.getAttribute('data-l_id') || '';
        document.getElementById('participant_name').value = btn.getAttribute('data-name') || '';
        document.getElementById('participant_phone1').value = btn.getAttribute('data-phone1') || '';
        document.getElementById('participant_phone2').value = btn.getAttribute('data-phone2') || '';
        document.getElementById('participant_email').value = btn.getAttribute('data-email') || '';
        document.getElementById('participant_linkedin').value = btn.getAttribute('data-linkedin') || '';
        // show bootstrap modal
        try {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        } catch (err) {
            $('#addRelationModal').modal('show');
        }
    });
    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("SaveRelationbtn");
        if (!btn) return;

        btn.addEventListener("click", (e) => {
            e.preventDefault(); // prevents submit
            console.log("Yay");
            console.log("Add Relation");
            e.preventDefault();
            // Find the closest form element to the button
            const form = btn.closest('form');
            const formData = new FormData(form);
            const formDataObj = Object.fromEntries(formData.entries());
            fetch('https://api.coherta.com/participants/add', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${document.body.getAttribute('session_id')}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formDataObj)
            })
                .then(response => response.json())
                .then(data => {
                    window.location.reload();
                    $('#addRelationModal').modal('hide');
                })
                .catch(error => {
                    alert('Error adding relation');
                });
            return false;
        });
    });
    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        const pipelineCompanyId = urlParams.get('id');
        attachFormSaver(".was-validated", ".btn.btn-success", "company/notes", () => {
            loadNotes(pipelineCompanyId);
        });
        if (pipelineCompanyId) {
            loadNotes(pipelineCompanyId);
        } else {
            console.warn('No id found in URL');
        }
    });
    <?php
    $stats = [];
    foreach($lead_data["employees"] as $employment_stats) {
        $formattedDate = DateTime::createFromFormat('m/Y', $employment_stats["month"]."/".$employment_stats["year"])->format('02 M Y');

        $stats[$formattedDate] = $employment_stats["employees"];
    }
    ?>
    var stats = {
        monthly_employees: {
            prices: <?php echo json_encode(array_values($stats)) ?>,
            dates: <?php echo json_encode(array_keys($stats)) ?>,
        },
        gross_profit: {
            prices: [<?php
                    if(isset($financial_data)) {
                        foreach ($financial_data as $item) {
                            if (is_null($item['GrossProfitLoss'])) {
                                echo 0;
                            } else {
                                echo $item['GrossProfitLoss'];
                            }
                            echo ",";
                        }
                    }
?>],
            dates: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    echo "'". date('d M Y', strtotime($item['end_date'])) ."'";
                    echo ",";
                } } ?>],
        },
        profit_before_tax: {
            prices: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    if (is_null($item['ProfitLossFromOrdinaryActivitiesBeforeTax'])) {
                        echo 0;
                    } else {
                        echo $item['ProfitLossFromOrdinaryActivitiesBeforeTax'];
                    }
                    echo ",";
                } } ?>],
            dates: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    echo "'". date('d M Y', strtotime($item['end_date'])) ."'";
                    echo ",";
                } } ?>],
        },
        ebitda: {
            prices: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    if (is_null($item['ebitda'])) {
                        echo 0;
                    } else {
                        echo $item['ebitda'];
                    }
                    echo ",";
                } } ?>],
            dates: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    echo "'". date('d M Y', strtotime($item['end_date'])) ."'";
                    echo ",";
                } } ?>],
        },
        result_of_year: {
            prices: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    if (is_null($item['ProfitLoss'])) {
                        echo 0;
                    } else {
                        echo $item['ProfitLoss'];
                    }
                    echo ",";
                } } ?>],
            dates: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    echo "'". date('d M Y', strtotime($item['end_date'])) ."'";
                    echo ",";
                } } ?>],
        },
        liquidity: {
            prices: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    if (is_null($item['liquidity_ratio'])) {
                        echo 0;
                    } else {
                        echo $item['liquidity_ratio'];
                    }
                    echo ",";
                } } ?>],
            dates: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    echo "'". date('d M Y', strtotime($item['end_date'])) ."'";
                    echo ",";
                } } ?>],
        },
        return_on_equity: {
            prices: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    if (is_null($item['return_on_equity'])) {
                        echo 0;
                    } else {
                        echo $item['return_on_equity'];
                    }
                    echo ",";
                } } ?>],
            dates: [<?php
                if(isset($financial_data)) {
                foreach ($financial_data as $item) {
                    echo "'". date('d M Y', strtotime($item['end_date'])) ."'";
                    echo ",";
                } } ?>],
        },
    };
    function createAreaChart(selector, seriesName, prices, dates) {
        const options = {
            chart: {
                height: 150,
                type: 'area',
                zoom: { enabled: false },
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'straight' },
            series: [{
                name: seriesName,
                data: prices
            }],
            labels: dates,
            xaxis: {
                type: 'datetime',
                labels: {
                    formatter: value =>
                        new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'short' })
                }
            },
            yaxis: { opposite: true },
            legend: { horizontalAlign: 'left' },
            colors: [CubaAdminConfig.primary]
        };

        const chart = new ApexCharts(document.querySelector(selector), options);
        chart.render().catch(err => {
            console.error(`Error rendering chart for ${selector}`, err);
        });
        return chart;
    }
    <?php
    if(count($lead_data["employees"]) > 0) {
        echo '    createAreaChart("#employee-monthly-growth", "Employees", stats.monthly_employees.prices, stats.monthly_employees.dates);';
    } ?>
    createAreaChart("#gross-profit", "Gross Profit", stats.gross_profit.prices, stats.gross_profit.dates);
    createAreaChart("#profit-before-tax", "Profit before Tax", stats.profit_before_tax.prices, stats.profit_before_tax.dates);
    createAreaChart("#ebitda", "EBITDA", stats.ebitda.prices, stats.ebitda.dates);
    createAreaChart("#result-of-the-year", "Result of the Year", stats.result_of_year.prices, stats.result_of_year.dates);
    createAreaChart("#liquidity-ratio", "liquidity Ratio", stats.liquidity.prices, stats.liquidity.dates);
    createAreaChart("#return-on-equity", "Return on Equity", stats.return_on_equity.prices, stats.return_on_equity.dates);


    function copyToClipboard(element) {
        const text = element.innerText;
        navigator.clipboard.writeText(text).then(function() {
            showCopiedNotification(element);
        });
    }

function showCopiedNotification(element) {
    let popup = document.createElement('div');
    popup.textContent = 'Data copied';
    popup.style.position = 'absolute';
    popup.style.background = 'rgba(40,167,69,0.95)';
    popup.style.color = '#fff';
    popup.style.padding = '8px 18px';
    popup.style.borderRadius = '6px';
    popup.style.zIndex = '9999';
    popup.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
    popup.style.whiteSpace = 'nowrap';

    // Get element position
    const rect = element.getBoundingClientRect();
    popup.style.left = rect.left + window.scrollX + 'px';
    popup.style.top = (rect.top + window.scrollY - popup.offsetHeight - 10) + 'px';

    document.body.appendChild(popup);

    // Recalculate top after adding to DOM (offsetHeight available)
    popup.style.top = (rect.top + window.scrollY - popup.offsetHeight - 10) + 'px';

    setTimeout(() => {
        popup.remove();
    }, 1500);
}
</script>
    <template id="note-template">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="new-users-social">
                        <div class="media"><img
                                    class="rounded-circle image-radius m-r-15"
                                    src="#IMG_KEY" alt="">
                            <div class="media-body">
                                <h6 class="mb-0 f-w-700">#FULL_NAME</h6>
                                <p class="note-created">Created: #CREATED</p>
                            </div>
                            <span class="pull-right mt-0"><i
                                        data-feather="more-vertical"></i></span>
                        </div>
                        <p class="note-text">#NOTE</p>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <script src="assets/js/todo/todo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
    <script>
        document.getElementById('move-lead-btn').addEventListener('click', function(e) {
            e.preventDefault();
            var modal = new bootstrap.Modal(document.getElementById('moveLeadModal'));
            modal.show();
        });

        document.getElementById('moveLeadPipeline').addEventListener('click', function(e) {
            console.log("submit");
            e.preventDefault();
            var pipelineId = document.getElementById('move-lead-pipeline').value;
            const sel = document.getElementById('move-lead-pipeline');
            const optionText = sel.options[sel.selectedIndex].text;
            fetch('https://api.coherta.com/pipeline/all/add_single', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${document.body.getAttribute('session_id')}`
                },
                body: JSON.stringify({"pipeline_id":pipelineId,"company_id":"<?php echo $root["company_id"]; ?>","move_lead":true})
            })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        document.querySelector('.pipeline_title').textContent = optionText;
                        bootstrap.Modal.getInstance(document.getElementById('moveLeadModal')).hide();
                    } else {
                        alert('Lead could not be moved');
                    }
                })
                .catch(() => alert('Unexpected error.'));
        });
    </script>
    <script>
        tinymce.init({
            selector: '#validationTextarea',
            plugins: 'link',
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | link',
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false
        });

        // Bind click event for .request-phone to trigger bindFullEnrichOnClick actions
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.request-phone');
            if (!btn) return;
            e.preventDefault();
            // --- begin bindFullEnrichOnClick logic ---
            const participantId = btn.getAttribute('data-participant-id');
            const companyUid = btn.getAttribute('data-company-uid');
            if (!participantId || !companyUid) {
                alert('Missing participant or company information.');
                return;
            }
            btn.disabled = true;
            btn.textContent = 'Requesting...';
            fetch('https://api.coherta.com/pipeline/leads/fullenrich', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${document.body.getAttribute('session_id')}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    participant_id: participantId,
                    company_uid: companyUid
                })
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data && typeof data === 'object' && data.detail) {
                        btn.outerHTML = data.detail;
                    } else if (typeof data === 'string') {
                        link = (typeof data === 'string' ? data.trim() : '');
                        link = link.replace(/\s+/g, '');
                        btn.parentElement.outerHTML = '<a href="tel:' + link + '">' + data + '</a>';
                    } else {
                        btn.parentElement.outerHTML = data;
                    }
                })
                .catch(() => {
                    btn.textContent = 'Request phone details';
                    alert('Unexpected error.');
                })
                .finally(() => {
                    btn.disabled = false;
                });
            // --- end bindFullEnrichOnClick logic ---
        });
        // Listen for changes to the Lead Owner select and PATCH when changed
        document.addEventListener('DOMContentLoaded', function() {
            const leadOwnerSelect = document.getElementById('LeadOwner');
            if (!leadOwnerSelect) return;
            leadOwnerSelect.addEventListener('change', function() {
                const newOwnerId = leadOwnerSelect.value;
                const accessToken = document.body.getAttribute('session_id');
                fetch('https://api.coherta.com/pipeline/leads/owner', {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${accessToken}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ pipeline_company_id: <?php echo $_GET['id']; ?>, owner_id: newOwnerId })
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Owner updated:', data);
                    })
                    .catch(error => {
                        console.error('Error updating owner:', error);
                    });
            });
        });

    </script>
<?php include('partial/footer-end.php'); ?>
