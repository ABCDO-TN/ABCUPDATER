jQuery(document).ready(function($) {
    'use strict';

    // Fetch latest news from GitHub
    function fetchLatestNews() {
        var $newsContainer = $('#postbox-container-1 .postbox .inside');
        
        // Placeholder while loading
        $newsContainer.html('<p>Fetching latest news...</p>');

        $.ajax({
            url: ajaxurl, // WordPress global ajaxurl
            type: 'POST',
            data: {
                action: 'abcupdater_get_latest_news',
                nonce: abcupdater_dashboard_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var newsHtml = '<ul>';
                    response.data.forEach(function(release) {
                        newsHtml += '<li>';
                        newsHtml += '<strong>' + release.name + ' (' + release.tag_name + ')</strong><br>';
                        newsHtml += '<small>Published on: ' + new Date(release.published_at).toLocaleDateString() + '</small><br>';
                        newsHtml += '<a href="' + release.html_url + '" target="_blank">View on GitHub</a>';
                        newsHtml += '</li>';
                    });
                    newsHtml += '</ul>';
                    $newsContainer.html(newsHtml);
                } else {
                    $newsContainer.html('<p>' + response.data.message + '</p>');
                }
            },
            error: function() {
                $newsContainer.html('<p>An error occurred while fetching news.</p>');
            }
        });
    }

    // Only run on the dashboard page
    if ($('body').hasClass('toplevel_page_abcupdater_dashboard')) {
        fetchLatestNews();
    }
});
