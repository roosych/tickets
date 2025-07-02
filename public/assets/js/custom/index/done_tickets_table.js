"use strict";
let DeptDoneTickets = (function () {
    let t, e;
    return {
        init: function () {
            (t = document.querySelector("#done_tickets_table")) &&
            ((e = $(t).DataTable({
                    info: !1,
                    paging: true,
                    pageLength: 10,
                    lengthMenu: [5, 10, 15, 20],
                    order: [],
                    columnDefs: [
                        { orderable: false, targets: 0 },
                        { orderable: false, targets: 1 },
                        { orderable: false, targets: 2 },
                        { orderable: false, targets: 4 },
                    ],
                }))
            );
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    DeptDoneTickets.init();
});

