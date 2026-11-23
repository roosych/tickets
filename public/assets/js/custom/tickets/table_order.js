"use strict";
/**
 * Сортировка таблиц тикетов по вкладкам (chips) с запоминанием выбора пользователя.
 *
 * - У каждой вкладки своя сортировка по умолчанию:
 *   «Новые» и «Ожидают закрытия» — по дате создания (новые сверху),
 *   остальные — сначала срочные/просроченные (дедлайн ↑), затем по дате создания ↓.
 * - Если пользователь кликнул по заголовку колонки, сортировка сохраняется
 *   в localStorage отдельно для страницы и вкладки и восстанавливается при возврате.
 * - Последняя открытая вкладка тоже запоминается.
 */
window.TicketTableOrder = function (dt, opts) {
    const created = opts.createdCol;
    const deadline = opts.deadlineCol;
    const prefix = "tickets_table:" + opts.page + ":";
    const byCreated = [[created, "desc"]];
    const byDeadline = [[deadline, "asc"], [created, "desc"]];
    const defaults = { new: byCreated, done: byCreated };

    let chip = "all";
    let applying = false;

    function read(key) {
        try { return JSON.parse(localStorage.getItem(prefix + key)); } catch (_) { return null; }
    }
    function write(key, value) {
        try {
            value === null ? localStorage.removeItem(prefix + key) : localStorage.setItem(prefix + key, JSON.stringify(value));
        } catch (_) { /* приватный режим / запрет хранилища — просто не запоминаем */ }
    }

    function isValidOrder(order) {
        const cols = dt.columns().count();
        return Array.isArray(order) && order.length > 0 && order.every((o) =>
            Array.isArray(o) && Number.isInteger(o[0]) && o[0] >= 0 && o[0] < cols &&
            (o[1] === "asc" || o[1] === "desc") && dt.settings()[0].aoColumns[o[0]].bSortable !== false
        );
    }

    function orderFor(c) {
        const saved = read("order:" + c);
        return isValidOrder(saved) ? saved : (defaults[c] || byDeadline);
    }

    // Сохраняем только сортировку, выбранную пользователем (клик по заголовку)
    dt.on("order.dt", function () {
        if (applying) return;
        write("order:" + chip, dt.order().map((o) => [o[0], o[1]]));
    });

    return {
        // Переключить вкладку: применить её сортировку и перерисовать таблицу
        apply: function (c) {
            chip = c;
            write("chip", c);
            applying = true;
            try { dt.order(orderFor(c)).draw(); } finally { applying = false; }
        },
        savedChip: function () {
            const c = read("chip");
            return typeof c === "string" ? c : null;
        },
    };
};
