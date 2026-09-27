
if (typeof LatestFoundLeadCompanyID === 'undefined') {
    var LatestFoundLeadCompanyID = null;
}
if (typeof LatestFoundLeadCompanyName === 'undefined') {
    var LatestFoundLeadCompanyName = null;
}

$(document).ready(function () {
    var engine, remoteHost, template, empty;
    var lastLookupHasData = false;
    var lastResultsLength = 0;

    $.support.cors = true;

    remoteHost = 'https://api.coherta.com/';
    template = Handlebars.compile($(".result-template").html());
    empty = Handlebars.compile($(".empty-template").html());

    var sessionId = $('body').attr('session_id');
    var authHeader = sessionId ? { 'Authorization': 'Bearer ' + sessionId } : {};

    engine = new Bloodhound({
        identify: function (o) {
            return o.lead_id || o.uid || o.company_uid || o.name;
        },
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: remoteHost + 'search?q=%QUERY',
            wildcard: '%QUERY',
            prepare: function (query, settings) {
                settings.url = settings.url.replace('%QUERY', encodeURIComponent(query));
                settings.headers = $.extend({}, settings.headers, authHeader);
                return settings;
            },
            transform: function (response) {
                var results = [];
                lastLookupHasData = false;
                lastResultsLength = 0;

                if (!response) return [];

                if (Array.isArray(response.results)) {
                    results = response.results;
                }

                lastResultsLength = results.length;

                // Track if lookup has entries
                if (Array.isArray(response.lookup) && response.lookup.length > 0) {
                    lastLookupHasData = true;
                    LatestFoundLeadCompanyID = response.lookup[0].company_id;
                    LatestFoundLeadCompanyName = response.lookup[0].name;
                }

                return results;
            }
        }
    });

    function engineWithDefaults(q, sync, async) {
        if (q && q.trim() !== '') {
            engine.search(q, sync, async);
        } else {
            lastLookupHasData = false;
            lastResultsLength = 0;
            sync([]);
            $('.SearchFoundVAT').hide();
        }
    }

    $('.demo-input').typeahead({
        hint: $('.Typeahead-hint'),
        menu: $('.Typeahead-menu'),
        minLength: 3,
        classNames: {
            empty: 'is-empty',
            cursor: 'is-active',
            suggestion: 'Typeahead-suggestion',
            selectable: 'Typeahead-selectable'
        }
    }, {
        name: 'coherta-search',
        source: engineWithDefaults,
        displayKey: 'name',
        templates: {
            suggestion: template,
            empty: empty
        }
    })
        .on('typeahead:asyncrequest', function () {
            $('.Typeahead-menu').addClass('is-open');
            $('.Typeahead-spinner').show();
        })
        .on('typeahead:asynccancel typeahead:asyncreceive', function () {
            $('.Typeahead-spinner').hide();
        })
        .on('typeahead:render', function () {
            if (lastLookupHasData) {
                $('.SearchFoundVAT').show();
            } else {
                $('.SearchFoundVAT').hide();
            }
        });
});
