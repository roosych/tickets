$(function() {
    let SettingsUsers = (function() {
        let table, datatable;

        const matchers = {
            all: () => true,
            active: row => Number(row.attr('data-active')) === 1,
            inactive: row => Number(row.attr('data-active')) === 0,
            hidden: row => Number(row.attr('data-hidden')) === 1,
            approver: row => Number(row.attr('data-approver')) === 1,
            inactive_tickets: row => Number(row.attr('data-active')) === 0 && Number(row.attr('data-open')) > 0
        };

        function init() {
            table = $("#user_report_table");
            if (!table.length) return;

            datatable = table.DataTable({
                info: false,
                order: [],
                pageLength: 25,
                columnDefs: [{ orderable: false, targets: [] }]
            });

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable !== table[0]) return true;
                const row = $(datatable.row(dataIndex).node());
                const dept = $('#filterDept').val();
                if (dept && String(String(row.attr('data-dept'))) !== dept) return false;
                return (matchers[$('#filterStatus').val()] || matchers.all)(row);
            });

            $('[user-report-filter="search"]').on("keyup", function() {
                datatable.search($(this).val()).draw();
            });
            $('#filterDept, #filterStatus').on('change', () => datatable.draw());

            $(document).on('click', '[data-quick-filter]', function(e) {
                e.preventDefault();
                $('#filterStatus').val($(this).data('quick-filter')).trigger('change');
            });
        }

        return { init: init };
    })();

    SettingsUsers.init();
});
