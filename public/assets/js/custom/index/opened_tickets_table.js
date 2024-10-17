"use strict";
let DeptTickets = (function () {
    let t, e;
    return {
        init: function () {
            (t = document.querySelector("#my_open_tickets_table")) &&
            ((e = $(t).DataTable({
                    info: !1,
                    order: [],
                    pageLength: 5,
                    columnDefs: [
                        { orderable: false, targets: 0 },
                        { orderable: false, targets: 1 },
                        { orderable: false, targets: 2 },
                        { orderable: false, targets: 4 },
                        { orderable: false, targets: 5 },
                    ],
                }))
            );
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    DeptTickets.init();
});

