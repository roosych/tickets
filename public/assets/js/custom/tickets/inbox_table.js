"use strict";
let DeptTickets = (function () {
    let t, e;
    const state = { chip: "all", creator: "", own: false, from: null, to: null, userId: null };

    // Дополнительные фильтры по data-атрибутам строк
    function registerRowFilter() {
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== "dept_tickets_table") return true;
            const row = settings.aoData[dataIndex].nTr;
            const d = row.dataset;

            switch (state.chip) {
                case "new": if (d.status !== "opened") return false; break;
                case "in_progress": if (d.status !== "in_progress") return false; break;
                case "overdue": if (d.overdue !== "1") return false; break;
                case "done": if (d.status !== "done") return false; break;
            }
            if (state.creator && d.creator !== state.creator) return false;
            if (state.own && d.own !== "1") return false;
            if (state.from !== null) {
                const created = parseInt(d.created, 10);
                if (created < state.from || created > state.to) return false;
            }
            return true;
        });
    }

    function initChips() {
        const box = document.querySelector("#dept_tickets_chips");
        if (!box) return;
        box.querySelectorAll("[data-chip]").forEach((link) => {
            link.addEventListener("click", (ev) => {
                ev.preventDefault();
                state.chip = link.dataset.chip;
                box.querySelectorAll("[data-chip]").forEach((l) => l.classList.toggle("active", l === link));
                e.draw();
            });
        });
    }

    function initPeriod() {
        const L = window.deptTicketsI18n || {};
        const input = $("#dept_tickets_period");
        if (!input.length) return;
        input.daterangepicker({
            autoUpdateInput: false,
            ranges: {
                [L.today]: [moment(), moment()],
                [L.yesterday]: [moment().subtract(1, "days"), moment().subtract(1, "days")],
                [L.days7]: [moment().subtract(6, "days"), moment()],
                [L.days30]: [moment().subtract(29, "days"), moment()],
                [L.month]: [moment().startOf("month"), moment().endOf("month")],
            },
            locale: { format: "DD.MM.YYYY", firstDay: 1, customRangeLabel: L.custom, applyLabel: L.apply, cancelLabel: L.reset },
        }, function (start, end) {
            input.val(start.format("DD.MM.YYYY") + " - " + end.format("DD.MM.YYYY"));
            state.from = start.clone().startOf("day").unix();
            state.to = end.clone().endOf("day").unix();
            e.draw();
        });
        input.on("cancel.daterangepicker", function () {
            input.val("");
            state.from = state.to = null;
            e.draw();
        });
    }

    return {
        init: function () {
            (t = document.querySelector("#dept_tickets_table")) &&
            ((e = $(t).DataTable({
                info: !1,
                // сверху срочные и просроченные, затем новые
                order: [[4, "asc"], [3, "desc"]],
                pageLength: 25,
                columnDefs: [
                    { orderable: false, targets: [0, 1, 2, 5, 6, 7, 8] },
                ],
            })),
                registerRowFilter(),
                (() => {
                    const el = document.querySelector('[data-dept-tickets-filter="status"]');
                    el && el.dataset.initial && e.column(5).search(el.dataset.initial).draw();
                })(),
                initChips(),
                initPeriod(),
                document.querySelector('[data-dept-tickets-filter="search"]').addEventListener("keyup", function (t) {
                    e.search(t.target.value).draw();
                }),
                (() => {
                    const t = document.querySelector('[data-dept-tickets-filter="status"]');
                    $(t).on("change", (t) => {
                        let n = t.target.value;
                        "all" === n && (n = ""), e.column(5).search(n).draw();
                    });
                })(),
                (() => {
                    const t = document.querySelector('[data-kt-ecommerce-priority-filter="priority"]');
                    $(t).on("change", (t) => {
                        let n = t.target.value;
                        "all" === n && (n = ""), e.column(2).search(n).draw();
                    });
                })(),
                ["creator"].forEach((key) => {
                    const t = document.querySelector('[data-dept-tickets-filter="' + key + '"]');
                    t && $(t).on("change", (ev) => {
                        state[key] = "all" === ev.target.value ? "" : ev.target.value;
                        e.draw();
                    });
                }),
                (() => {
                    const t = document.querySelector('[data-dept-tickets-filter="own"]');
                    state.userId = t.dataset.userId;
                    t.addEventListener("change", () => {
                        state.own = t.checked;
                        e.draw();
                    });
                })()
            );
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    DeptTickets.init();
});
