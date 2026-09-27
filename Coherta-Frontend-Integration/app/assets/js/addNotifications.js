function SaveNotifications(crm_company) {
    const bearerToken = document.body.getAttribute('session_id');
    const filters = [];

    $('.addedFilters .filter-row').each(function() {
        const $row   = $(this);
        const key    = $row.data('key') ||
            $row.find('.key span').text().trim()
                .toLowerCase().replace(/\s+/g, '_');
        const origin = $row.find('select[name="origin"]').val();
        const $val   = $row.find('input.form-control, select')
            .not('[name="origin"]');
        const value  = $val.is('[data-value]') ? $val.attr('data-value') : $val.val();

        const filter = { key, origin, value };
        filters.push(filter);
    });
    var title = $("#title").val();
    const finalPayload = {
        title: title,
        crm_company: crm_company,
        pipeline_filter: { filters: filters },
        subjects: 0
    };
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

    const method = $('input[name="pipeline_id"]').val() ? 'PATCH' : 'POST';

    $.ajax({
        url: 'https://api.coherta.com/notifications',
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(finalPayload),
        headers: {
            'Authorization': 'Bearer ' + bearerToken
        },
        success(response) {
            const pipelineId = response.id;
            const $header = $('.list-of-notification-headers');
            if (finalPayload.jobtitles && Array.isArray(finalPayload.jobtitles)) {
                finalPayload.jobtitles.forEach(function(title) {
                    $('<button>', {
                        type: 'button',
                        class: 'btn btn-outline-primary notification-action-btn',
                        'data-pipeline-id': finalPayload.pipeline_id || 0,
                        'data-notification-id': pipelineId,
                        text: title
                    }).appendTo($header);
                });
            }
            $jobtitleList.empty();
        },
        error(xhr, status, err) {
            console.error('Error applying filters:', status, err);
        }
    });
}
