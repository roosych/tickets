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



/*
"use strict";
let KTUsersList = (function () {
    let e,
        o = document.getElementById("kt_table_users");
    return {
        init: function () {
            o &&
            (o.querySelectorAll("tbody tr").forEach((e) => {}),
                (e = $(o).DataTable({
                    info: !1,
                    order: [],
                    pageLength: 10,
                    lengthChange: !1,
                    columnDefs: [
                        { orderable: false, targets: [0, 1, 3] },
                    ],
                })),

                document.querySelector('[data-kt-user-table-filter="search"]').addEventListener("keyup", function (t) {
                    e.search(t.target.value).draw();
                }),
                document.querySelector('[data-kt-user-table-filter="reset"]').addEventListener("click", function () {
                    document
                        .querySelector('[data-kt-user-table-filter="form"]')
                        .querySelectorAll("select")
                        .forEach((e) => {
                            $(e).val("").trigger("change");
                        }),
                        e.search("").draw();
                }),
                (() => {
                    const t = document.querySelector('[data-kt-user-table-filter="form"]');
                    const n = t.querySelector('[data-kt-user-table-filter="filter"]');
                    const r = t.querySelectorAll("select");
                    n.addEventListener("click", function () {
                        let t = "";
                        r.forEach((e, n) => {
                            e.value && "" !== e.value && (0 !== n && (t += " "), (t += e.value));
                        }),
                            e.search(t).draw();
                    });
                })());
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTUsersList.init();
});
*/
