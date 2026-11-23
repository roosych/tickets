function loadUnreadMentions() {
    $.ajax({
        url: '/cabinet/mentions/unread',
        method: 'GET',
        success: function(response) {
            const container = $('#kt_drawer_chat_messenger_body');
            const marker = $('#unreadMentionsMarker');
            container.html(response.html);
            if (response.mentions_count > 0) {
                marker.show();
            } else {
                marker.hide();
            }
        },
        error: function(error) {
            console.error('Fetch error:', error);
        }
    });
}

// Загружаем при открытии drawer
$('#kt_drawer_chat_toggle').on('click', loadUnreadMentions);

$(document).ready(loadUnreadMentions);
setInterval(loadUnreadMentions, 60000);

// Функция для применения waitMe эффекта
function applyWait(element) {
    element.waitMe({
        effect: 'bounce',
        color: '#3E97FF',
    });
}

// Функция для удаления waitMe эффекта
function removeWait(element) {
    element.waitMe('hide');
}

function getAjaxErrorMessage(response) {
    let errorMessage = '';

    if (response.responseJSON) {
        const json = response.responseJSON;

        if (json.errors) {
            for (const key in json.errors) {
                errorMessage += `<p class="mb-0">${json.errors[key][0]}</p>`;
            }
        } else if (json.message) {
            errorMessage += `<p class="mb-0">${json.message}</p>`;
        }
    } else {
        errorMessage = `<p class="mb-0">Unknown error. Code: ${response.status}</p>`;
    }

    return errorMessage;
}

// Сессия истекла (401) или устарел CSRF-токен (419): вместо «тихой» ошибки
// показываем окно и перезагружаем страницу. Если сессии нет, middleware auth
// отправит на логин и после входа вернёт на эту же страницу.
(function () {
    let shown = false;
    $(document).ajaxError(function (event, xhr) {
        if (shown || (xhr.status !== 401 && xhr.status !== 419)) return;
        shown = true;
        const L = window.sessionExpiredI18n || {};
        const reload = () => window.location.reload();
        if (typeof Swal === 'undefined') return reload();
        Swal.fire({
            icon: 'warning',
            title: L.title || 'Session expired',
            text: L.text || '',
            confirmButtonText: L.button || 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false,
            buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-primary' },
        }).then(reload);
    });
})();

// Уведомления о новых тикетах.
// Раз в 30 сек запрашиваем открытые тикеты пользователя/отдела:
// - заголовок вкладки: «(3) Ticket system», при новом тикете мигает, пока окно не в фокусе;
// - системное уведомление браузера (Notification API), если пользователь его разрешил.
(function () {
    const cfg = window.newTicketsTitle;
    if (!cfg || !cfg.url) return;
    const L = cfg.notify || {};
    const STORE_MUTED = 'desktop_notify_muted';
    const STORE_ASKED = 'desktop_notify_asked';

    const baseTitle = document.title;
    let count = 0;
    let lastId = null;
    let blinkTimer = null;

    // --- localStorage (может быть недоступен) ---
    function store(key, value) {
        try {
            if (value === undefined) return localStorage.getItem(key);
            value === null ? localStorage.removeItem(key) : localStorage.setItem(key, value);
        } catch (_) { return null; }
    }

    // --- заголовок вкладки ---
    function render() {
        document.title = count > 0 ? '(' + count + ') ' + baseTitle : baseTitle;
    }
    function stopBlink() {
        if (!blinkTimer) return;
        clearInterval(blinkTimer);
        blinkTimer = null;
        render();
    }
    function startBlink() {
        if (blinkTimer) return;
        let alt = false;
        blinkTimer = setInterval(function () {
            alt = !alt;
            alt ? (document.title = '🔔 ' + cfg.text) : render();
        }, 1000);
    }

    // --- системные уведомления ---
    const supported = 'Notification' in window;
    function notifyEnabled() {
        return supported && Notification.permission === 'granted' && store(STORE_MUTED) !== '1';
    }
    function showNotification(t) {
        try {
            const n = new Notification(t.title, {
                body: t.body,
                icon: cfg.icon,
                tag: 'ticket-' + t.id, // одинаковый tag — несколько вкладок не дублируют уведомление
            });
            n.onclick = function () {
                window.focus();
                window.location.href = t.url;
                n.close();
            };
        } catch (_) { /* некоторые браузеры не дают создавать Notification напрямую */ }
    }

    function updateMenu() {
        const badge = document.querySelector('[data-desktop-notify-status]');
        if (!badge) return;
        let text = L.off, cls = 'badge-light';
        if (!supported) { text = L.unsupported; }
        else if (Notification.permission === 'denied') { text = L.blocked; cls = 'badge-light-danger'; }
        else if (notifyEnabled()) { text = L.on; cls = 'badge-light-success'; }
        badge.textContent = text;
        badge.className = 'badge ' + cls;
    }

    function requestPermission() {
        return Notification.requestPermission().then(function (p) {
            store(STORE_ASKED, '1');
            if (p === 'granted') store(STORE_MUTED, null);
            updateMenu();
            return p;
        });
    }

    // Пункт в меню пользователя: включить / выключить / подсказка, если заблокировано
    $(document).on('click', '[data-desktop-notify-toggle]', function (ev) {
        ev.preventDefault();
        if (!supported) return;
        if (Notification.permission === 'denied') {
            typeof Swal !== 'undefined' && Swal.fire({ icon: 'info', text: L.blocked_help, buttonsStyling: false, customClass: { confirmButton: 'btn btn-primary' } });
            return;
        }
        if (Notification.permission === 'default') { requestPermission(); return; }
        store(STORE_MUTED, notifyEnabled() ? '1' : null);
        updateMenu();
    });

    // Один раз предлагаем включить уведомления (разрешение можно запросить только по клику)
    function offerOnce() {
        if (!supported || Notification.permission !== 'default' || store(STORE_ASKED) || typeof Swal === 'undefined') return;
        Swal.fire({
            icon: 'question',
            title: L.prompt_title,
            text: L.prompt_text,
            showCancelButton: true,
            confirmButtonText: L.enable,
            cancelButtonText: L.later,
            buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light' },
        }).then(function (res) {
            store(STORE_ASKED, '1');
            if (res.isConfirmed) requestPermission();
        });
    }

    // --- опрос ---
    function poll() {
        $.ajax({
            url: cfg.url,
            method: 'GET',
            dataType: 'json',
            data: lastId !== null ? { after: lastId } : {},
        }).done(function (r) {
            count = r.count || 0;
            const id = r.latest_id || 0;
            const away = document.hidden || !document.hasFocus();
            if (lastId !== null && id > lastId) {
                if (away) startBlink();
                if (away && notifyEnabled()) (r.new || []).slice().reverse().forEach(showNotification);
            }
            lastId = lastId === null ? id : Math.max(lastId, id);
            if (!blinkTimer) render();
        });
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) { stopBlink(); poll(); }
    });
    window.addEventListener('focus', stopBlink);

    updateMenu();
    setTimeout(offerOnce, 3000);
    poll();
    setInterval(poll, 30000);
})();
