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
