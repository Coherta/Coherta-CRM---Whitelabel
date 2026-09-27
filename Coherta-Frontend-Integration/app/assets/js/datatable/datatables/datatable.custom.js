$(document).ready(function() {
    const enum_stages = {1:"Initial",2:"Contacted",3:"Qualified",4:"Proposal",5:"Negotiation"};
    $('product-list').DataTable();
    // Basic table example 
    $('#basic-1').DataTable();
    $('#basic-2').DataTable({
        "paging":   true,
        "ordering": false,
        "info":     false
    });
    $('#basic-3').DataTable({
        "order": [[ 3, "desc" ]]
    });
    $('#basic-4').DataTable({
        columnDefs: [ {
            targets: [ 0 ],
            orderData: [ 0, 1 ]
        }, {
            targets: [ 1 ],
            orderData: [ 1, 0 ]
        }, {
            targets: [ 4 ],
            orderData: [ 4, 0 ]
        } ]
    });
    $('table.show-case').DataTable();
    $('#basic-5').DataTable({
        "columnDefs": [
            {
                "targets": [ 2 ],
                "visible": false,
                "searchable": false
            },
            {
                "targets": [ 3 ],
                "visible": false
            }
        ]
    });
    $('#basic-6').DataTable();
    $('#basic-7').DataTable({
        "dom": '<"wrapper"ltipf>'
    });
    $('#basic-8').DataTable();
    $('#basic-9').DataTable({
        stateSave: true
    });
    $('#basic-10').DataTable({
        "pagingType": "full_numbers"
    });
    $('#basic-11').DataTable({
        "scrollY":        "200px",
        "scrollCollapse": true,
        "paging":         false
    });
    $('#basic-12').DataTable({
        scrollY: '40vh',
        scrollCollapse: true,
        paging:         false
    });
    $('#basic-13').DataTable({
        "scrollY": 200,
        "scrollX": true
    });
    $('#basic-14').DataTable({
        "language": {
            "decimal": ",",
            "thousands": "."
        }
    });
    // Advance data table
    var table = $('#advance-1').DataTable();
    $('#advance-1 tbody').on('click', 'tr', function () {
        var data = table.row( this ).data();
        alert( 'You clicked on '+data[0]+'\'s row' );
    });
    var eventFired = function ( type ) {
        var n = $('#demo_info')[0];
        n.innerHTML += '<div class="me-2"><b>'+type+' event - </b>'+new Date().getTime()+'</div>';
        n.scrollTop = n.scrollHeight;
    }
    $('#advance-2')
        .on( 'order.dt',  function () { eventFired( 'Order' ); } )
        .on( 'search.dt', function () { eventFired( 'Search' ); } )
        .on( 'page.dt',   function () { eventFired( 'Page' ); } )
        .DataTable();
    $('#advance-3').DataTable({
        "columnDefs": [
            {
                "render": function ( data, type, row ) {
                    return data +' ('+ row[3]+')';
                },
                "targets": 0
            },
            { "visible": false,  "targets": [ 3 ] }
        ]
    });
    $('#advance-4').DataTable({
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
    });
    $('#advance-5').DataTable({
        "dom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>'
    });
    $('#advance-6').DataTable({
        "columnDefs": [ {
            "visible": false,
            "targets": -1
        } ]
    });
    $('#advance-7').DataTable({
        "columns": [
            { "data": "name" },
            { "data": "position" },
            { "data": "office" },
            { "data": "age" },
            { "data": "start_date" },
            { "data": "salary" }
        ]
    });
    $('#advance-8').DataTable({
        "language": {
            "url": "assets/json/German.json"
        }
    });
    $('#advance-9').DataTable({
        "searching": false,
        "ordering": false
    });
    $('#advance-10').DataTable({
        "createdRow": function ( row, data, index ) {
            if ( data[5].replace(/[\$,]/g, '') * 1 > 150000 ) {
                $('td', row).eq(5).addClass('text-danger');
            }
        }
    });
    var table = $('#advance-11').DataTable({
        "columnDefs": [
            { "visible": false, "targets": 2 }
        ],
        "order": [[ 2, 'asc' ]],
        "displayLength": 25,
        "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;
            api.column(2, {page:'current'} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        '<tr class="group"><td colspan="5">'+group+'</td></tr>'
                    );
                    last = group;
                }
            });
        }
    });
    $('#advance-11 tbody').on( 'click', 'tr.group', function () {
        var currentOrder = table.order()[0];
        if ( currentOrder[0] === 2 && currentOrder[1] === 'asc' ) {
            table.order( [ 2, 'desc' ] ).draw();
        }
        else {
            table.order( [ 2, 'asc' ] ).draw();
        }
    });
    $('#advance-12').DataTable({
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                i.replace(/[\$,]/g, '')*1 :
                typeof i === 'number' ?
                i : 0;
            };
            total = api
                .column( 4 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTotal = api
                .column( 4, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $( api.column( 4 ).footer() ).html(
                '$'+pageTotal +' ( $'+ total +' total)'
            );
        }
    });
    $('#advance-13').DataTable({
        "dom": '<"toolbar">frtip'
    });
    $("div.toolbar").html('<b>Hello This is custom toolbar</b>');
    $('#advance-14').DataTable({
        "aoColumns": [
            null,
            null,
            { "orderSequence": [ "asc" ] },
            { "orderSequence": [ "desc", "asc", "asc" ] },
            { "orderSequence": [ "desc" ] },
            null
        ]
    });

    // API Data Tables
    var t = $('#API-1').DataTable();
    var counter = 10;
    $('#addRow').on( 'click', function () {
        t.row.add( [
            counter +'1',
            counter +'.2',
            counter +'.3',
            counter +'.4',
            counter +'.5'
        ]).draw(false);
        counter++;
    });
    // Automatically add a first row of data
    $('#addRow').click();
    $('#addRow').click();
    $('#addRow').click();
    // Setup - add a text input to each footer cell
    $('#API-2 tfoot th').each( function () {
        var title = $(this).text();
        $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
    } );
    var table = $('#API-2').DataTable();
    table.columns().every( function () {
        var that = this;
        $( 'input', this.footer() ).on( 'keyup change', function () {
            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        });
    });
    $('#API-3').DataTable({
        initComplete: function () {
            this.api().columns().every( function () {
                var column = this;
                var select = $('<select><option value=""></option></select>')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                    var val = $.fn.dataTable.util.escapeRegex(
                        $(this).val()
                    );
                    column
                        .search( val ? '^'+val+'$' : '', true, false )
                        .draw();
                    });
                    column.data().unique().sort().each( function ( d, j ) {
                    select.append( '<option value="'+d+'">'+d+'</option>' )
                });
            });
        }
    });
    var table = $('#API-4').DataTable();
        $('#API-4 tbody')
        .on( 'mouseenter', 'td', function () {
            var colIdx = table.cell(this).index().column;
            $( table.cells().nodes() ).removeClass( 'highlight' );
            $( table.column( colIdx ).nodes() ).addClass( 'highlight' );
        });
    function format ( d ) {
        return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
            '<tr>'+
            '<td>Full name:</td>'+
            '<td>'+d.name+'</td>'+
            '</tr>'+
            '<tr>'+
            '<td>Extension number:</td>'+
            '<td>'+d.extn+'</td>'+
            '</tr>'+
            '<tr>'+
            '<td>Extra info:</td>'+
            '<td>And any further details here (images etc)...</td>'+
            '</tr>'+
            '</table>';
    }
    //chield row multiple data table start here
    var ct = $('#API-chield-row').DataTable({
        "ajax": "../assets/ajax/api.txt",
        "columns": [{
            "className": 'details-control',
            "orderable": false,
            "data": null,
            "defaultContent": ''
        }, {
            "data": "name"
        }, {
            "data": "position"
        }, {
            "data": "office"
        }, {
            "data": "salary"
        }],
            "order": [
                [1, 'asc']
            ]
    });
    $('#API-chield-row tbody').on('click', 'td.details-control', function() {
        var tr = $(this).closest('tr');
        var row = ct.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(format(row.data())).show();
            tr.addClass('shown');
        }
    });
    //chield row multiple data table end here
    //row select multiple data table start here
    var srow = $('#row-select-multiple').DataTable();
    $('#row-select-multiple tbody').on('click', 'tr', function() {
        $(this).toggleClass('selected');
    });
    $('#multiple-row-select-btn').on('click', function() {
        alert(srow.rows('.selected').data().length + ' row(s) selected');
    });
    //row select multiple data table end here
    //single row delete data table start here        
    var deleterow = $('#row-select-delete').DataTable();
    $('#row-select-delete tbody').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            deleterow.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });
    $('#single-row-delete-btn').on('click', function() {
        deleterow.row('.selected').remove().draw(!1);
    });
    //single row delete data table end here        
    //form input submit start here
    var table = $('#form-input-datatable').DataTable();
    $('#form-input-datatable-submit').on('click', function() {
        var data = table.$('input, select').serialize();
        alert("The following data would have been submitted to the server: \n\n" + data.substr(0, 120) + '...');
        return false;
    });
    //form input submit end here
    //show hide column start here
    var sh = $('#show-hide-datatable').DataTable({
        "scrollY": "200px",
        "paging": false
    });
    //show hide column end here
    //seach API regular expression start
    function filterGlobal() {
        $('#search-api-datatable').DataTable().search($('#g-filter').val(), $('#global_regex').prop('checked'), $('#global_smart').prop('checked')).draw();
    }
    function filterColumn(i) {
        $('#search-api-datatable').DataTable().column(i).search($('#col' + i + '_filter').val(), $('#col' + i + '_regex').prop('checked'), $('#col' + i + '_smart').prop('checked')).draw();
    }
    $('#search-api-datatable').DataTable();
    $('input.g-filter').on('keyup click', function() {
        filterGlobal();
    });
    $('input.column_filter').on('keyup click', function() {
        filterColumn($(this).parents('tr').attr('data-column'));
    });
    //seach API regular expression start
    //Ajax Data Source (Arrays) start 


    const fileCellCache = new Map();

    function sanitizeFilename(name) {
        // Basic cleanup for filesystem-unsafe characters
        const cleaned = String(name || "").trim().replace(/[\\/:*?"<>|]+/g, "_") || "download.html";
        // Ensure it has an extension (default .html)
        return /\.[a-z0-9]+$/i.test(cleaned) ? cleaned : `${cleaned}.html`;
    }

    function resolveFilename(col, data, row) {
        if (!col || col.filename == null) return "download.html";

        // Function form: col.filename(data, row) -> string
        if (typeof col.filename === "function") {
            try {
                const out = col.filename(data, row);
                return sanitizeFilename(out);
            } catch {
                return "download.html";
            }
        }

        // String form: prefer data[col.filename] if available, else literal string
        if (typeof col.filename === "string") {
            const candidate = (data && typeof data === "object" && col.filename in data)
                ? data[col.filename]
                : col.filename;
            return sanitizeFilename(candidate);
        }

        return "download.html";
    }

    function downloadBlob(blob, filename) {
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = filename || "download.html";
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            URL.revokeObjectURL(a.href);
            a.remove();
        }, 0);
    }

    document.addEventListener("click", async (e) => {
        const el = e.target.closest(".dt-open-file");
        if (!el) return;
        e.preventDefault();

        const id = el.getAttribute("data-id");
        const item = fileCellCache.get(id);
        if (!item) return;

        const filename = item.filename || "download.html";
        if (item.kind === "url") {
            // Try fetch -> blob -> download (CORS may block)
            try {
                const resp = await fetch(item.value, { credentials: "include" });
                const blob = await resp.blob();
                downloadBlob(blob, filename);
            } catch {
                // Fallback: direct download attempt (browser/CORS dependent)
                const a = document.createElement("a");
                a.href = item.value;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
            }
            return;
        }
        // Inline content → treat as HTML by default (matches requested .html filename)
        // If filename contains .pdf, treat as base64 PDF
        let blob;
        if (filename.toLowerCase().endsWith('.pdf')) {
            // Assume item.value is base64-encoded PDF string
            const byteCharacters = atob(item.value);
            const byteNumbers = new Array(byteCharacters.length);
            for (let i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            const byteArray = new Uint8Array(byteNumbers);
            blob = new Blob([byteArray], { type: "application/pdf" });
        } else {
            blob = new Blob([item.value], { type: "text/html;charset=utf-8" });
        }
        downloadBlob(blob, filename);
    });

    window.LEAD_EDITOR = window.LEAD_EDITOR || 'lead.php';
    window.LEADS_ENDPOINT = window.LEADS_ENDPOINT || 'https://api.coherta.com/pipeline/all/leads';
    const usersById = new Map(
        (window.users || users || []).map(u => [String(u.id), u])
    );
    function getKnownUserById(id) {
        if (!id || String(id) === "0") return null;
        const key = String(id);
        if (usersById.has(key)) return usersById.get(key);
        const latestUsers = Array.isArray(window.users) ? window.users : (typeof users !== "undefined" && Array.isArray(users) ? users : []);
        const user = latestUsers.find(u => String(u.id || "") === key);
        if (user) usersById.set(key, user);
        return user || null;
    }
    function formatFirstAndLastName(name) {
        const cleaned = String(name || "").trim().replace(/\s+/g, " ");
        if (!cleaned || cleaned.includes("@")) return cleaned;
        const parts = cleaned.split(" ");
        if (parts.length <= 2) return cleaned;
        return `${parts[0]} ${parts[parts.length - 1]}`;
    }
    function resolveOwnerName(data, row) {
        const ownerValue = row?.owner ?? data ?? "";
        const ownerIsObject = typeof ownerValue === "object" && ownerValue !== null;
        const ownerPrimitive = ownerIsObject ? "" : String(ownerValue || "");
        const ownerPrimitiveLooksLikeId = ownerPrimitive !== "" && /^[0-9]+$/.test(ownerPrimitive);
        const ownerId = row?.owner_id || row?.lead_owner_id || row?.assigned_user_id || (ownerIsObject ? (ownerValue.id || "") : (ownerPrimitiveLooksLikeId ? ownerPrimitive : ""));
        const fallbackName = row?.owner_name
            || row?.owner_full_name
            || row?.owner_email
            || row?.lead_owner
            || row?.lead_owner_name
            || (ownerIsObject ? (ownerValue.name || ownerValue.full_name || ownerValue.email || "") : (ownerPrimitiveLooksLikeId ? "" : ownerPrimitive));
        const owner = getKnownUserById(ownerId);
        const ownerName = owner
            ? ((owner.first_name && owner.last_name) ? `${owner.first_name} ${owner.last_name}` : (owner.full_name || owner.name || owner.email || ""))
            : "";
        return formatFirstAndLastName(ownerName || fallbackName) || "Unassigned";
    }

    $(function () {
        const customRenderers = window.LEADS_COLUMN_RENDERERS || {};

        function escapeHtml(value) {
            return $('<div>').text(value || '').html();
        }

        function getLeadColumnLabel(col) {
            if (col && col.label) return col.label;
            return String(col?.key || '').replace(/[._]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        }

        function normalizeLeadColumnConfig(inputColumns) {
            const availableColumns = Array.isArray(window.LEADS_ALL_COLUMNS) ? window.LEADS_ALL_COLUMNS : [];
            const byKey = new Map(availableColumns.map(col => [String(col.key), col]));
            const list = Array.isArray(inputColumns) ? inputColumns : [];
            return list.map(item => {
                const key = typeof item === 'string' ? item : item?.key;
                if (!key) return null;
                return Object.assign({}, byKey.get(String(key)) || {}, typeof item === 'object' ? item : {}, { key });
            }).filter(Boolean);
        }

        function renderLeadTableHeaders(activeColumnList) {
            const tableEl = document.getElementById('ajax-data-array');
            if (!tableEl) return;
            const headerCells = activeColumnList.map(col => `<th>${escapeHtml(getLeadColumnLabel(col))}</th>`).join('');
            const actionCell = window.LEADS_SHOW_ACTIONS !== false ? '<th></th>' : '';
            tableEl.innerHTML = `<thead><tr>${headerCells}${actionCell}</tr></thead><tfoot><tr>${headerCells}${actionCell}</tr></tfoot><tbody></tbody>`;
        }

        function buildDataTableColumns(activeColumnList) {
            const builtColumns = activeColumnList.map(col => {
                if (window.LeadContactEditor && ['company_phone', 'company_email', 'key_employee.employee.phone', 'key_employee.employee.email', 'phone_number', 'email'].includes(col.key)) {
                    return {
                        data: col.key,
                        defaultContent: '',
                        render: (data, type, row) => window.LeadContactEditor.renderCell(data, type, row, col.key)
                    };
                }
                if (typeof customRenderers[col.key] === "function") {
                    return {
                        data: col.key,
                        orderable: col.orderable !== false,
                        render: customRenderers[col.key]
                    };
                }
                if (col.type === "user_images") {
                    return {
                        data: col.key, // string like "1,103,158"
                        orderable: true,
                        render: (data, type) => {
                            console.log(data);
                            console.log(type);
                            if (type !== "display") return data || "";
                            if (!data) return "—";
                            const ids = data.split(",").map(x => x.trim()).filter(Boolean);
                            return ids.map(id => {
                                const u = usersById.get(String(id));
                                if (!u) return "";
                                if (!u.img_key) return `<span title="${u.full_name}">${u.full_name}</span>`;
                                return `<img 
                                    src="https://api.coherta.com/images/${u.img_key}" 
                                    title="${u.full_name}"
                                    alt="${u.full_name}"
                                    style="width:22px;height:22px;border-radius:50%;object-fit:cover;margin-right:4px;"
                                />`;
                            }).join("");
                        }
                    }
                }
                if (col.type === "combine") {
                    return {
                        data: null,
                        render: data => `${data[col.combine_with] || ""}${data[col.key] || ""}`.trim()
                    };
                }
                if (col.type === "enum") {
                    return {
                        data: col.key,
                        render: data => enum_stages[data] || data || '—'
                    };
                }
                if (col.type === "owner") {
                    return {
                        data: col.key,
                        orderable: true,
                        render: (data, type, row) => {
                            const ownerName = resolveOwnerName(data, row);
                            if (type !== "display") return ownerName;
                            return $("<div>").text(ownerName).html();
                        }
                    };
                }
                if (col.type === "array") {
                    return {
                        data: col.key,
                        render: (data, type, row) => {
                            if (data && col.subkey && data[col.subkey] !== undefined) {
                                return data[col.subkey]
                            } else {
                                return "";
                            }
                        }
                    };
                }
                if (col.type === "date") {
                    return {
                        data: col.key,
                        render: (data, type) => {
                            if (!data) return type === 'display' ? '—' : 0;
                            const t = new Date(data).getTime();
                            if (type === 'sort' || type === 'type') return t;
                            return new Date(t).toLocaleDateString('en-GB');
                        }
                    };
                }
                if (col.key === "company_name") {
                    return {
                        data: col.key,
                        render: (data, type, row) => {
                            if (!data) return '—';
                            return `<a target="_blank" href="${window.LEAD_EDITOR}?id=${row.id}">${data}</a>`;
                        }
                    };
                }
                if (col.key === "key_employee.employee.phone") {
                    return {
                        data: col.key,
                        render: (data, type, row) => {
                            const id = row?.key_employee?.employee?.id || "";
                            let direct = row?.key_employee?.employee?.phone || "";
                            const secondary = row?.key_employee?.employee?.phone2 || "";

                            const linkedin = row?.key_employee?.employee?.linkedin || "";

                            if (!direct && !secondary && !linkedin) return '—';

                            if (direct) return direct;
                            if (!direct && secondary) return secondary;

                            if (!direct && !secondary && linkedin) return `<button data-company_uid="${row?.uid}" data-id='${id}' class='btn btn-info text-black-100 checkFullEnrich'>Check FullEnrich</button>`;
                        },
                    };
                }
                if (col.key === "key_employee.employee.linkedin") {
                    return {
                        data: col.key,
                        render: (data, type, row) => {
                            if (!data) return '—';
                            return `<a target="_blank" href="${data}">${data}</a>`;
                        }
                    };
                }
                if (col.type === "file") {
                    return {
                        data: col.key,
                        orderable: false,
                        render: (data, type, row) => {
                            if (type !== "display") return "";

                            const val = data && col.subkey ? data[col.subkey] : null;
                            if (!val) return "";

                            const id = (crypto?.randomUUID?.() || Math.random().toString(36).slice(2));
                            const filename = resolveFilename(col, data, row); // <-- from col.filename

                            if (typeof val === "string" && (/^https?:\/\//i.test(val) || val.startsWith("/"))) {
                                fileCellCache.set(id, { kind: "url", value: val, filename });
                            } else {
                                fileCellCache.set(id, { kind: "blob", value: String(val), filename });
                            }

                            return `<a href="#" class="dt-open-file" data-id="${id}">Download file</a>`;
                        }
                    };
                }


                return { data: col.key };
            });
            if (window.LEADS_SHOW_ACTIONS !== false) {
                builtColumns.push({
                    data: null,
                orderable: false,
                searchable: false,
                render: (data) => `
        <ul class="action">
          <li class="hurry"><a data-id="${data.id}" class="move-first-in-queue" href="${window.LEADS_ENDPOINT}?action=queue&id=${data.id}&pipeline_id=${data.pipeline_id}"><?xml version="1.0" encoding="UTF-8"?>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="17px" height="16px" viewBox="0 0 16 16" version="1.1">
            <g id="surface1">
            <path style=" stroke:none;fill-rule:nonzero;fill:rgb(0%,0%,0%);fill-opacity:1;" d="M 9.496094 9.085938 C 9.757812 9.242188 9.917969 9.457031 10 9.75 C 10.007812 9.832031 10.011719 9.910156 10.011719 9.992188 C 10.011719 10.015625 10.011719 10.039062 10.011719 10.0625 C 10.007812 10.21875 9.980469 10.339844 9.902344 10.476562 C 9.867188 10.535156 9.867188 10.535156 9.875 10.625 C 9.9375 10.679688 9.9375 10.679688 10.015625 10.734375 C 10.269531 10.929688 10.425781 11.160156 10.5 11.46875 C 10.511719 11.597656 10.515625 11.726562 10.511719 11.855469 C 10.515625 11.890625 10.515625 11.925781 10.515625 11.964844 C 10.515625 12.039062 10.515625 12.113281 10.515625 12.191406 C 10.511719 12.308594 10.515625 12.421875 10.515625 12.539062 C 10.515625 12.613281 10.515625 12.6875 10.515625 12.761719 C 10.515625 12.796875 10.515625 12.828125 10.515625 12.867188 C 10.515625 12.914062 10.515625 12.914062 10.515625 12.964844 C 10.515625 12.992188 10.515625 13.019531 10.515625 13.050781 C 10.496094 13.148438 10.453125 13.183594 10.375 13.25 C 10.296875 13.296875 10.296875 13.296875 10.214844 13.335938 C 10.1875 13.347656 10.160156 13.363281 10.132812 13.375 C 10.0625 13.40625 10.0625 13.40625 10 13.40625 C 10 13.429688 10 13.453125 10 13.480469 C 10.003906 13.710938 10.003906 13.941406 10.007812 14.167969 C 10.007812 14.289062 10.007812 14.40625 10.007812 14.523438 C 10.011719 14.636719 10.011719 14.753906 10.011719 14.867188 C 10.011719 14.910156 10.011719 14.953125 10.011719 14.996094 C 10.015625 15.058594 10.015625 15.117188 10.015625 15.179688 C 10.015625 15.214844 10.015625 15.25 10.015625 15.285156 C 10 15.375 10 15.375 9.949219 15.449219 C 9.859375 15.511719 9.808594 15.515625 9.703125 15.515625 C 9.667969 15.515625 9.632812 15.515625 9.597656 15.515625 C 9.542969 15.515625 9.542969 15.515625 9.484375 15.515625 C 9.425781 15.515625 9.425781 15.515625 9.367188 15.515625 C 9.285156 15.515625 9.207031 15.515625 9.125 15.515625 C 9 15.515625 8.875 15.515625 8.75 15.515625 C 8.671875 15.515625 8.59375 15.515625 8.515625 15.515625 C 8.476562 15.515625 8.441406 15.515625 8.402344 15.515625 C 8.351562 15.515625 8.351562 15.515625 8.296875 15.515625 C 8.265625 15.515625 8.238281 15.515625 8.207031 15.515625 C 8.097656 15.496094 8.0625 15.464844 8 15.375 C 7.984375 15.285156 7.984375 15.285156 7.984375 15.179688 C 7.984375 15.140625 7.984375 15.101562 7.984375 15.0625 C 7.988281 15.019531 7.988281 14.976562 7.988281 14.933594 C 7.988281 14.886719 7.988281 14.84375 7.988281 14.800781 C 7.988281 14.683594 7.988281 14.570312 7.992188 14.453125 C 7.992188 14.335938 7.992188 14.21875 7.992188 14.101562 C 7.996094 13.867188 7.996094 13.636719 8 13.40625 C 7.96875 13.398438 7.941406 13.386719 7.910156 13.378906 C 7.820312 13.347656 7.746094 13.3125 7.664062 13.265625 C 7.636719 13.253906 7.613281 13.238281 7.589844 13.226562 C 7.53125 13.1875 7.53125 13.1875 7.5 13.125 C 7.496094 13.074219 7.496094 13.023438 7.496094 12.972656 C 7.492188 12.9375 7.492188 12.90625 7.492188 12.875 C 7.492188 12.839844 7.492188 12.804688 7.492188 12.769531 C 7.492188 12.734375 7.492188 12.695312 7.492188 12.660156 C 7.492188 12.582031 7.492188 12.507812 7.492188 12.429688 C 7.492188 12.316406 7.488281 12.199219 7.488281 12.085938 C 7.488281 12.011719 7.488281 11.9375 7.488281 11.863281 C 7.484375 11.828125 7.484375 11.792969 7.484375 11.757812 C 7.484375 11.398438 7.589844 11.105469 7.84375 10.84375 C 7.90625 10.789062 7.972656 10.742188 8.039062 10.695312 C 8.097656 10.65625 8.097656 10.65625 8.15625 10.59375 C 8.144531 10.566406 8.144531 10.566406 8.128906 10.542969 C 8.117188 10.519531 8.105469 10.496094 8.09375 10.476562 C 8.085938 10.453125 8.074219 10.429688 8.0625 10.40625 C 7.957031 10.1875 7.96875 9.890625 8.019531 9.65625 C 8.125 9.382812 8.300781 9.191406 8.5625 9.0625 C 8.847656 8.949219 9.21875 8.953125 9.496094 9.085938 Z M 8.582031 9.738281 C 8.511719 9.882812 8.507812 10 8.53125 10.15625 C 8.605469 10.308594 8.691406 10.394531 8.84375 10.46875 C 9 10.492188 9.117188 10.488281 9.261719 10.417969 C 9.375 10.316406 9.457031 10.234375 9.480469 10.082031 C 9.488281 9.902344 9.464844 9.792969 9.34375 9.65625 C 9.207031 9.535156 9.097656 9.511719 8.917969 9.519531 C 8.765625 9.542969 8.683594 9.625 8.582031 9.738281 Z M 8.230469 11.238281 C 8.019531 11.472656 7.996094 11.699219 7.996094 12.007812 C 7.996094 12.03125 7.996094 12.054688 7.996094 12.082031 C 7.996094 12.15625 7.996094 12.234375 8 12.308594 C 8 12.359375 8 12.414062 8 12.464844 C 8 12.589844 8 12.71875 8 12.84375 C 8.046875 12.867188 8.046875 12.867188 8.09375 12.890625 C 8.136719 12.910156 8.175781 12.929688 8.21875 12.949219 C 8.246094 12.964844 8.246094 12.964844 8.277344 12.980469 C 8.433594 13.054688 8.433594 13.054688 8.46875 13.125 C 8.472656 13.183594 8.472656 13.246094 8.476562 13.304688 C 8.476562 13.339844 8.476562 13.378906 8.476562 13.417969 C 8.476562 13.457031 8.476562 13.5 8.480469 13.542969 C 8.480469 13.582031 8.480469 13.625 8.480469 13.667969 C 8.480469 13.777344 8.484375 13.886719 8.484375 14 C 8.488281 14.109375 8.488281 14.222656 8.492188 14.335938 C 8.492188 14.558594 8.496094 14.777344 8.5 15 C 8.828125 15 9.160156 15 9.5 15 C 9.5 14.867188 9.503906 14.730469 9.503906 14.59375 C 9.503906 14.484375 9.507812 14.378906 9.507812 14.273438 C 9.507812 14.160156 9.511719 14.046875 9.511719 13.933594 C 9.511719 13.824219 9.511719 13.714844 9.515625 13.605469 C 9.515625 13.546875 9.515625 13.484375 9.515625 13.421875 C 9.515625 13.382812 9.515625 13.347656 9.519531 13.308594 C 9.519531 13.273438 9.519531 13.242188 9.519531 13.207031 C 9.53125 13.113281 9.550781 13.085938 9.625 13.03125 C 9.6875 12.996094 9.753906 12.964844 9.820312 12.929688 C 9.851562 12.914062 9.886719 12.898438 9.921875 12.882812 C 9.945312 12.867188 9.972656 12.855469 10 12.84375 C 10 12.691406 10.003906 12.542969 10.003906 12.390625 C 10.003906 12.339844 10.003906 12.289062 10.003906 12.238281 C 10.023438 11.730469 10.023438 11.730469 9.8125 11.28125 C 9.554688 11.023438 9.296875 10.992188 8.9375 10.996094 C 8.65625 11 8.4375 11.03125 8.230469 11.238281 Z M 8.230469 11.238281 "/>
            <path style=" stroke:none;fill-rule:nonzero;fill:rgb(0%,0%,0%);fill-opacity:1;" d="M 5.996094 9.085938 C 6.257812 9.242188 6.417969 9.457031 6.5 9.75 C 6.507812 9.832031 6.511719 9.910156 6.511719 9.992188 C 6.511719 10.015625 6.511719 10.039062 6.511719 10.0625 C 6.507812 10.21875 6.480469 10.339844 6.402344 10.476562 C 6.367188 10.535156 6.367188 10.535156 6.375 10.625 C 6.4375 10.679688 6.4375 10.679688 6.515625 10.734375 C 6.769531 10.929688 6.925781 11.160156 7 11.46875 C 7.011719 11.597656 7.015625 11.726562 7.011719 11.855469 C 7.015625 11.890625 7.015625 11.925781 7.015625 11.964844 C 7.015625 12.039062 7.015625 12.113281 7.015625 12.191406 C 7.011719 12.308594 7.015625 12.421875 7.015625 12.539062 C 7.015625 12.613281 7.015625 12.6875 7.015625 12.761719 C 7.015625 12.796875 7.015625 12.828125 7.015625 12.867188 C 7.015625 12.914062 7.015625 12.914062 7.015625 12.964844 C 7.015625 12.992188 7.015625 13.019531 7.015625 13.050781 C 6.996094 13.148438 6.953125 13.183594 6.875 13.25 C 6.796875 13.296875 6.796875 13.296875 6.714844 13.335938 C 6.6875 13.347656 6.660156 13.363281 6.632812 13.375 C 6.5625 13.40625 6.5625 13.40625 6.5 13.40625 C 6.5 13.429688 6.5 13.453125 6.5 13.480469 C 6.503906 13.710938 6.503906 13.941406 6.507812 14.167969 C 6.507812 14.289062 6.507812 14.40625 6.507812 14.523438 C 6.511719 14.636719 6.511719 14.753906 6.511719 14.867188 C 6.511719 14.910156 6.511719 14.953125 6.511719 14.996094 C 6.515625 15.058594 6.515625 15.117188 6.515625 15.179688 C 6.515625 15.214844 6.515625 15.25 6.515625 15.285156 C 6.5 15.375 6.5 15.375 6.449219 15.449219 C 6.359375 15.511719 6.308594 15.515625 6.203125 15.515625 C 6.167969 15.515625 6.132812 15.515625 6.097656 15.515625 C 6.042969 15.515625 6.042969 15.515625 5.984375 15.515625 C 5.925781 15.515625 5.925781 15.515625 5.867188 15.515625 C 5.785156 15.515625 5.707031 15.515625 5.625 15.515625 C 5.5 15.515625 5.375 15.515625 5.25 15.515625 C 5.171875 15.515625 5.09375 15.515625 5.015625 15.515625 C 4.976562 15.515625 4.941406 15.515625 4.902344 15.515625 C 4.851562 15.515625 4.851562 15.515625 4.796875 15.515625 C 4.765625 15.515625 4.738281 15.515625 4.707031 15.515625 C 4.597656 15.496094 4.5625 15.464844 4.5 15.375 C 4.484375 15.285156 4.484375 15.285156 4.484375 15.179688 C 4.484375 15.140625 4.484375 15.101562 4.484375 15.0625 C 4.488281 15.019531 4.488281 14.976562 4.488281 14.933594 C 4.488281 14.886719 4.488281 14.84375 4.488281 14.800781 C 4.488281 14.683594 4.488281 14.570312 4.492188 14.453125 C 4.492188 14.335938 4.492188 14.21875 4.492188 14.101562 C 4.496094 13.867188 4.496094 13.636719 4.5 13.40625 C 4.46875 13.398438 4.441406 13.386719 4.410156 13.378906 C 4.320312 13.347656 4.246094 13.3125 4.164062 13.265625 C 4.136719 13.253906 4.113281 13.238281 4.089844 13.226562 C 4.03125 13.1875 4.03125 13.1875 4 13.125 C 3.996094 13.074219 3.996094 13.023438 3.996094 12.972656 C 3.992188 12.9375 3.992188 12.90625 3.992188 12.875 C 3.992188 12.839844 3.992188 12.804688 3.992188 12.769531 C 3.992188 12.734375 3.992188 12.695312 3.992188 12.660156 C 3.992188 12.582031 3.992188 12.507812 3.992188 12.429688 C 3.992188 12.316406 3.988281 12.199219 3.988281 12.085938 C 3.988281 12.011719 3.988281 11.9375 3.988281 11.863281 C 3.984375 11.828125 3.984375 11.792969 3.984375 11.757812 C 3.984375 11.398438 4.089844 11.105469 4.34375 10.84375 C 4.40625 10.789062 4.472656 10.742188 4.539062 10.695312 C 4.597656 10.65625 4.597656 10.65625 4.65625 10.59375 C 4.644531 10.566406 4.644531 10.566406 4.628906 10.542969 C 4.617188 10.519531 4.605469 10.496094 4.59375 10.476562 C 4.585938 10.453125 4.574219 10.429688 4.5625 10.40625 C 4.457031 10.1875 4.46875 9.890625 4.519531 9.65625 C 4.625 9.382812 4.800781 9.191406 5.0625 9.0625 C 5.347656 8.949219 5.71875 8.953125 5.996094 9.085938 Z M 5.082031 9.738281 C 5.011719 9.882812 5.007812 10 5.03125 10.15625 C 5.105469 10.308594 5.191406 10.394531 5.34375 10.46875 C 5.5 10.492188 5.617188 10.488281 5.761719 10.417969 C 5.875 10.316406 5.957031 10.234375 5.980469 10.082031 C 5.988281 9.902344 5.964844 9.792969 5.84375 9.65625 C 5.707031 9.535156 5.597656 9.511719 5.417969 9.519531 C 5.265625 9.542969 5.183594 9.625 5.082031 9.738281 Z M 4.730469 11.238281 C 4.519531 11.472656 4.496094 11.699219 4.496094 12.007812 C 4.496094 12.03125 4.496094 12.054688 4.496094 12.082031 C 4.496094 12.15625 4.496094 12.234375 4.5 12.308594 C 4.5 12.359375 4.5 12.414062 4.5 12.464844 C 4.5 12.589844 4.5 12.71875 4.5 12.84375 C 4.546875 12.867188 4.546875 12.867188 4.59375 12.890625 C 4.636719 12.910156 4.675781 12.929688 4.71875 12.949219 C 4.746094 12.964844 4.746094 12.964844 4.777344 12.980469 C 4.933594 13.054688 4.933594 13.054688 4.96875 13.125 C 4.972656 13.183594 4.972656 13.246094 4.976562 13.304688 C 4.976562 13.339844 4.976562 13.378906 4.976562 13.417969 C 4.976562 13.457031 4.976562 13.5 4.980469 13.542969 C 4.980469 13.582031 4.980469 13.625 4.980469 13.667969 C 4.980469 13.777344 4.984375 13.886719 4.984375 14 C 4.988281 14.109375 4.988281 14.222656 4.992188 14.335938 C 4.992188 14.558594 4.996094 14.777344 5 15 C 5.328125 15 5.660156 15 6 15 C 6 14.867188 6.003906 14.730469 6.003906 14.59375 C 6.003906 14.484375 6.007812 14.378906 6.007812 14.273438 C 6.007812 14.160156 6.011719 14.046875 6.011719 13.933594 C 6.011719 13.824219 6.011719 13.714844 6.015625 13.605469 C 6.015625 13.546875 6.015625 13.484375 6.015625 13.421875 C 6.015625 13.382812 6.015625 13.347656 6.019531 13.308594 C 6.019531 13.273438 6.019531 13.242188 6.019531 13.207031 C 6.03125 13.113281 6.050781 13.085938 6.125 13.03125 C 6.1875 12.996094 6.253906 12.964844 6.320312 12.929688 C 6.351562 12.914062 6.386719 12.898438 6.421875 12.882812 C 6.445312 12.867188 6.472656 12.855469 6.5 12.84375 C 6.5 12.691406 6.503906 12.542969 6.503906 12.390625 C 6.503906 12.339844 6.503906 12.289062 6.503906 12.238281 C 6.523438 11.730469 6.523438 11.730469 6.3125 11.28125 C 6.054688 11.023438 5.796875 10.992188 5.4375 10.996094 C 5.15625 11 4.9375 11.03125 4.730469 11.238281 Z M 4.730469 11.238281 "/>
            <path style=" stroke:none;fill-rule:nonzero;fill:rgb(0%,0%,0%);fill-opacity:1;" d="M 2.496094 9.085938 C 2.757812 9.242188 2.917969 9.457031 3 9.75 C 3.007812 9.832031 3.011719 9.910156 3.011719 9.992188 C 3.011719 10.015625 3.011719 10.039062 3.011719 10.0625 C 3.007812 10.21875 2.980469 10.339844 2.902344 10.476562 C 2.867188 10.535156 2.867188 10.535156 2.875 10.625 C 2.9375 10.679688 2.9375 10.679688 3.015625 10.734375 C 3.269531 10.929688 3.425781 11.160156 3.5 11.46875 C 3.511719 11.597656 3.515625 11.726562 3.511719 11.855469 C 3.515625 11.890625 3.515625 11.925781 3.515625 11.964844 C 3.515625 12.039062 3.515625 12.113281 3.515625 12.191406 C 3.511719 12.308594 3.515625 12.421875 3.515625 12.539062 C 3.515625 12.613281 3.515625 12.6875 3.515625 12.761719 C 3.515625 12.796875 3.515625 12.828125 3.515625 12.867188 C 3.515625 12.914062 3.515625 12.914062 3.515625 12.964844 C 3.515625 12.992188 3.515625 13.019531 3.515625 13.050781 C 3.496094 13.148438 3.453125 13.183594 3.375 13.25 C 3.296875 13.296875 3.296875 13.296875 3.214844 13.335938 C 3.1875 13.347656 3.160156 13.363281 3.132812 13.375 C 3.0625 13.40625 3.0625 13.40625 3 13.40625 C 3 13.429688 3 13.453125 3 13.480469 C 3.003906 13.710938 3.003906 13.941406 3.007812 14.167969 C 3.007812 14.289062 3.007812 14.40625 3.007812 14.523438 C 3.011719 14.636719 3.011719 14.753906 3.011719 14.867188 C 3.011719 14.910156 3.011719 14.953125 3.011719 14.996094 C 3.015625 15.058594 3.015625 15.117188 3.015625 15.179688 C 3.015625 15.214844 3.015625 15.25 3.015625 15.285156 C 3 15.375 3 15.375 2.949219 15.449219 C 2.859375 15.511719 2.808594 15.515625 2.703125 15.515625 C 2.667969 15.515625 2.632812 15.515625 2.597656 15.515625 C 2.542969 15.515625 2.542969 15.515625 2.484375 15.515625 C 2.425781 15.515625 2.425781 15.515625 2.367188 15.515625 C 2.285156 15.515625 2.207031 15.515625 2.125 15.515625 C 2 15.515625 1.875 15.515625 1.75 15.515625 C 1.671875 15.515625 1.59375 15.515625 1.515625 15.515625 C 1.476562 15.515625 1.441406 15.515625 1.402344 15.515625 C 1.351562 15.515625 1.351562 15.515625 1.296875 15.515625 C 1.265625 15.515625 1.238281 15.515625 1.207031 15.515625 C 1.097656 15.496094 1.0625 15.464844 1 15.375 C 0.984375 15.285156 0.984375 15.285156 0.984375 15.179688 C 0.984375 15.140625 0.984375 15.101562 0.984375 15.0625 C 0.988281 15.019531 0.988281 14.976562 0.988281 14.933594 C 0.988281 14.886719 0.988281 14.84375 0.988281 14.800781 C 0.988281 14.683594 0.988281 14.570312 0.992188 14.453125 C 0.992188 14.335938 0.992188 14.21875 0.992188 14.101562 C 0.996094 13.867188 0.996094 13.636719 1 13.40625 C 0.96875 13.398438 0.941406 13.386719 0.910156 13.378906 C 0.820312 13.347656 0.746094 13.3125 0.660156 13.265625 C 0.636719 13.253906 0.613281 13.238281 0.589844 13.226562 C 0.53125 13.1875 0.53125 13.1875 0.5 13.125 C 0.496094 13.074219 0.496094 13.023438 0.496094 12.972656 C 0.492188 12.9375 0.492188 12.90625 0.492188 12.875 C 0.492188 12.839844 0.492188 12.804688 0.492188 12.769531 C 0.492188 12.734375 0.492188 12.695312 0.492188 12.660156 C 0.492188 12.582031 0.492188 12.507812 0.492188 12.429688 C 0.492188 12.316406 0.488281 12.199219 0.488281 12.085938 C 0.488281 12.011719 0.488281 11.9375 0.488281 11.863281 C 0.484375 11.828125 0.484375 11.792969 0.484375 11.757812 C 0.484375 11.398438 0.589844 11.105469 0.84375 10.84375 C 0.90625 10.789062 0.972656 10.742188 1.039062 10.695312 C 1.097656 10.65625 1.097656 10.65625 1.15625 10.59375 C 1.144531 10.566406 1.144531 10.566406 1.128906 10.542969 C 1.117188 10.519531 1.105469 10.496094 1.09375 10.476562 C 1.085938 10.453125 1.074219 10.429688 1.0625 10.40625 C 0.957031 10.1875 0.96875 9.890625 1.019531 9.65625 C 1.125 9.382812 1.300781 9.191406 1.5625 9.0625 C 1.847656 8.949219 2.21875 8.953125 2.496094 9.085938 Z M 1.582031 9.738281 C 1.511719 9.882812 1.507812 10 1.53125 10.15625 C 1.605469 10.308594 1.691406 10.394531 1.84375 10.46875 C 2 10.492188 2.117188 10.488281 2.261719 10.417969 C 2.375 10.316406 2.457031 10.234375 2.480469 10.082031 C 2.488281 9.902344 2.464844 9.792969 2.34375 9.65625 C 2.207031 9.535156 2.097656 9.511719 1.917969 9.519531 C 1.765625 9.542969 1.683594 9.625 1.582031 9.738281 Z M 1.230469 11.238281 C 1.019531 11.472656 0.996094 11.699219 0.996094 12.007812 C 0.996094 12.03125 0.996094 12.054688 0.996094 12.082031 C 0.996094 12.15625 0.996094 12.234375 1 12.308594 C 1 12.359375 1 12.414062 1 12.464844 C 1 12.589844 1 12.71875 1 12.84375 C 1.046875 12.867188 1.046875 12.867188 1.09375 12.890625 C 1.136719 12.910156 1.175781 12.929688 1.21875 12.949219 C 1.246094 12.964844 1.246094 12.964844 1.277344 12.980469 C 1.433594 13.054688 1.433594 13.054688 1.46875 13.125 C 1.472656 13.183594 1.472656 13.246094 1.476562 13.304688 C 1.476562 13.339844 1.476562 13.378906 1.476562 13.417969 C 1.476562 13.457031 1.476562 13.5 1.480469 13.542969 C 1.480469 13.582031 1.480469 13.625 1.480469 13.667969 C 1.480469 13.777344 1.484375 13.886719 1.484375 14 C 1.488281 14.109375 1.488281 14.222656 1.492188 14.335938 C 1.492188 14.558594 1.496094 14.777344 1.5 15 C 1.828125 15 2.160156 15 2.5 15 C 2.5 14.867188 2.503906 14.730469 2.503906 14.59375 C 2.503906 14.484375 2.507812 14.378906 2.507812 14.273438 C 2.507812 14.160156 2.511719 14.046875 2.511719 13.933594 C 2.511719 13.824219 2.511719 13.714844 2.515625 13.605469 C 2.515625 13.546875 2.515625 13.484375 2.515625 13.421875 C 2.515625 13.382812 2.515625 13.347656 2.519531 13.308594 C 2.519531 13.273438 2.519531 13.242188 2.519531 13.207031 C 2.53125 13.113281 2.550781 13.085938 2.625 13.03125 C 2.6875 12.996094 2.753906 12.964844 2.820312 12.929688 C 2.851562 12.914062 2.886719 12.898438 2.921875 12.882812 C 2.945312 12.867188 2.972656 12.855469 3 12.84375 C 3 12.691406 3.003906 12.542969 3.003906 12.390625 C 3.003906 12.339844 3.003906 12.289062 3.003906 12.238281 C 3.023438 11.730469 3.023438 11.730469 2.8125 11.28125 C 2.554688 11.023438 2.296875 10.992188 1.9375 10.996094 C 1.65625 11 1.4375 11.03125 1.230469 11.238281 Z M 1.230469 11.238281 "/>
            <path style=" stroke:none;fill-rule:nonzero;fill:rgb(0%,0%,0%);fill-opacity:1;" d="M 11.75 0.625 C 11.769531 0.632812 11.789062 0.636719 11.808594 0.644531 C 12.300781 0.8125 12.695312 1.074219 13.0625 1.4375 C 13.085938 1.457031 13.105469 1.476562 13.128906 1.5 C 13.621094 1.953125 13.96875 2.714844 14.003906 3.378906 C 14.023438 4.320312 14.023438 4.320312 13.875 4.75 C 13.867188 4.769531 13.863281 4.789062 13.855469 4.808594 C 13.6875 5.300781 13.425781 5.695312 13.0625 6.0625 C 13.042969 6.085938 13.023438 6.105469 13 6.128906 C 12.542969 6.628906 11.769531 6.972656 11.097656 7.003906 C 10.636719 7.019531 10.191406 7.019531 9.75 6.875 C 9.730469 6.867188 9.710938 6.863281 9.6875 6.855469 C 9.21875 6.695312 8.820312 6.441406 8.46875 6.09375 C 8.441406 6.066406 8.410156 6.039062 8.382812 6.011719 C 8.3125 5.9375 8.3125 5.9375 8.3125 5.84375 C 8.386719 5.773438 8.386719 5.773438 8.484375 5.695312 C 8.515625 5.671875 8.546875 5.648438 8.582031 5.621094 C 8.605469 5.601562 8.628906 5.582031 8.65625 5.5625 C 8.773438 5.609375 8.84375 5.683594 8.933594 5.769531 C 9.429688 6.226562 10.109375 6.46875 10.78125 6.46875 C 11.554688 6.4375 12.242188 6.132812 12.773438 5.566406 C 13.332031 4.917969 13.492188 4.183594 13.4375 3.34375 C 13.375 2.804688 13.101562 2.3125 12.75 1.90625 C 12.730469 1.886719 12.714844 1.863281 12.695312 1.84375 C 12.230469 1.339844 11.507812 1.0625 10.832031 1.03125 C 10 1.039062 9.300781 1.347656 8.71875 1.9375 C 8.550781 1.890625 8.4375 1.773438 8.3125 1.65625 C 8.339844 1.523438 8.402344 1.464844 8.5 1.375 C 8.519531 1.355469 8.542969 1.332031 8.5625 1.3125 C 8.902344 0.988281 9.308594 0.769531 9.75 0.625 C 9.769531 0.617188 9.789062 0.613281 9.8125 0.605469 C 10.417969 0.414062 11.144531 0.421875 11.75 0.625 Z M 11.75 0.625 "/>
            <path style=" stroke:none;fill-rule:nonzero;fill:rgb(0%,0%,0%);fill-opacity:1;" d="M 12.5625 12.988281 C 12.667969 13.007812 12.746094 13.0625 12.832031 13.125 C 12.851562 13.136719 12.871094 13.148438 12.890625 13.160156 C 12.949219 13.199219 13.003906 13.242188 13.0625 13.28125 C 13.101562 13.308594 13.140625 13.335938 13.179688 13.363281 C 13.277344 13.429688 13.375 13.496094 13.46875 13.5625 C 13.46875 13.515625 13.46875 13.515625 13.472656 13.472656 C 13.472656 13.410156 13.472656 13.410156 13.476562 13.351562 C 13.476562 13.3125 13.480469 13.273438 13.480469 13.234375 C 13.5 13.125 13.5 13.125 13.546875 13.046875 C 13.636719 12.992188 13.703125 12.976562 13.804688 12.984375 C 13.953125 13.015625 14.070312 13.109375 14.191406 13.191406 C 14.222656 13.210938 14.25 13.230469 14.28125 13.253906 C 14.375 13.316406 14.46875 13.378906 14.5625 13.445312 C 14.628906 13.484375 14.691406 13.527344 14.753906 13.570312 C 14.921875 13.679688 15.085938 13.792969 15.25 13.90625 C 15.273438 13.921875 15.292969 13.9375 15.316406 13.953125 C 15.464844 14.054688 15.464844 14.054688 15.5 14.125 C 15.515625 14.300781 15.515625 14.300781 15.5 14.375 C 15.367188 14.53125 15.199219 14.632812 15.03125 14.742188 C 14.984375 14.773438 14.984375 14.773438 14.941406 14.800781 C 14.84375 14.863281 14.75 14.925781 14.652344 14.992188 C 14.585938 15.03125 14.523438 15.074219 14.457031 15.117188 C 14.316406 15.210938 14.171875 15.304688 14.03125 15.398438 C 13.980469 15.429688 13.925781 15.464844 13.875 15.5 C 13.652344 15.519531 13.652344 15.519531 13.558594 15.449219 C 13.480469 15.347656 13.480469 15.277344 13.476562 15.148438 C 13.476562 15.109375 13.472656 15.070312 13.472656 15.027344 C 13.46875 15 13.46875 14.96875 13.46875 14.9375 C 13.445312 14.953125 13.421875 14.972656 13.394531 14.988281 C 13.304688 15.050781 13.214844 15.117188 13.121094 15.179688 C 13.082031 15.203125 13.042969 15.230469 13.003906 15.257812 C 12.949219 15.300781 12.890625 15.339844 12.832031 15.378906 C 12.816406 15.390625 12.800781 15.402344 12.78125 15.414062 C 12.636719 15.511719 12.542969 15.523438 12.375 15.5 C 12.304688 15.449219 12.304688 15.449219 12.25 15.375 C 12.234375 15.277344 12.234375 15.277344 12.234375 15.15625 C 12.234375 15.132812 12.234375 15.113281 12.234375 15.089844 C 12.234375 15.019531 12.234375 14.945312 12.234375 14.875 C 12.234375 14.824219 12.234375 14.773438 12.234375 14.726562 C 12.234375 14.621094 12.234375 14.515625 12.234375 14.410156 C 12.234375 14.277344 12.234375 14.144531 12.234375 14.007812 C 12.234375 13.90625 12.234375 13.800781 12.234375 13.699219 C 12.234375 13.648438 12.234375 13.601562 12.234375 13.550781 C 12.234375 13.480469 12.234375 13.414062 12.234375 13.34375 C 12.234375 13.304688 12.234375 13.265625 12.234375 13.226562 C 12.25 13.125 12.25 13.125 12.304688 13.050781 C 12.390625 12.988281 12.457031 12.984375 12.5625 12.988281 Z M 12.75 13.75 C 12.75 14.078125 12.75 14.410156 12.75 14.75 C 12.933594 14.648438 13.109375 14.546875 13.28125 14.433594 C 13.316406 14.410156 13.316406 14.410156 13.347656 14.390625 C 13.398438 14.355469 13.449219 14.320312 13.5 14.28125 C 13.5 14.261719 13.5 14.238281 13.5 14.21875 C 13.433594 14.164062 13.433594 14.164062 13.34375 14.109375 C 13.3125 14.089844 13.28125 14.066406 13.246094 14.046875 C 13.210938 14.023438 13.179688 14.003906 13.140625 13.980469 C 13.109375 13.957031 13.074219 13.9375 13.039062 13.914062 C 12.902344 13.808594 12.902344 13.808594 12.75 13.75 Z M 14 13.75 C 14 14.078125 14 14.410156 14 14.75 C 14.183594 14.648438 14.359375 14.546875 14.53125 14.433594 C 14.566406 14.410156 14.566406 14.410156 14.597656 14.390625 C 14.648438 14.355469 14.699219 14.320312 14.75 14.28125 C 14.75 14.261719 14.75 14.238281 14.75 14.21875 C 14.683594 14.164062 14.683594 14.164062 14.59375 14.109375 C 14.5625 14.089844 14.53125 14.066406 14.496094 14.046875 C 14.460938 14.023438 14.429688 14.003906 14.390625 13.980469 C 14.359375 13.957031 14.324219 13.9375 14.289062 13.914062 C 14.152344 13.808594 14.152344 13.808594 14 13.75 Z M 14 13.75 "/>
            <path style=" stroke:none;fill-rule:nonzero;fill:rgb(0%,0%,0%);fill-opacity:1;" d="M 2 3.5 C 3.898438 3.5 5.796875 3.5 7.75 3.5 C 7.75 3.664062 7.75 3.828125 7.75 4 C 5.851562 4 3.953125 4 2 4 C 2 3.835938 2 3.671875 2 3.5 Z M 2 3.5 "/>
            <path style=" stroke:none;fill-rule:nonzero;fill:rgb(0%,0%,0%);fill-opacity:1;" d="M 4.25 2.25 C 5.570312 2.25 6.890625 2.25 8.25 2.25 C 8.25 2.414062 8.25 2.578125 8.25 2.75 C 6.929688 2.75 5.609375 2.75 4.25 2.75 C 4.25 2.585938 4.25 2.421875 4.25 2.25 Z M 4.25 2.25 "/>
            <path style=" stroke:none;fill-rule:nonzero;fill:rgb(0%,0%,0%);fill-opacity:1;" d="M 10.5 1.75 C 10.664062 1.75 10.828125 1.75 11 1.75 C 11 1.804688 10.996094 1.859375 10.996094 1.917969 C 10.992188 2.101562 10.988281 2.28125 10.988281 2.464844 C 10.988281 2.578125 10.988281 2.6875 10.984375 2.796875 C 10.945312 3.308594 10.945312 3.308594 11.121094 3.753906 C 11.210938 3.832031 11.308594 3.890625 11.414062 3.941406 C 11.488281 3.980469 11.550781 4.03125 11.613281 4.085938 C 11.632812 4.101562 11.65625 4.117188 11.675781 4.136719 C 11.703125 4.160156 11.703125 4.160156 11.734375 4.183594 C 11.753906 4.199219 11.773438 4.214844 11.796875 4.234375 C 11.917969 4.332031 12.035156 4.429688 12.15625 4.53125 C 12.113281 4.652344 12.058594 4.738281 11.980469 4.835938 C 11.949219 4.875 11.949219 4.875 11.921875 4.914062 C 11.90625 4.929688 11.890625 4.949219 11.875 4.96875 C 11.789062 4.929688 11.71875 4.882812 11.644531 4.824219 C 11.625 4.808594 11.605469 4.789062 11.582031 4.773438 C 11.550781 4.746094 11.550781 4.746094 11.519531 4.71875 C 11.476562 4.683594 11.429688 4.648438 11.386719 4.609375 C 11.363281 4.589844 11.339844 4.574219 11.316406 4.554688 C 11.21875 4.472656 11.117188 4.394531 11.015625 4.316406 C 11 4.300781 10.980469 4.289062 10.964844 4.273438 C 10.882812 4.210938 10.804688 4.148438 10.722656 4.089844 C 10.699219 4.070312 10.671875 4.050781 10.648438 4.03125 C 10.625 4.011719 10.601562 3.996094 10.582031 3.980469 C 10.53125 3.9375 10.53125 3.9375 10.5 3.875 C 10.496094 3.808594 10.496094 3.742188 10.496094 3.671875 C 10.496094 3.652344 10.496094 3.632812 10.496094 3.609375 C 10.496094 3.542969 10.496094 3.472656 10.496094 3.402344 C 10.496094 3.355469 10.496094 3.308594 10.496094 3.261719 C 10.496094 3.136719 10.496094 3.011719 10.496094 2.886719 C 10.5 2.757812 10.5 2.628906 10.5 2.503906 C 10.5 2.25 10.5 2 10.5 1.75 Z M 10.5 1.75 "/>
            <path style=" stroke:none;fill-rule:nonzero;fill:rgb(0%,0%,0%);fill-opacity:1;" d="M 4.75 4.75 C 5.90625 4.75 7.058594 4.75 8.25 4.75 C 8.25 4.914062 8.25 5.078125 8.25 5.25 C 7.09375 5.25 5.941406 5.25 4.75 5.25 C 4.75 5.085938 4.75 4.921875 4.75 4.75 Z M 4.75 4.75 "/>
            </g>
            </svg>
            </a>
          </li>
          <li class="edit"><a target="_blank" href="${window.LEAD_EDITOR}?id=${data.id}"><i class="icon-pencil-alt"></i></a></li>
          <li class="delete"><a data-id="${data.id}" class="delete-lead" href="${window.LEADS_ENDPOINT}?action=delete&id=${data.id}&pipeline_id=${data.pipeline_id}"><i class="icon-trash"></i></a></li>
        </ul>
      `
            });
            }
            return builtColumns;
        }

        let currentLeadColumns = normalizeLeadColumnConfig(typeof activeColumns !== "undefined" ? activeColumns : []);
        let columns = buildDataTableColumns(currentLeadColumns);
        function createLeadDataTable() {
            return $('#ajax-data-array').DataTable({
            data: [],
            columns,
            deferRender: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            processing: true, // enables the overlay
            language: {
                processing: "Loading data…",   // text in the overlay
                emptyTable: "No data available" // shown when it's done & empty
            },
            fixedHeader: {
                header: true,
                headerOffset: 75 // set to your sticky navbar height if you have one
            },
            dom: 'lBfrtip',
            buttons: [
                {
                    extend: 'csvHtml5',
                    className: 'leads-hidden-export-buttons btn btn-success mb-3',
                    title: 'Export to CSV',
                    exportOptions: {
                        orthogonal: 'export'
                    }
                }, {
                    extend: 'excelHtml5',
                    text: 'Export to Excel',
                    className: 'leads-hidden-export-buttons btn btn-success mb-3',
                    exportOptions: {
                        orthogonal: 'export',
                        columns: ":visible",
                        modifier: { search: "applied", order: "applied" }
                    },
                    action: function (e, dt, button, config) {
                        if (exportOnlyFirst5) {
                            // first 5 rows in the *current display order* (after search + order)
                            const idxs = dt
                                .rows({ search: "applied", order: "applied" })
                                .indexes()
                                .toArray()
                                .slice(0, 5);

                            const prevRows = config.exportOptions?.rows;

                            config.exportOptions = config.exportOptions || {};
                            config.exportOptions.rows = idxs;

                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);

                            // restore so future exports behave normally
                            config.exportOptions.rows = prevRows;
                            return;
                        }

                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                    }

                }
            ]
            });
        }
        let table = createLeadDataTable();

        function setLoading(table, show) {
            const $wrapper = $(table.table().container());      // DataTables wrapper
            const $table   = $wrapper.find('table.dataTable');  // The actual table

            // Ensure wrapper can host an overlay
            if ($wrapper.css('position') === 'static') {
                $wrapper.css('position', 'relative');
            }

            // Create overlay once
            let $overlay = $wrapper.find('.dt-loading-overlay');
            if (!$overlay.length) {
                $overlay = $('<div class="dt-loading-overlay">Loading data…</div>').appendTo($wrapper);
                $overlay.css({
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    background: 'rgba(255, 255, 255, 0.7)',
                    fontWeight: 'bold',
                    zIndex: 10,
                    pointerEvents: 'auto'
                });
            }

            if (show) {
                // Gray out table and block interaction
                $table.css({
                    opacity: 0.3,
                    pointerEvents: 'none'
                });
                $overlay.show();
            } else {
                $table.css({
                    opacity: 1,
                    pointerEvents: ''
                });
                $overlay.hide();
            }

        }

        function setupLeadTableUi(nextTable) {
            setLoading(nextTable, true);
            const exportButton = $('#export-button');
            if (exportButton.length) {
                exportButton.empty();
                nextTable.buttons().container().appendTo(exportButton);
                $('#export-button .dt-buttons').css('float', 'left').css("margin-right", "0.5rem");
            }

            $('#ajax-data-array tfoot th').each(function (i) {
                const column = currentLeadColumns[i];
                if (column && column.type === 'date') {
                    $(this).html('<input class="datepicker-here" type="text" />');
                } else {
                    $(this).html('<input type="text" placeholder="Search" />');
                }
            });
            const dateInputs = $('#ajax-data-array tfoot .datepicker-here');
            if (dateInputs.length && typeof dateInputs.datepicker === 'function') {
                dateInputs.datepicker({
                    dateFormat: 'dd/mm/yyyy',
                    autoClose: true,
                    language: 'en',
                    onSelect: function (formattedDate, date, inst) {
                        var $input = $(inst.$el);
                        var $th = $input.closest('th');
                        if ($th.length === 0) {
                            console.warn('No <th> found for datepicker input');
                            return;
                        }
                        var columnIndex = nextTable.column($th).index();
                        if (columnIndex == null) columnIndex = $th.index();
                        nextTable.column(columnIndex).search(formattedDate || '').draw();
                    }
                });
            }

            nextTable.columns().every(function () {
                const that = this;
                $('input', this.footer()).on('change', function () {
                    if (that.search() !== this.value) {
                        that.search(this.value).draw();
                    }
                });
            });
        }

        setupLeadTableUi(table);

        let leadsInBatchRunning = false;
        let leadLoadGeneration = 0;
        async function loadLeadsInBatches(table, limit = 200, offset = 0, total = 0) {
            if(leadsInBatchRunning)
                return;
            leadsInBatchRunning = true;
            const runGeneration = leadLoadGeneration;
            try {
                for (;;) {
                    const endpoint = window.LEADS_ENDPOINT || 'https://api.coherta.com/pipeline/all/leads';
                    let url = `${endpoint}?offset=${offset}&limit=${limit}${
                        window.location.search.includes('pipeline_id=')
                            ? '&' + window.location.search.split('?')[1].split('&').find(p => p.startsWith('pipeline_id='))
                            : window.location.search.includes('day_id=')
                                ? '&' + window.location.search.split('?')[1].split('&').find(p => p.startsWith('day_id='))
                                : ''
                    }${
                        window.location.search.includes('stage_id=')
                            ? '&' + window.location.search.split('?')[1].split('&').find(p => p.startsWith('stage_id='))
                            : ''
                    }`;

                    // Add additional fields from ADDITIONAL_FIELDS to the URL if present
                    if (Array.isArray(window.ADDITIONAL_FIELDS) && window.ADDITIONAL_FIELDS.length > 0) {
                        const urlParams = new URLSearchParams(window.location.search);
                        let addedUrl = "";
                        window.ADDITIONAL_FIELDS.forEach((field) => {
                            const value = urlParams.get(field); // e.g. ?type=vat -> "vat"
                            if (value !== null && value !== "") {
                                addedUrl += '&' + encodeURIComponent(field) + '=' + encodeURIComponent(value);
                            }
                        });
                        url += addedUrl;
                    }
                    if (typeof window.LEADS_BUILD_URL === 'function') {
                        url = window.LEADS_BUILD_URL({ endpoint, limit, offset, total });
                    }

                    console.debug("Fetching:", url);
                    const res = await fetch(url, { headers: {
                        Accept: "application/json", "Authorization": `Bearer ${document.body.getAttribute('session_id')}`
                        } });
                    if (runGeneration !== leadLoadGeneration) return false;
                    if (!res.ok) {
                        $('#invalidSubscription').modal('show');
                        throw new Error(`API error: ${res.status} ${res.statusText}`);
                        return;
                    }

                    const chunk = await res.json();
                    if (runGeneration !== leadLoadGeneration) return false;
                    const extractRows = typeof window.LEADS_EXTRACT_ROWS === 'function'
                        ? window.LEADS_EXTRACT_ROWS
                        : (payload) => Array.isArray(payload?.data) ? payload.data : payload;
                    const rows = extractRows(chunk);
                    console.debug("Chunk length:", Array.isArray(rows) ? rows.length : "not array", chunk);
                    setLoading(table, false);

                    // Guard: API must return a PLAIN ARRAY of objects
                    if (!Array.isArray(rows)) {
                        // If your endpoint returns {data: [...]}, adapt here:
                        const maybe = chunk?.data;
                        if (Array.isArray(maybe)) {
                            table.rows.add(maybe);
                            total += maybe.length;
                            table.draw(false);
                            if (maybe.length < limit) break;
                            offset += maybe.length;
                            continue;
                        }
                        throw new Error("API did not return an array; got: " + typeof rows);
                    }

                    if (rows.length === 0) {
                        break;
                    }          // done
                    table.rows.add(rows).draw(false);       // append
                    total += rows.length;

                    if (rows.length < limit) break;         // last page
                    offset += rows.length;                  // next batch
                }
            } catch (err) {
                console.error("Failed loading leads:", err);
                if (runGeneration === leadLoadGeneration) {
                    table.clear().draw(false);
                }
                // Optional: show a friendly error somewhere in the UI
            } finally {
                if (runGeneration !== leadLoadGeneration) return false;
                leadsInBatchRunning = false;
                if (total === 0) {
                    // Show a clearer message if nothing came back
                    $('#ajax-data-array').DataTable().clear().draw();
                    $('#ajax-data-array tbody').html(
                        `<tr class="odd"><td valign="top" colspan="${columns.length}" class="dataTables_empty">${window.LEADS_EMPTY_TEXT || 'No leads found'}</td></tr>`
                    );
                    $.fn.dataTable.moment('dd/mm/yyyy');
                }
            }
        }
        window.leadsTable = table;
        window.loadLeadsInBatches = function (limit = 250, offset = 0, total = 0) {
            return loadLeadsInBatches(table, limit, offset, total);
        };
        window.reloadLeadsInBatches = function (limit = 250, offset = 0, total = 0) {
            leadLoadGeneration += 1;
            leadsInBatchRunning = false;
            table.clear().draw(false);
            setLoading(table, true);
            return loadLeadsInBatches(table, limit, offset, total);
        };
        window.reloadLeadColumns = function (nextColumns, options = {}) {
            if (!Array.isArray(nextColumns)) return Promise.resolve(false);
            const normalizedColumns = normalizeLeadColumnConfig(nextColumns);

            leadLoadGeneration += 1;
            leadsInBatchRunning = false;
            currentLeadColumns = normalizedColumns;
            columns = buildDataTableColumns(currentLeadColumns);

            if ($.fn.DataTable.isDataTable('#ajax-data-array')) {
                table.clear().draw(false);
                table.destroy();
            }

            renderLeadTableHeaders(currentLeadColumns);
            table = createLeadDataTable();
            window.leadsTable = table;
            setupLeadTableUi(table);

            return Promise.resolve(window.reloadLeadsInBatches(options.limit || 250, 0, 0)).then(() => true);
        };
    });

    (function bindFullEnrichOnClick() {
        const API_URL = "https://api.coherta.com/pipeline/leads/fullenrich";
        const ROTATE_MS = 5000;
        const TIMEOUT_MS = 60000;

        const messages = [
            "Requesting",
            "Waiting for data from FulllEnrich",
            "Validating phone number",
            "Validating on ContactOut",
        ];

        function ensureCss() {
            if (document.getElementById("fe-css")) return;
            const style = document.createElement("style");
            style.id = "fe-css";
            style.textContent = `
      .fullEnrichStatus{display:inline-block;margin-left:8px;white-space:pre-wrap;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;font-size:12px;}
      .feSpinner{display:inline-block;width:12px;height:12px;margin-right:6px;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;animation:feSpin .8s linear infinite;vertical-align:-2px;}
      @keyframes feSpin{to{transform:rotate(360deg)}}
    `;
            document.head.appendChild(style);
        }

        // jQuery supported? If yes, use .on('click'). If not, fallback to vanilla delegation.
        const hasJQ = !!window.jQuery;

        async function handleClick(btn) {
            ensureCss();

            // prevent double-trigger if user spam clicks
            if (btn.dataset.feBusy === "1") return;
            btn.dataset.feBusy = "1";

            const parent = btn.parentElement || document.body;

            // remove button
            btn.remove();

            // status span
            const status = document.createElement("span");
            status.className = "fullEnrichStatus";

            const spinner = document.createElement("span");
            spinner.className = "feSpinner";
            status.appendChild(spinner);

            const msgNode = document.createElement("span");
            status.appendChild(msgNode);

            parent.appendChild(status);

            // rotate messages
            let i = 0;
            msgNode.textContent = messages[i];
            const intervalId = setInterval(() => {
                i = (i + 1) % messages.length;
                msgNode.textContent = messages[i];
            }, ROTATE_MS);

            // build payload from button data-* (customize this to your needs)
            // Example:
            // <button class="checkFullEnrich" data-lead-id="123" data-linkedin-url="...">...</button>
            const payload = {
                company_uid: btn.dataset.company_uid ? String(btn.dataset.company_uid) : undefined,
                participant_id: btn.dataset.id ? Number(btn.dataset.id) : undefined
            };

            // optional auth (set window.COHERTA_API_TOKEN somewhere if needed)
            const bearerToken = document.body.getAttribute('session_id');

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), TIMEOUT_MS);

            try {
                const r = await fetch(API_URL, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        ...(bearerToken ? { Authorization: `Bearer ${bearerToken}` } : {}),
                    },
                    body: JSON.stringify(payload),
                    signal: controller.signal,
                });

                const ct = (r.headers.get("content-type") || "").toLowerCase();
                const isJson = ct.includes("application/json");
                const body = isJson ? await r.json() : await r.text();

                clearInterval(intervalId);
                clearTimeout(timeoutId);
                spinner.remove();

                if (!r.ok) {
                    msgNode.textContent =
                        "Error: " + (isJson ? JSON.stringify(body, null, 2) : String(body));
                    return;
                }

                if (body === "") {
                    msgNode.textContent = "";
                } else {
                    msgNode.innerHTML = `Direct: <a href="tel:${body}">${body}</a>`;
                }
            } catch (err) {
                clearInterval(intervalId);
                clearTimeout(timeoutId);
                spinner.remove();

                msgNode.textContent =
                    err?.name === "AbortError"
                        ? "Timed out while waiting for FullEnrich response."
                        : (err?.message || String(err));
            }
        }

        if (hasJQ) {
            // jQuery-style .onClick (delegated)
            window.jQuery(document).on("click", ".checkFullEnrich", function (e) {
                e.preventDefault();
                handleClick(this);
            });
        } else {
            // Vanilla delegated click
            document.addEventListener("click", (e) => {
                const btn = e.target.closest?.(".checkFullEnrich");
                if (!btn) return;
                e.preventDefault();
                handleClick(btn);
            });
        }
    })();


