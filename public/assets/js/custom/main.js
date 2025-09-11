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
