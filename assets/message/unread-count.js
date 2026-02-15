import $ from 'jquery';

let unreadCountConfig = {
    url: null
};

export const initUnreadCount = (config) => {
    unreadCountConfig.url = config.url;
    updateUnreadCount();
};

export const updateUnreadCount = () => {
    if (!unreadCountConfig.url) {
        return;
    }

    $.ajax({
        url: unreadCountConfig.url,
        method: 'GET',
        cache: false,
        dataType: 'json'
    }).done((data) => {
        const $badge = $('.pc-sidebar .pc-link[href*="messages"] .pc-badge');

        if (!data.hasOwnProperty('unreadCount')) {
            return;
        }

        if (data.unreadCount > 0) {
            if ($badge.length) {
                $badge.text(data.unreadCount);
            } else {
                $('.pc-sidebar .pc-link[href*="messages"]').append(`<span class="pc-badge">${data.unreadCount}</span>`);
            }
        } else {
            $badge.remove();
        }
    }).fail((xhr, status, error) => {
        console.error('Error fetching unread count:', error);
    });
};
