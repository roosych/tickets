"use strict";
let DeptTickets = (function () {
    let t, e;
    return {
        init: function () {
            (t = document.querySelector("#dept_tickets_table")) &&
            ((e = $(t).DataTable({
                info: !1,
                order: [],
                pageLength: 10,
                columnDefs: [
                    { orderable: false, targets: 0 },
                    { orderable: false, targets: 1 },
                    { orderable: false, targets: 2 },
                    { orderable: false, targets: 4 },
                    { orderable: false, targets: 5 },
                    { orderable: false, targets: 6 },
                    { orderable: false, targets: 7 },
                ],
            })),
                document.querySelector('[data-dept-tickets-filter="search"]').addEventListener("keyup", function (t) {
                    e.search(t.target.value).draw();
                }),
                (() => {
                    const t = document.querySelector('[data-dept-tickets-filter="status"]');
                    $(t).on("change", (t) => {
                        let n = t.target.value;
                        "all" === n && (n = ""), e.column(4).search(n).draw();
                    });
                })(),
                (() => {
                    const t = document.querySelector('[data-kt-ecommerce-priority-filter="priority"]');
                    $(t).on("change", (t) => {
                        let n = t.target.value;
                        "all" === n && (n = ""), e.column(2).search(n).draw();
                    });
                })()
                );
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    DeptTickets.init();
});

