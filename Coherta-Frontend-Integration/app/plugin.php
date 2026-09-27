<?php
require_once "./base.php";
$plugins = $client->get("plugins/details", ['id' => isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0]);
$plugins_mapping = $client->get("plugins/mapping", ['id' => isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0]);
$PageTitle = $plugins['title'] ?? "Plugins";
include('partial/header.php'); ?>

    <link rel="stylesheet" type="text/css" href="assets/css/vendors/todo.css">

<?php include('partial/loader.php'); ?>

    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Header Start-->
        <?php include('partial/topbar.php'); ?>
        <!-- Page Header Ends -->
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page Sidebar Start-->
            <?php include('partial/sidebar.php'); ?>
            <!-- Page Sidebar Ends-->
            <div class="page-body">
                <?php include('partial/breadcrumb.php'); ?>
                <!-- Container-fluid starts-->
                <div class="container-fluid plugin-admin-wrap">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center top-bar p-3 mb-3">
                                <h3 class="mb-0"><?= $PageTitle ?></h3>
                                <div class="d-flex align-items-center">
                                    <?php
                                    if(!isset($plugins['installed'])) {
                                        $plugins['installed'] = false;
                                    }
                                    $paid = coherta_company_has_product($company_data, 'prod_Tj6gIhPsQhHIq9', 12);
                                    if($paid) {
                                    ?>
                                    <button id="plugin-action-btn" class="btn btn-<?= $plugins['installed'] ? 'danger' : 'primary' ?>">
                                        <?= $plugins['installed'] ? 'Uninstall' : 'Install' ?>
                                    </button>
                                    <?php
                                    } else {
                                        echo '<a href="upgrade?product=Tj6gIhPsQhHIq9">
                                            <button class="btn btn-warning">
                                                Upgrade to install
                                            </button>
                                        </a>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <ul class="list-group" id="plugin-menu">
                                <li class="list-group-item active" data-section="info">Information</li>
                                <li class="list-group-item" data-section="global">Global Settings</li>
                                <li class="list-group-item" data-section="mapping">Integration Mapping</li>
                            </ul>
                        </div>
                        <div class="col-md-9">
                            <div id="plugin-content" class="p-3 border bg-white">
                                <div id="plugin-mapping" class="plugin-section d-none">

                                    <?php
                                    function flattenFields(array $data, string $prefix = ''): array {
                                        $result = [];
                                        foreach ($data as $key => $value) {
                                            $fullKey = $prefix === '' ? $key : $prefix . '.' . $key;
                                            if (is_array($value)) {
                                                if (array_values($value) === $value) {
                                                    foreach ($value as $subfield) {
                                                        $leafKey = $fullKey . '.' . $subfield; // e.g. organization.address.city
                                                        $result[$leafKey] = $leafKey;
                                                    }
                                                } else {
                                                    $result += flattenFields($value, $fullKey);
                                                }
                                            } else {
                                                $result[$fullKey] = $value;
                                            }
                                        }
                                        return $result;
                                    }

                                    $organizationFields = flattenFields($plugins_mapping['organization'], 'organization');
                                    $peopleFields       = flattenFields($plugins_mapping['people'], 'people');
                                    $field_mapping      = json_decode(json_encode($plugins["configuration"]["field_mapping"]), true);
                                    echo '<form id="organization-fields-form" method="post" action="">';
                                    foreach ($organizationFields as $name => $label) {
                                        ?>
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <?php echo htmlspecialchars($name); ?>
                                            </label>
                                            <input
                                                    type="text"
                                                    class="form-control"
                                                    name="<?php echo htmlspecialchars($name); ?>"
                                                    value="<?php echo isset($field_mapping[$name]) ? htmlspecialchars($field_mapping[$name]) : ''; ?>"
                                                    placeholder="<?php echo htmlspecialchars($label); ?>"
                                            >
                                        </div>
                                        <?php
                                    }
                                    foreach ($peopleFields as $name => $label) {
                                        ?>
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <?php echo htmlspecialchars($name); ?>
                                            </label>
                                            <input
                                                    type="text"
                                                    class="form-control"
                                                    name="<?php echo htmlspecialchars($name); ?>"
                                                    value="<?php echo isset($field_mapping[$name]) ? htmlspecialchars($field_mapping[$name]) : ''; ?>"
                                                    placeholder="<?php echo htmlspecialchars($label); ?>"
                                            >
                                        </div>
                                        <?php
                                    }
                                    echo '<button type="submit" class="btn btn-primary">Save</button>';
                                    echo '</form>';
                                    ?>
                                </div>
                                <div id="plugin-info" class="plugin-section"><?php echo $plugins['description'] ?? ""; ?></div>
                                <div id="plugin-global" class="plugin-section d-none">
                                    <?php
                                    if (!empty($plugins['settings']['standard']['company'])) {
                                        foreach ($plugins['settings']['standard']['company'] as $field) {
                                            foreach ($field['fields'] as $pipeline_fields) {
                                                $value = $plugins['configuration']['company'][$pipeline_fields['key']] ?? $field['default'] ?? '';
                                                if(!is_string($value)) $value = "";
                                                echo '<div class="form-group">';
                                                echo '<label>' . htmlspecialchars($pipeline_fields['title']) . '</label>';
                                                echo '<input type="text" class="form-control" name="' . htmlspecialchars($field['key']) . '" value="' . htmlspecialchars($value) . '" '
                                                        . (!empty($field['required_at_setup']) ? 'required' : '') . '>';
                                                echo '</div>';
                                            }
                                        }
                                    }
                                    if (!empty($plugins['settings']['standard']['company'])) {
                                        foreach ($plugins['settings']['standard']['company'] as $field) {
                                            $value = $plugins['configuration']['global'][$field['key']] ?? $field['default'] ?? '';
                                            if(!is_string($value)) $value = "";
                                            echo '<div class="form-group">';
                                            echo '<label>' . ($field['title']) . '</label>';
                                            echo '<input type="text" class="form-control" name="' . htmlspecialchars($field['key']) . '" value="' . htmlspecialchars($value) . '" '
                                                    . (!empty($field['required_at_setup']) ? 'required' : '') . '>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div id="plugin-user" class="plugin-section d-none">
                                    <?php
                                    if (!empty($plugins['settings']['standard']['user'])) {
                                        foreach ($plugins['settings']['standard']['user'] as $field) {
                                            $value = $plugins['configuration']['user'][$field['key']] ?? $field['default'] ?? '';
                                            if(!is_string($value)) $value = "";
                                            echo '<div class="form-group">';
                                            echo '<label>' . htmlspecialchars($field['title']) . '</label>';
                                            echo '<input type="text" class="form-control" name="' . htmlspecialchars($field['key']) . '" value="' . htmlspecialchars($value) . '" '
                                                    . (!empty($field['required_at_setup']) ? 'required' : '') . '>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div id="plugin-pipeline" class="plugin-section d-none">
                                    <?php
                                    if (!empty($plugins['settings']['standard']['pipeline'])) {
                                        foreach ($plugins['settings']['standard']['pipeline'] as $field) {
                                            foreach ($field['fields'] as $pipeline_fields) {
                                                $value = $plugins['configuration']['pipeline'][$pipeline_fields['key']] ?? $field['default'] ?? '';
                                                if(!is_string($value)) $value = "";
                                                echo '<div class="form-group">';
                                                echo '<label>' . htmlspecialchars($pipeline_fields['title']) . '</label>';
                                                echo '<input type="text" class="form-control" name="' . htmlspecialchars($field['key']) . '" value="' . htmlspecialchars($value) . '" '
                                                        . (!empty($field['required_at_setup']) ? 'required' : '') . '>';
                                                echo '</div>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid Ends-->
                <!-- Container-fluid ends                    -->
            </div>

            <?php include('partial/footer.php'); ?>
        </div>
    </div>
<?php include('partial/scripts.php'); ?>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        const API_URL = 'https://api.coherta.com/plugins/details/fields';
        document.getElementById('organization-fields-form')
            .addEventListener('submit', async function (e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                const fields = {};
                for (const [key, value] of formData.entries()) {
                    if (value !== '') {
                        fields[key] = value;
                    }
                }
                const payload = {
                    fields: fields,
                    plugin_id: <?= json_encode($plugins['id'] ?? 0) ?>,
                };

                try {
                    const res = await fetch(API_URL, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer <?= isset($_SESSION["token"]) ? $_SESSION["token"] : "" ?>',
                        },
                        body: JSON.stringify(payload),
                    });

                    const text = await res.text();
                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status + ' – ' + text);
                    } else {
                        alert('Field mapping saved successfully!');
                    }
                } catch (err) {
                    console.error(err);
                }
            });
        jQuery(function() {
            jQuery('#plugin-menu .list-group-item').on('click', function() {
                jQuery('#plugin-menu .list-group-item').removeClass('active');
                jQuery(this).addClass('active');
                const section = jQuery(this).data('section');
                jQuery('.plugin-section').addClass('d-none');
                jQuery('#plugin-' + section).removeClass('d-none');
            });


            jQuery('#plugin-action-btn').on('click', function() {
                const action = $(this).text().toLowerCase().trim();
                if (action === 'install') {
                    // Build modal content
                    let requiredFields = [];
                    <?php
                    if (!empty($plugins['settings']['standard']['company'])) {
                        foreach ($plugins['settings']['standard']['company'] as $field) {
                            if (!empty($field['required_at_setup'])) {
                                echo "requiredFields.push({key: '{$field['key']}',group: 'company', title: '{$field['title']}', default: '{$field['default']}'});";
                            }
                        }
                    }
                    if (!empty($plugins['settings']['standard']['user'])) {
                        foreach ($plugins['settings']['standard']['user'] as $field) {
                            if (!empty($field['required_at_setup'])) {
                                echo "requiredFields.push({key: '{$field['key']}',group: 'user', title: '{$field['title']}', default: '{$field['default']}'});";
                            }
                        }
                    }
                    ?>
                    let modalHtml = '<div id="plugin-install-modal" class="modal" tabindex="-1" role="dialog" style="display:block;">' +
                        '<div class="modal-dialog" role="document"><div class="modal-content">' +
                        '<div class="modal-header"><h5 class="modal-title">Confirm Install</h5></div>' +
                        '<div class="modal-body"><p>Are you sure you want to install this plugin?</p>';
                    if (requiredFields.length > 0) {
                        modalHtml += '<form id="plugin-install-form">';
                        requiredFields.forEach(function(field) {
                            modalHtml += '<div class="form-group">' +
                                '<label>' + field.title + '</label>' +
                                '<input type="text" class="form-control" data-group="' + field.group + '" name="' + field.key + '" required placeholder="' + field.default + '">' +
                                '</div>';
                        });
                        modalHtml += '</form>';
                    }
                    modalHtml += '</div>' +
                        '<div class="modal-footer">' +
                        '<button type="button" id="confirm-install" class="btn btn-primary">Install</button>' +
                        '<button type="button" id="cancel-install" class="btn btn-secondary">Cancel</button>' +
                        '</div></div></div></div>';
                    $('body').append(modalHtml);

                    $('#cancel-install').on('click', function() {
                        $('#plugin-install-modal').remove();
                    });

                    $('#confirm-install').on('click', function() {
                        if (requiredFields.length > 0) {
                            let valid = true;
                            $('#plugin-install-form input[required]').each(function() {
                                if (!$(this).val()) valid = false;
                            });
                            if (!valid) {
                                alert('Please fill out all required fields.');
                                return;
                            }
                        }
                        $.ajax({
                            url: 'https://api.coherta.com/plugins/details',
                            type: 'PATCH',
                            contentType: 'application/json',
                            headers: {
                                'Authorization': 'Bearer <?= isset($_SESSION["token"]) ? $_SESSION["token"] : "" ?>'
                            },
                            data: JSON.stringify({
                                plugin_id: <?= json_encode($plugins['id'] ?? 0) ?>,
                                requiredFields: (() => {
                                    let fields = {};
                                    $('#plugin-install-form input[required]').each(function() {
                                        if (!fields[$(this).attr('data-group')]) fields[$(this).attr('data-group')] = {};
                                        fields[$(this).attr('data-group')][$(this).attr('name')] = $(this).val();
                                    });
                                    return fields;
                                })()
                            }),
                            success: function() {
                                $('#plugin-install-modal').remove();
                                <?php

                                if(isset($plugins["redirect_on_install"]) &&
                                        $plugins["redirect_on_install"] !== false && $plugins["redirect_on_install"] !== null) {
                                    $url = strtr($plugins['redirect_on_install'], [
                                            '{company_id}' => $company_data['id'],
                                            '{session_id}' => $_SESSION['token'],
                                    ]);
                                    echo "window.location.href = '" . $url . "';";
                                } else {
                                    echo "location.reload();";
                                }
                                ?>
                            },
                            error: function() {
                                alert('Failed to install plugin.');
                            }
                        });
                    });
                }
                if (action === 'uninstall') {
                    if (confirm('Are you sure you want to uninstall this plugin?')) {
                        $.ajax({
                            url: 'https://api.coherta.com/plugins/details',
                            type: 'PATCH',
                            contentType: 'application/json',
                            headers: {
                                'Authorization': 'Bearer <?= isset($_SESSION["token"]) ? $_SESSION["token"] : "" ?>'
                            },
                            data: JSON.stringify({
                                plugin_id: <?= json_encode($plugins['id'] ?? 0) ?>,
                                installed: 0
                            }),
                            success: function() {
                                alert('Plugin uninstalled!');
                                location.reload();
                            },
                            error: function() {
                                alert('Failed to uninstall plugin.');
                            }
                        });
                    }
                }
            });
        });
    </script>

<?php include('partial/footer-end.php'); ?>