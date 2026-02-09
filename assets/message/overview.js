import $ from 'jquery';

export const initMessageList = (config) => {
    const $containerContent = $('#message-container-content');
    const $containerSpinner = $('#message-container-spinner');
    const $listContainer = $('#message-list-container');
    const $detailCard = $('#message-detail-card');
    const $placeholder = $('#message-detail-placeholder');

    const translations = config.translations;

    const showSpinner = () => {
        $containerSpinner.removeClass('d-none');
        $containerContent.css('opacity', '0.5');
    };

    const hideSpinner = () => {
        $containerSpinner.addClass('d-none');
        $containerContent.css('opacity', '1');
    };

    const updateActiveFilter = ($clickedLink) => {
        if ($clickedLink.hasClass('btn')) {
            $('.btn-group .btn').removeClass('btn-primary').addClass('btn-outline-primary');
            $clickedLink.removeClass('btn-outline-primary').addClass('btn-primary');
        }
    };

    const attachRowListeners = () => {
        $listContainer
            .off('click', '.message-row')
            .on('click', '.message-row', function() {
            const $row = $(this);
            const uuid = $row.data('message-uuid');
            const state = $row.data('message-state');

            // UI Updates
            $('.message-row').removeClass('table-active');
            $row.addClass('table-active');

            // Data Fetching
            const url = config.viewUrlTemplate.replace('__UUID__', uuid);

            $detailCard.show();
            $placeholder.hide();
            showSpinner();

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json'
            }).done((data) => {
                $('#message-detail-subject').text(data.subject || translations.noSubject);
                $('#message-detail-sender').text(data.sender || translations.defaultSender);
                $('#message-detail-date').text(data.createdAt);
                $('#message-detail-content').text(data.message);
                if (state === 'unread') {
                    const $badge = $row.find('.message-state-badge');
                    if ($badge.length) {
                        $badge.removeClass('bg-warning').addClass('bg-success').text(translations.read);
                    }
                    $row.removeClass('fw-bold');
                    $row.data('message-state', 'read');
                    // Also update the DOM attribute if needed for CSS selectors
                    $row.attr('data-message-state', 'read');
                }
            }).fail((xhr, status, error) => {
                console.error('Error loading message:', error);
                alert(translations.errorLoading);
            }).always(() => {
                hideSpinner();
            });
        });
    };

    const handleAjaxLinks = () => {
        $(document).off('click', '.ajax-link').on('click', '.ajax-link', function(e) {
            e.preventDefault();
            const $link = $(this);
            const href = $link.attr('href');

            const url = new URL(href, window.location.origin);
            url.searchParams.set('ajax', '1');

            showSpinner();

            $.ajax({
                url: url.toString(),
                method: 'GET',
                dataType: 'html'
            }).done((html) => {
                $listContainer.html(html);
                updateActiveFilter($link);

                const cleanUrl = new URL(href, window.location.origin);
                window.history.pushState({}, '', cleanUrl.toString());
            }).fail((xhr, status, error) => {
                console.error('Error fetching list:', error);
            }).always(() => {
                hideSpinner();
            });
        });
    };

    // Initialize
    attachRowListeners();
    handleAjaxLinks();
};
