"use strict";
var KTAppEcommerceReportCustomerOrders = (function () {
    var table, dataTable;

    return {
        init: function () {
            table = $("#kt_ecommerce_report_customer_orders_table");

            if (table.length) {
                // DataTables buttons setup
                (function () {
                    const reportTitle = "Customer Orders Report";
                    new $.fn.dataTable.Buttons(table[0], {
                        buttons: [
                            { extend: "copyHtml5", title: reportTitle },
                            { extend: "excelHtml5", title: reportTitle },
                            { extend: "csvHtml5", title: reportTitle },
                            { extend: "pdfHtml5", title: reportTitle },
                        ],
                    })
                        .container()
                        .appendTo($("#kt_ecommerce_report_customer_orders_export"));

                    $("#kt_ecommerce_report_customer_orders_export_menu [data-kt-ecommerce-export]").on("click", function (event) {
                        event.preventDefault();
                        const exportType = $(this).attr("data-kt-ecommerce-export");
                        $(".dt-buttons .buttons-" + exportType).click();
                    });
                })();

                // Search filter
                $('[data-kt-ecommerce-order-filter="search"]').on("keyup", function (event) {
                    dataTable.search($(this).val()).draw();
                });

                // Status filter
                (function () {
                    const statusFilter = $('[data-kt-ecommerce-order-filter="status"]');
                    statusFilter.on("change", function (event) {
                        let statusValue = $(this).val();
                        if (statusValue === "all") {
                            statusValue = "";
                        }
                        dataTable.column(2).search(statusValue).draw();
                    });
                })();
            }
        },
    };
})();

KTUtil.onDOMContentLoaded(function () {
    KTAppEcommerceReportCustomerOrders.init();
});
