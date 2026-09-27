<?php
global $user,$user_image;
$company_updates = $client->get("users/company/updates");
?>
<div class="page-header">
    <div class="header-wrapper row m-0">

        <div class="header-logo-wrapper col-auto p-0">
            <div class="logo-wrapper"><a href="/app/"><img class="img-fluid" src="/assets/img/logo/logo.png?w=150" alt="" width="3200" height="715"></a></div>
            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i></div>
        </div>
        <div class="nav-right pull-left left-header col-6 p-0 ms-auto">
            <div class="row">
                <div class="col-12">
                    <?php if (isset($PageTitle) && strlen($PageTitle) < 25): ?>
                        <h3 style="line-height: 50px;vertical-align: middle;align-items: center;"><?php echo $PageTitle; ?></h3>
                    <?php else: ?>
                        <h7 style="line-height: 50px;vertical-align: middle;align-items: center;"><?php echo $PageTitle; ?></h7>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="nav-right pull-right right-header col-6 p-0 ms-auto">
            <ul class="nav-menus">
                <?php
                if (!empty($win_deal_topbar)) {
                $pipelineId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                ?>
                    <li class="nav-item ms-2">

                        <button
                                id="move-deal-btn"
                                class="btn-outline-primary btn-primary text-white btn-sm" style="min-width:140px;"
                                type="button"
                                data-lead-id="<?= $pipelineId ?>"
                        >
                            <span class="ms-1">Change Company VAT</span>
                        </button>
                    </li>

                    <div id="moveDealModal" class="modal" style="display:none;position:fixed;height:160px;top:10px;left:50%;transform:translateX(-50%);background:#fff;padding:12px 18px;border-radius:8px;z-index:9999;min-width:280px;max-width:350px;box-shadow:0 2px 16px rgba(0,0,0,0.15);">
                        <h5 style="font-size:1.1rem;">Change VAT</h5>
                        <input type="text" id="newCompanyId" placeholder="Enter new Company VAT" class="form-control mb-2" style="font-size:0.95rem;">
                        <div id="companyNamePreview" style="font-size:0.95rem; margin-bottom:8px;"></div>
                        <div id="moveDealWarning" style="color:red;display:none;font-size:0.9rem;"></div>
                        <button id="confirmMoveDeal" class="btn btn-success btn-sm" style="font-size:0.95rem;display:none;">OK</button>
                        <button id="closeMoveDealModal" class="btn btn-secondary btn-sm" style="font-size:0.95rem;">Cancel</button>
                    </div>
                    <script>
                        document.getElementById('move-deal-btn').onclick = function() {
                            document.getElementById('moveDealModal').style.display = 'block';
                            document.getElementById('moveDealWarning').style.display = 'none';
                            document.getElementById('companyNamePreview').innerText = '';
                            document.getElementById('newCompanyId').value = '';
                            document.getElementById('confirmMoveDeal').style.display = 'none';
                        };

                        document.getElementById('closeMoveDealModal').onclick = function() {
                            document.getElementById('moveDealModal').style.display = 'none';
                        };

                        document.getElementById('newCompanyId').oninput = async function() {
                            const companyId = this.value.trim();
                            const preview = document.getElementById('companyNamePreview');
                            const okBtn = document.getElementById('confirmMoveDeal');
                            const bearerToken = document.body.getAttribute('session_id');
                            preview.innerText = '';
                            okBtn.style.display = 'none';
                            if (!companyId || companyId.length !== 8) return;
                            const res = await fetch(`https://api.coherta.com/pipeline/all/add_single?company_id=${companyId}`, {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json', "Authorization": "Bearer " + bearerToken },
                                body: JSON.stringify({company_id: companyId})
                            });
                            const data = await res.json();
                            if (data.company_id) {
                                preview.innerText = `Company: ${data.name}`;
                                okBtn.style.display = 'inline-block';
                            } else {
                                preview.innerText = 'Company not found.';
                            }
                        };

                        document.getElementById('confirmMoveDeal').onclick = async function() {
                            const companyId = document.getElementById('newCompanyId').value.trim();
                            const leadId = document.getElementById('move-deal-btn').getAttribute('data-lead-id');
                            if (!companyId) return;
                            // Confirm and send PATCH
                            await fetch('https://api.coherta.com/pipeline/leads/details/' + leadId, {
                                method: 'PATCH',
                                headers: {'Content-Type': 'application/json', Authorization: "Bearer " + document.body.getAttribute('session_id') },
                                body: JSON.stringify({company_id: companyId})
                            });
                            document.getElementById('moveDealModal').style.display = 'none';
                            location.reload();
                        };
                    </script>
                <!-- Desktop buttons -->
                <?php

                if ($lead_data["pipeline"][0]["status"] == 1 || $lead_data["pipeline"][0]["status"] == 2) {
                    ?>
                    <li class="nav-item">
                        <button
                                id="deal-won-btn"
                                class="btn-info btn-sm"
                                type="button"
                                data-status="open"
                                data-pipeline-id="<?= $pipelineId ?>"
                                data-action="https://api.coherta.com/pipeline/leads/status">
                            <i data-feather="check-circle"></i><span class="ms-1">Reopen Deal</span>
                        </button>
                    </li>
                    <?php
                } else {
                    ?>
                    <li class="nav-item">
                        <button
                                id="deal-won-btn"
                                class="btn-success btn-sm"
                                type="button"
                                data-status="won"
                                data-pipeline-id="<?= $pipelineId ?>"
                                data-action="https://api.coherta.com/pipeline/leads/status">
                            <i data-feather="check-circle"></i><span class="ms-1">Deal won</span>
                        </button>
                    </li>
                    <li class="nav-item ms-2">
                        <button
                                id="deal-lost-btn"
                                class="btn-outline-danger btn-danger text-white btn-sm"
                                type="button"
                                data-status="lost"
                                data-pipeline-id="<?= $pipelineId ?>"
                                data-action="https://api.coherta.com/pipeline/leads/status">
                            <i data-feather="x-circle"></i><span class="ms-1">Deal lost</span>
                        </button>
                    </li>
                    <?php
                    }
                }

                ?>

                <li class="onhover-dropdown" style="display: none">
                    <svg>
                        <use href="/app/assets/svg/icon-sprite.svg#star"></use>
                    </svg>
                    <div class="onhover-show-div bookmark-flip">
                        <div class="flip-card">
                            <div class="flip-card-inner">
                                <div class="front">
                                    <h6 class="f-18 mb-0 dropdown-title">Bookmark</h6>
                                    <ul class="bookmark-dropdown">
                                        <li>
                                            <div class="row">
                                                <div class="col-4 text-center">
                                                    <div class="bookmark-content">
                                                        <div class="bookmark-icon"><i data-feather="file-text"></i></div><span>Forms</span>
                                                    </div>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <div class="bookmark-content">
                                                        <div class="bookmark-icon"><i data-feather="user"></i></div><span>Profile</span>
                                                    </div>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <div class="bookmark-content">
                                                        <div class="bookmark-icon"><i data-feather="server"></i></div><span>Tables</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="text-center"><a class="flip-btn f-w-700" id="flip-btn" href="javascript:void(0)">Add New Bookmark</a></li>
                                    </ul>
                                </div>
                                <div class="back">
                                    <ul>
                                        <li>
                                            <div class="bookmark-dropdown flip-back-content">
                                                <input type="text" placeholder="search...">
                                            </div>
                                        </li>
                                        <li><a class="f-w-700 d-block flip-back" id="flip-back" href="javascript:void(0)">Back</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li style="display: none">
                    <div class="mode">
                        <svg>
                            <use href="/app/assets/svg/icon-sprite.svg#moon"></use>
                        </svg>
                    </div>
                </li>
                <li class="onhover-dropdown" style="display: none">
                    <div class="notification-box">
                        <svg>
                            <use href="/app/assets/svg/icon-sprite.svg#notification"></use>
                        </svg><span class="badge rounded-pill badge-secondary">4 </span>
                    </div>
                    <div class="onhover-show-div notification-dropdown">
                        <h6 class="f-18 mb-0 dropdown-title">Notitications </h6>
                        <ul>
                            <li class="b-l-primary border-4">
                                <p>Delivery processing <span class="font-danger">10 min.</span></p>
                            </li>
                            <li class="b-l-success border-4">
                                <p>Order Complete<span class="font-success">1 hr</span></p>
                            </li>
                            <li class="b-l-secondary border-4">
                                <p>Tickets Generated<span class="font-secondary">3 hr</span></p>
                            </li>
                            <li class="b-l-warning border-4">
                                <p>Delivery Complete<span class="font-warning">6 hr</span></p>
                            </li>
                            <li><a class="f-w-700" href="#">Check all</a></li>
                        </ul>
                    </div>
                </li>
                <li class="profile-nav onhover-dropdown pe-0 py-0">
                    <div class="media profile-media"><img class="img-50 rounded-circle" src="<?php echo $client->getColleagueImage($user['id']); ?>" alt="<?php echo htmlspecialchars($user['full_name'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="media-body"><span><?php echo htmlspecialchars($user['full_name']) ?></span>
                        </div>
                    </div>
                    <ul class="profile-dropdown onhover-show-div">
                        <li>Overview</li>
                        <li><a href="/app/my-account"><i data-feather="file-text"></i><span>Settings</span></a></li>
                        <li><a href="/app/logout"><i data-feather="log-in"> </i><span>Log out</span></a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <script class="result-template" type="text/x-handlebars-template">
            <a href="/app/lead?id={{lead_id}}">
                <div class="ProfileCard u-cf">
                    <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
                    <div class="ProfileCard-details">
                        <div class="ProfileCard-realName">{{name}}</div>
                    </div>
                </div>
            </a>
          </script>
        <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results!<div class="SearchFoundVAT" style="display:none;">We found the lead in our global database.<br /><div id="createLeadFromSearch" class="btn btn-success">Create lead</div></button></div></div></script>
        <div id="createDealModal" class="modal" style="display:none;position:fixed;height:auto;top:10px;left:50%;transform:translateX(-50%);background:#fff;padding:12px 18px;border-radius:8px;z-index:9999;min-width:320px;max-width:400px;box-shadow:0 2px 16px rgba(0,0,0,0.15);">
            <h5 style="font-size:1.1rem;">Create Lead for Company</h5>
            <div class="mb-2">
                <label for="CreatenewCompanyId" class="form-label">Company VAT</label>
                <input type="text" id="CreatenewCompanyId" placeholder="Enter Company VAT" class="form-control mb-1" style="font-size:0.95rem;">
            </div>
            <div class="mb-2">
                <label for="CreateNewCompanyName" class="form-label">Company Name</label>
                <input type="text" id="CreateNewCompanyName" disabled="disabled" class="form-control mb-1" style="font-size:0.95rem;">
            </div>
            <div id="companyNamePreview" style="font-size:0.95rem; margin-bottom:8px;"></div>
            <div id="leadListWrapper" style="margin-bottom:8px;">
                <label for="leadListSelect" class="form-label">List</label>
                <select id="leadListSelect" class="form-select">
                    <?php
                    foreach($client->getCurrentPipelines() as $pipelines) {
                        echo '<option value="' . $pipelines['id'] . '">' . htmlspecialchars($pipelines['title']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div id="createDealWarning" style="color:red;display:none;font-size:0.9rem;"></div>
            <button id="createLeadBtn" class="btn btn-success btn-sm" style="font-size:0.95rem;">Create lead</button>
            <button id="closeCreateDealModal" class="btn btn-secondary btn-sm" style="font-size:0.95rem;">Cancel</button>
        </div>
        <script>
            (function () {
                const modal         = document.getElementById('createDealModal');
                const closeBtn      = document.getElementById('closeCreateDealModal');
                const vatInput      = document.getElementById('CreatenewCompanyId');
                const nameInput     = document.getElementById('CreateNewCompanyName');
                const namePreview   = document.getElementById('companyNamePreview');
                const warning       = document.getElementById('createDealWarning');
                const createLeadBtn = document.getElementById('createLeadBtn');

                const leadListSelect = document.getElementById('leadListSelect');
                const lastPipelineId = localStorage.getItem('lastPipelineId');
                if (leadListSelect && lastPipelineId) {
                    for (let i = 0; i < leadListSelect.options.length; i++) {
                        if (leadListSelect.options[i].value === lastPipelineId) {
                            leadListSelect.selectedIndex = i;
                            break;
                        }
                    }
                }

                document.addEventListener('click', function (e) {
                    if (e.target && e.target.id === 'createLeadFromSearch') {
                        modal.style.display = 'block';
                        warning.style.display = 'none';
                        namePreview.textContent = '';
                        vatInput.value = LatestFoundLeadCompanyID
                        nameInput.value = LatestFoundLeadCompanyName;
                    }
                });
                if (closeBtn) {
                    closeBtn.addEventListener('click', function () {
                        modal.style.display = 'none';
                        var input = document.getElementById('searchInput');
                        if (input) input.focus();
                    });
                }
                if (createLeadBtn) {
                    createLeadBtn.addEventListener('click', async function () {
                        const companyId = vatInput.value.trim();
                        const companyName = nameInput.value.trim();
                        const pipelineId = document.getElementById('leadListSelect').value;
                        const bearerToken = document.body.getAttribute('session_id');
                        warning.style.display = 'none';

                        if (!companyId || companyId.length !== 8) {
                            warning.textContent = 'Please enter a valid 8-digit Company VAT.';
                            warning.style.display = 'block';
                            return;
                        }

                        // Call Add Lead endpoint
                        const res = await fetch('https://api.coherta.com/pipeline/all/add_single', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + bearerToken
                            },
                            body: JSON.stringify({
                                company_id: companyId,
                                name: companyName,
                                pipeline_id: pipelineId
                            })
                        });
                        localStorage.setItem('lastPipelineId', document.getElementById('leadListSelect').value);

                        const data = await res.json();
                        if (data && data.id) {
                            modal.style.display = 'none';
                            location.href = 'lead.php?id=' + data.id;
                        } else {
                            warning.textContent = data.message || 'Failed to create lead.';
                            warning.style.display = 'block';
                        }
                    });
                }
            })();
        </script>


    </div>
</div>