// Delete lead handler
    $(document).on('click', '.delete-lead', function (e) {
        e.preventDefault();
        const url = $(this).attr("href");
        const $row = $(this).closest("tr");
        $('#deleteLeadModal').remove(); // Remove any existing modal
        const dataid = $(this).data("id");
        const modalHtml = `
<div class="modal fade" id="deleteLeadModal" tabindex="-1" role="dialog" aria-labelledby="deleteLeadModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteLeadModalLabel">Delete Lead</h5>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this lead and all notes on it?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="closeModalDeleteLead" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" data-id="${dataid}" id="confirmDeleteLead">Delete</button>
      </div>
    </div>
  </div>
</div>
`;

        $('body').append(modalHtml);
        $('#deleteLeadModal').modal('show');
        $('#closeModalDeleteLead').on('click').on('click', function () {
            $('#deleteLeadModal').modal('hide');
        });

        $('#confirmDeleteLead').on('click').on('click', function () {
            const bearerToken = document.body.getAttribute('session_id');
            fetch("https://api.coherta.com/pipeline/leads/delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    ...(bearerToken ? { Authorization: `Bearer ${bearerToken}` } : {}),
                },
                body: JSON.stringify({
                    pipeline_id: new URLSearchParams(window.location.search).get("pipeline_id"),
                    lead_id: $(this).data("id")
                })
            });
            $('#deleteLeadModal').modal('hide');
            $('#ajax-data-array').DataTable().row($row).remove().draw(false);
        });

    });





    //datatable post start here
    var table = $('#dt-plugin-method').DataTable();
    $('<button class="btn btn-primary  m-b-20">sum of age in all rows</button>').prependTo('.dt-plugin-buttons').on('click', function() {
        alert('Column sum is: ' + table.column(3).data().sum());
    });
    $('<button class="btn btn-primary m-r-10 m-b-20">sum of  age of visible rows</button>').prependTo('.dt-plugin-buttons').on('click', function() {
        alert('Column sum is: ' + table.column(3, {
            page: 'current'
        }).data().sum());
    });
    //Api datatable end here
    //Ordering Plug-ins (with type detection) start here
    $.fn.dataTable.ext.type.detect.unshift(function(d) {
        return d === 'Low' || d === 'Medium' || d === 'High' ? 'salary-grade' : null;
    });
    $.fn.dataTable.ext.type.order['salary-grade-pre'] = function(d) {
        switch (d) {
            case 'Low':
                return 1;
            case 'Medium':
                return 2;
            case 'High':
                return 3;
        }
        return 0;
    };
    $('#datatable-ordering').DataTable();
    //Ordering Plug-ins (with type detection) end here
    //Range plugin datatable start here
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var min = parseInt($('#min').val(), 10);
        var max = parseInt($('#max').val(), 10);
        var age = parseFloat(data[3]) || 0;
        if ((isNaN(min) && isNaN(max)) || (isNaN(min) && age <= max) || (min <= age && isNaN(max)) || (min <= age && age <= max)) {
            return true;
        }
            return false;
    });
    var dtage = $('#datatable-range').DataTable();
    $('#min, #max').keyup(function() {
        dtage.draw();
    });
    //Range plugin datatable end here    
    //datatable dom ordering start here
    $.fn.dataTable.ext.order['dom-text'] = function(settings, col) {
        return this.api().column(col, {
            order: 'index'
        }).nodes().map(function(td, i) {
            return $('input', td).val();
        });
    }
    $.fn.dataTable.ext.order['dom-text-numeric'] = function(settings, col) {
        return this.api().column(col, {
            order: 'index'
        }).nodes().map(function(td, i) {
            return $('input', td).val() * 1;
        });
    }
    $.fn.dataTable.ext.order['dom-select'] = function(settings, col) {
        return this.api().column(col, {
            order: 'index'
        }).nodes().map(function(td, i) {
            return $('select', td).val();
        });
    }   
    $.fn.dataTable.ext.order['dom-checkbox'] = function(settings, col) {
        return this.api().column(col, {
            order: 'index'
        }).nodes().map(function(td, i) {
            return $('input', td).prop('checked') ? '1' : '0';
        });
    }
    $(document).ready(function() {
        $('#datatable-livedom').DataTable({
            "columns": [null, {
                "orderDataType": "dom-text-numeric"
            }, {
                "orderDataType": "dom-text",
                type: 'string'
            }, {
                "orderDataType": "dom-select"
            }]
        });
    });
    // Move first in queue handler
    $(document).on('click', '.move-first-in-queue', async function (e) {
        e.preventDefault();
        const leadId = $(this).data('id');
        const bearerToken = document.body.getAttribute('session_id');
        try {
            await fetch('https://api.coherta.com/pipeline/list/queue', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    ...(bearerToken ? { Authorization: `Bearer ${bearerToken}` } : {}),
                },
                body: JSON.stringify({ lead_id: leadId }),
            });
            // Show confirmation modal in lower right corner
            const $modal = $(`
                <div id="move-first-confirm" style="
                    position: fixed;
                    right: 24px;
                    bottom: 24px;
                    z-index: 9999;
                    background: #28a745;
                    color: #fff;
                    padding: 16px 24px;
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                    font-size: 16px;
                    display: flex;
                    align-items: center;
                    min-width: 260px;
                ">
                    <span style="margin-right: 8px;">✅</span>
                    Lead is now being scraped as the next one.
                </div>
            `);
            $('body').append($modal);
            setTimeout(() => {
                $modal.fadeOut(400, function() { $(this).remove(); });
            }, 3500);
        } catch (err) {
            console.error('Failed to move lead to first in queue:', err);
        }
    });

});
