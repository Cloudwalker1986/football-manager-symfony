import $ from 'jquery';

const STORAGE_KEY = 'unread_message_count';

let unreadCountConfig = {
    url: null
};

export const initUnreadCount = (config) => {
    unreadCountConfig.url = config.url;
    
    // Check if we have a cached count in sessionStorage
    const cachedCount = sessionStorage.getItem(STORAGE_KEY);
    
    if (cachedCount !== null) {
        // Use cached count, no need to fetch from server
        updateBadgeDisplay(parseInt(cachedCount, 10));
    } else if ('initialCount' in config) {
        // Store the server-rendered initial count and display it
        sessionStorage.setItem(STORAGE_KEY, String(config.initialCount));
        updateBadgeDisplay(config.initialCount);
    } else {
        // Fallback: fetch from server if no cache or initial count
        updateUnreadCount();
    }
};

const updateBadgeDisplay = (count) => {
    // Ensure count is a valid number
    const numericCount = typeof count === 'number' ? count : parseInt(count, 10);
    
    if (isNaN(numericCount)) {
        console.error('Invalid count value:', count);
        return;
    }
    
    const $badge = $('.pc-sidebar .pc-link[href*="messages"] .pc-badge');
    
    if (numericCount > 0) {
        if ($badge.length) {
            $badge.text(numericCount);
        } else {
            $('.pc-sidebar .pc-link[href*="messages"]').append(`<span class="pc-badge">${numericCount}</span>`);
        }
    } else {
        $badge.remove();
    }
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
        if (!data.hasOwnProperty('unreadCount')) {
            return;
        }

        // Update sessionStorage cache
        sessionStorage.setItem(STORAGE_KEY, String(data.unreadCount));
        
        // Update the badge display
        updateBadgeDisplay(data.unreadCount);
    }).fail((xhr, status, error) => {
        console.error('Error fetching unread count:', error);
    });
};
