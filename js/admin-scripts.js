jQuery(document).ready(function($) {
    'use strict';

    // Function to re-index project numbers in the UI
    function reindexProjectNumbers() {
        $('#abcupdater-project-list .abcupdater-project-box').each(function(i) {
            $(this).find('.project-index').text(i + 1);
        });
    }

    // Add a new project
    $('#abcupdater-add-project').on('click', function() {
        // Remove the "No projects" message if it exists
        $('#abcupdater-no-projects').remove();

        // Get the template
        var template = $('#tmpl-abcupdater-project-template').html();

        // Determine the new index
        var newIndex = $('#abcupdater-project-list .abcupdater-project-box').length;

        // Replace placeholders
        template = template.replace(/__INDEX_RAW__/g, newIndex);
        template = template.replace(/__INDEX__/g, newIndex + 1);

        // Append the new project box
        $('#abcupdater-project-list').append(template);
    });

    // Remove a project
    $('#abcupdater-projects-wrapper').on('click', '.abcupdater-remove-project', function() {
        if (confirm('Are you sure you want to remove this project?')) {
            $(this).closest('.abcupdater-project-box').remove();
            reindexProjectNumbers();

            if ($('#abcupdater-project-list .abcupdater-project-box').length === 0) {
                $('#abcupdater-project-list').append('<p id="abcupdater-no-projects">No projects configured. Click "Add Project" to start.</p>');
            }
        }
    });

    // Test connection for a project
    $('#abcupdater-projects-wrapper').on('click', '.abcupdater-test-connection', function() {
        var $button = $(this);
        var $projectBox = $button.closest('.abcupdater-project-box');
        var $status = $projectBox.find('.abcupdater-test-status');

        var repo = $projectBox.find('input[name$="[github_repo]"]').val();
        var token = $projectBox.find('input[name$="[github_token]"]').val();

        $status.removeClass('success error').text('Testing...').show();
        $button.prop('disabled', true);

        $.ajax({
            url: abcupdater_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'abcupdater_test_connection',
                nonce: abcupdater_ajax.nonce,
                repo: repo,
                token: token
            },
            success: function(response) {
                if (response.success) {
                    $status.addClass('success').text(response.data.message);
                } else {
                    $status.addClass('error').text(response.data.message);
                }
            },
            error: function() {
                $status.addClass('error').text('An AJAX error occurred.');
            },
            complete: function() {
                $button.prop('disabled', false);
                setTimeout(function() {
                    $status.fadeOut();
                }, 5000);
            }
        });
    });
});
