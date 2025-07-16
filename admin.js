jQuery(document).ready(function($) {
    'use strict';

    // Function to re-index project numbers and input names
    function reindexProjects() {
        $('#abcupdater-project-list .abcupdater-project-box').each(function(i) {
            var index = i;
            $(this).find('.project-index').text(index + 1);
            $(this).find('select, input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[projects\]\[\d+\]/, '[projects][' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    // Add Project
    $('#abcupdater-add-project').on('click', function() {
        // Remove the 'no projects' message if it exists
        $('#abcupdater-no-projects').remove();

        var projectList = $('#abcupdater-project-list');
        var newIndex = projectList.find('.abcupdater-project-box').length;
        
        // Get the template
        var template = $('#tmpl-abcupdater-project-template').html();
        
        // Replace placeholders
        template = template.replace(/__INDEX__/g, newIndex + 1);
        template = template.replace(/__INDEX_RAW__/g, newIndex);

        // Append the new project box
        projectList.append(template);
    });

    // Remove Project
    $('#abcupdater-project-list').on('click', '.abcupdater-remove-project', function() {
        $(this).closest('.abcupdater-project-box').remove();
        
        if ($('#abcupdater-project-list .abcupdater-project-box').length === 0) {
            $('#abcupdater-project-list').append('<p id="abcupdater-no-projects">No projects configured. Click "Add Project" to start.</p>');
        }
        
        reindexProjects();
    });

    // Initial index check on page load
    reindexProjects();

    // Test Connection
    $('#abcupdater-project-list').on('click', '.abcupdater-test-connection', function(e) {
        e.preventDefault();
        var $button = $(this);
        var $projectBox = $button.closest('.abcupdater-project-box');
        var $status = $projectBox.find('.abcupdater-test-status');
        var repo = $projectBox.find('input[name*="[github_repo]"]').val();
        var token = $projectBox.find('input[name*="[github_token]"]').val();

        $status.removeClass('success error').text('Testing...');

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
                $status.addClass('error').text('An unknown error occurred.');
            }
        });
    });
});
