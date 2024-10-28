$(function() {
    let DeptTicketsUsers = (function() {
        let table, datatable;

        function init() {
            table = $("#user_report_table");
            if (table.length) {
                table.find("tbody tr").each(function() {
                    let row = $(this);
                    let dateCell = row.find("td:first");
                });

                datatable = table.DataTable({
                    info: false,
                    order: [],
                    pageLength: 25
                });

                initSearch();
            }
        }

        function initSearch() {
            $('[user-report-filter="search"]').on("keyup", function() {
                datatable.search($(this).val()).draw();
            });
        }

        return {
            init: init
        };
    })();

    DeptTicketsUsers.init();
});
