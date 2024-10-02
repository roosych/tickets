$(function() {
    let KTAppEcommerceReportReturns = (function() {
        let table, datatable;

        function init() {
            table = $("#user_report_table");
            if (table.length) {
                table.find("tbody tr").each(function() {
                    let row = $(this);
                    let dateCell = row.find("td:first");
                    let dateValue = moment(dateCell.text(), "MMM DD, YYYY").format();
                    dateCell.attr("data-order", dateValue);
                });

                datatable = table.DataTable({
                    info: false,
                    order: [],
                    pageLength: 10
                });

                initDateRangePicker();
                initExport();
                initSearch();
            }
        }

        function initDateRangePicker() {
            let start = moment().subtract(29, "days");
            let end = moment();
            let daterangepicker = $("#kt_ecommerce_report_returns_daterangepicker");

            function cb(start, end) {
                daterangepicker.html(start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY"));
            }

            daterangepicker.daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, "days"), moment().subtract(1, "days")],
                    'Last 7 Days': [moment().subtract(6, "days"), moment()],
                    'Last 30 Days': [moment().subtract(29, "days"), moment()],
                    'This Month': [moment().startOf("month"), moment().endOf("month")],
                    'Last Month': [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")]
                }
            }, cb);

            cb(start, end);
        }

        function initExport() {
            let title = "User tickets report";
            let buttons = new $.fn.dataTable.Buttons(table, {
                buttons: [
                    { extend: "excelHtml5", title: title },
                    { extend: "pdfHtml5", title: title }
                ]
            }).container().appendTo($("#kt_ecommerce_report_returns_export"));

            $("#users_export_menu [data-kt-ecommerce-export]").on("click", function(e) {
                e.preventDefault();
                var exportType = $(this).data("kt-ecommerce-export");
                $(".dt-buttons .buttons-" + exportType).click();
            });
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

    KTAppEcommerceReportReturns.init();
});
