"use strict";
var KTAppEcommerceSalesSaveOrder = (function () {
    var e, t;
    return {
        init: function () {
            (() => {
                const r = (e) => {
                    if (!e.id) return e.text;
                    var t = document.createElement("span"),
                        r = "";
                    return (r += '<img src="' + e.element.getAttribute("data-kt-select2-country") + '" class="rounded-circle h-20px me-2" alt="image"/>'), (r += e.text), (t.innerHTML = r), $(t);
                };

                e = document.querySelector("#kt_ecommerce_edit_order_product_table");
                t = $(e).DataTable({
                    order: [],
                    scrollY: "400px",
                    scrollCollapse: !0,
                    paging: !1,
                    info: !1,
                    columnDefs: [{ orderable: !1, targets: 0 }],
                });
            })();

            // Filtering logic
            document.querySelector('[data-kt-ecommerce-edit-order-filter="search"]').addEventListener("keyup", function (e) {
                t.search(e.target.value).draw();
            });

            // Checkbox selection handler
            (() => {
                const t = e.querySelectorAll('[type="checkbox"]');
                const r = document.getElementById("kt_ecommerce_edit_order_selected_products");
                const o = document.getElementById("kt_ecommerce_edit_order_total_price");

                t.forEach((checkbox) => {
                    checkbox.addEventListener("change", (t) => {
                        const productRow = checkbox.closest("tr").querySelector('[data-kt-ecommerce-edit-order-filter="product"]').cloneNode(true);
                        const containerDiv = document.createElement("div");
                        const productContent = productRow.innerHTML;
                        const removeClasses = ["d-flex", "align-items-center"];
                        productRow.classList.remove(...removeClasses);
                        productRow.classList.add("col", "my-2");
                        productRow.innerHTML = "";
                        containerDiv.classList.add(...removeClasses);
                        containerDiv.classList.add("border", "border-dashed", "rounded", "p-3", "bg-body");
                        containerDiv.innerHTML = productContent;
                        productRow.appendChild(containerDiv);

                        const productId = productRow.getAttribute("data-kt-ecommerce-edit-order-id");

                        if (t.target.checked) {
                            r.appendChild(productRow);
                        } else {
                            const existingProduct = r.querySelector(`[data-kt-ecommerce-edit-order-id="${productId}"]`);
                            existingProduct && r.removeChild(existingProduct);
                        }
                    });
                });
            })();
        },
    };
})();

KTUtil.onDOMContentLoaded(function () {
    KTAppEcommerceSalesSaveOrder.init();
});
