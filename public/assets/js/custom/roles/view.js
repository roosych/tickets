"use strict";
var KTUsersViewRole = (function () {
    var t,
        e;

    return {
        init: function () {
            (e = document.querySelector("#kt_roles_view_table")) &&
            (e.querySelectorAll("tbody tr").forEach((t) => {}),
                    (t = $(e).DataTable({
                        info: !1,
                        order: [],
                        pageLength: 5,
                        lengthChange: !1,
                        columnDefs: [
                            { orderable: !1, targets: [0, 3] },
                        ],
                    })),
                    document.querySelector('[data-kt-roles-table-filter="search"]').addEventListener("keyup", function (e) {
                        t.search(e.target.value).draw();
                    })

            );
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTUsersViewRole.init();
});
