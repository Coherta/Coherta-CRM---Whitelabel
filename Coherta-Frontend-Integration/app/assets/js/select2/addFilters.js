i = 0;
const filterSettingsSelections = {};
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-group [type="button"]');
    if (!btn) return;
    const group = btn.closest('.btn-group[data-settings]');
    if (!group) return;
    const key = group.dataset.settings;
    filterSettingsSelections[key] = btn.id;
    group.querySelectorAll('[type="button"]').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-primary');
    });
    btn.classList.add('btn-primary');
    btn.classList.remove('btn-outline-primary');
});

let intervalId;
const $status = $("#SubjctsCompletingFilters");

const messages = [
    "Calculating ...",
    "Identifying companies in ICP ... ",
    "Matching filters ... ",
    "Removing incorrect companies ... ",
    "Identified another match ... ",
    "Going through list to validate ... ",
    "Analyzing company signals ...",
    "Cross-checking data sources ...",
    "Scoring high-fit companies ...",
    "Filtering out duplicates ...",
    "Validating contact availability ...",
    "Enriching firmographic details ...",
    "Checking industry alignment ...",
    "Verifying company size ...",
    "Reviewing recent activity ...",
    "Refining ICP accuracy ...",
    "Updating results list ... "
];

let lastIndex = -1;

function setRandomMessage() {
    if (messages.length === 0) return;

    let idx = Math.floor(Math.random() * messages.length);

    // avoid repeating the same message twice in a row (optional)
    if (messages.length > 1) {
        while (idx === lastIndex) {
            idx = Math.floor(Math.random() * messages.length);
        }
    }

    lastIndex = idx;
    if(intervalId > 0) {
        $status.html(messages[idx]);
        setTimeout(() => { setRandomMessage(); }, intervalId);
    }
}

const sendFilters = (() => {
    let currentRequest = null;
    let latestCallId    = 0;

    return function() {
        // 1) Abort any previous request
        if (currentRequest && currentRequest.readyState !== 4) {
            currentRequest.abort();
        }

        // 2) Bump the call‑ID and capture it
        latestCallId += 1;
        const thisCallId = latestCallId;
        $("#SubjctsCompletingFilters").html("Calculating ... ");
        intervalId = 2000;
        setTimeout(() => { setRandomMessage(); }, 2000);

        // 3) Build your payload
        const bearerToken = document.body.getAttribute('session_id');
        const payload = { filters: [] };


        $('.addedFilters').each(function() {
            const $row   = $(this);
            const key    = $row.data('type');
            const origin = $row.find('select').val();
            const $val   = $row.find('input.form-control');
            let value = $val.val();
            let andor  = $row.find('.btn-and-or.btn-success').data('type');
            payload.filters.push({ key, origin, value, andor });
        });

        const websiteInfoCheckbox = document.getElementById('websiteInfoCheckbox');
        if (websiteInfoCheckbox && websiteInfoCheckbox.checked) {
            payload.websiteInfo = true;
        }

        $("#nextBtn").prop("disabled", true);
        // 4) Fire off the new request and store the jqXHR
        currentRequest = $.ajax({
            url: 'https://api.coherta.com/filter/company_new',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            headers: {
                'Authorization': 'Bearer ' + bearerToken
            },
            success(response) {
                if (thisCallId !== latestCallId) return;
                intervalId = 0;
                $("#SubjctsCompletingFilters").html(response.count);
                $("#nextBtn").prop("disabled", false);
                let idx = 0;
                payload.filters.forEach((filter, i) => {
                    console.log(filter);
                    console.log(i);
                    const item = response.progress[idx] || {};
                    const remaining = item.remaining ?? "";
                    const $calcField = $(`.addedFilters:eq(${i}) .calculation`);

                    $calcField.html("Companies left: " + remaining);
                    idx += 1;
                });

                const prevRemaining = response.progress?.at?.(-2)?.remaining
                    ?? response.progress?.[response.progress.length - 2]?.remaining
                    ?? 0;
                const removed_leads = prevRemaining - response.count;
                $(".calculation_with_active").html("We have removed " + removed_leads + " leads due to being a non-active company or already added to a leadlist.");

            },
            error(xhr, status, err) {
                // 6) Only handle if this is still the latest call,
                //    and ignore aborts
                if (thisCallId !== latestCallId || status === 'abort') return;
                intervalId = 0;
                $("#SubjctsCompletingFilters").html('Error applying filters');
                console.warn('Error applying filters:', status, err);
            }
        });
    };
})();

function SavePipeline(crm_company, redirect=true) {
    let modal = document.getElementById('savingModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'savingModal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        `;
        modal.innerHTML = `
            <div style="
                background: white;
                padding: 20px 40px;
                border-radius: 8px;
                font-size: 18px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            ">
                Saving data, please wait...
            </div>
        `;
        document.body.appendChild(modal);
    }

    const bearerToken = document.body.getAttribute('session_id');
    const filters = [];

    $('.addedFilters').each(function() {
        const $row   = $(this);
        const key    = $row.data('type');
        const origin = $row.find('select').val();
        const $val   = $row.find('input.form-control');
        let value = $val.val();
        let andor  = $row.find('.btn-and-or.btn-success').data('type');
        filters.push({ key, origin, value, andor });
    });
    websiteInfo = false;
    const websiteInfoCheckbox = document.getElementById('websiteInfoCheckbox');
    if (websiteInfoCheckbox && websiteInfoCheckbox.checked) {
        websiteInfo = true;
    }
    var title = $("#title").val();
    const finalPayload = {
        title: title,
        crm_company: crm_company,
        pipeline_filter: { filters: filters },
        subjects: 0,
        websiteInfo: websiteInfo
    };
// If #jobtitle-list exists, collect all li texts into an array and add to finalPayload
    const $jobtitleList = $('#jobtitle-list');
    if ($jobtitleList.length) {
        const jobtitles = [];
        $jobtitleList.find('li').each(function() {
            jobtitles.push($(this).text().trim());
        });
        finalPayload.jobtitles = jobtitles;
    }

    const pipelineIdInput = $('input[name="pipeline_id"]').val();
    if (pipelineIdInput) {
        finalPayload.pipeline_id = pipelineIdInput;
    }
    finalPayload.pipeline_settings = filterSettingsSelections;

    const method = $('input[name="pipeline_id"]').val() ? 'PATCH' : 'POST';

    $.ajax({
        url: 'https://api.coherta.com/pipeline',
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(finalPayload),
        headers: {
            'Authorization': 'Bearer ' + bearerToken
        },
        success(response) {
            const pipelineId = response.id;
            if (pipelineId && redirect)
                window.location.href = `/app/leads.php?pipeline_id=${pipelineId}`;
            else
                modal.style.display = 'none';
        },
        error(xhr, status, err) {
            console.error('Error applying filters:', status, err);
        }
    });
}
