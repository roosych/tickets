"use strict";
let KTModalNewTicket = (function () {
    let t, i, o, a;
    return {
        init: function () {
            (a = document.querySelector("#kt_modal_new_ticket")) &&
            ((o = new bootstrap.Modal(a)),
                (i = document.querySelector("#kt_modal_new_ticket_form")),
                (t = document.getElementById("create_ticket_form_submit"))
            );
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTModalNewTicket.init();
});
