"use strict";
var KTAppEcommerceSalesSaveOrder = (function () {
    var e, t;
    return {
        init: function () {
            (() => {
                //$("#kt_ecommerce_edit_order_date").flatpickr({ altInput: !0, altFormat: "d F, Y", dateFormat: "Y-m-d" });
                const r = (e) => {
                    if (!e.id) return e.text;
                    var t = document.createElement("span"),
                        r = "";
                    return (r += '<img src="' + e.element.getAttribute("data-kt-select2-country") + '" class="rounded-circle h-20px me-2" alt="image"/>'), (r += e.text), (t.innerHTML = r), $(t);
                };
                    //$("#kt_ecommerce_edit_order_billing_country").select2({ placeholder: "Select a country", minimumResultsForSearch: 1 / 0, templateSelection: r, templateResult: r }),
                    //$("#kt_ecommerce_edit_order_shipping_country").select2({ placeholder: "Select a country", minimumResultsForSearch: 1 / 0, templateSelection: r, templateResult: r }),
                    (e = document.querySelector("#kt_ecommerce_edit_order_product_table")),
                    (t = $(e).DataTable({ order: [], scrollY: "400px", scrollCollapse: !0, paging: !1, info: !1, columnDefs: [{ orderable: !1, targets: 0 }] }));
            })(),
                document.querySelector('[data-kt-ecommerce-edit-order-filter="search"]').addEventListener("keyup", function (e) {
                    t.search(e.target.value).draw();
                }),
                (() => {
                    const e = document.getElementById("kt_ecommerce_edit_order_shipping_form");
                    // document.getElementById("same_as_billing").addEventListener("change", (t) => {
                    //     t.target.checked ? e.classList.add("d-none") : e.classList.remove("d-none");
                    // });
                })(),
                (() => {
                    const t = e.querySelectorAll('[type="checkbox"]'),
                        r = document.getElementById("kt_ecommerce_edit_order_selected_products"),
                        o = document.getElementById("kt_ecommerce_edit_order_total_price");
                    t.forEach((e) => {
                        e.addEventListener("change", (t) => {
                            const o = e.closest("tr").querySelector('[data-kt-ecommerce-edit-order-filter="product"]').cloneNode(!0),
                                i = document.createElement("div"),
                                n = o.innerHTML,
                                a = ["d-flex", "align-items-center"];
                            o.classList.remove(...a), o.classList.add("col", "my-2"), (o.innerHTML = ""), i.classList.add(...a), i.classList.add("border", "border-dashed", "rounded", "p-3", "bg-body"), (i.innerHTML = n), o.appendChild(i);
                            const c = o.getAttribute("data-kt-ecommerce-edit-order-id");
                            if (t.target.checked) r.appendChild(o);
                            else {
                                const e = r.querySelector('[data-kt-ecommerce-edit-order-id="' + c + '"]');
                                e && r.removeChild(e);
                            }
                            //d();
                        });
                    });
                })();
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTAppEcommerceSalesSaveOrder.init();
});
