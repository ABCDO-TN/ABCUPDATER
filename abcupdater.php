<?php
/**
 * Plugin Name:       ABCUPDATER
 * Description:       Manages automatic updates for multiple themes and plugins from private or public GitHub repositories.
 * Version:           0.13.0
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Plugin URI:        http://abcdo.tn/abcupdater
 * Author:            ABCDO
 * Author URI:        http://abcdo.tn
 * Text Domain:       abcupdater
 * License:           MIT
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ===================================================================================
// 1. ACTIVATION REQUIREMENTS CHECK
// ===================================================================================

register_activation_hook( __FILE__, 'abcupdater_activation_check' );
function abcupdater_activation_check() {
    $min_wp_version = '5.5';
    $min_php_version = '7.4';
    global $wp_version;

    if ( version_compare( $wp_version, $min_wp_version, '<' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( sprintf( esc_html__( 'Could not activate %1$s. This plugin requires WordPress version %2$s or later. Your site is running version %3$s.', 'abcupdater' ), '<strong>ABCUPDATER</strong>', esc_html( $min_wp_version ), esc_html( $wp_version ) ), 'Plugin Activation Error', [ 'back_link' => true ] );
    }

    if ( version_compare( PHP_VERSION, $min_php_version, '<' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( sprintf( esc_html__( 'Could not activate %1$s. This plugin requires PHP version %2$s or later. Your site is running version %3$s.', 'abcupdater' ), '<strong>ABCUPDATER</strong>', esc_html( $min_php_version ), esc_html( PHP_VERSION ) ), 'Plugin Activation Error', [ 'back_link' => true ] );
    }
}

// ===================================================================================
// 2. UPDATER SETTINGS PAGE
// ===================================================================================

add_action( 'admin_menu', 'abcupdater_add_admin_menu' );
function abcupdater_add_admin_menu() {
    add_menu_page(
        'ABCUPDATER Dashboard',
        'ABCUPDATER',
        'manage_options',
        'abcupdater_dashboard',
        'abcupdater_dashboard_page_html',
        'dashicons-update-alt',
        30
    );

    add_submenu_page(
        'abcupdater_dashboard',
        'Settings',
        'Settings',
        'manage_options',
        'abcupdater_settings',
        'abcupdater_settings_page_html'
    );
}

add_action( 'admin_init', 'abcupdater_settings_init' );
function abcupdater_settings_init() {
    register_setting( 'abcupdater_options', 'abcupdater_settings', 'abcupdater_sanitize_settings' );
    add_settings_section('abcupdater_main_section', 'GitHub Connection Settings', null, 'abcupdater_settings');
    add_settings_field('abcupdater_projects_field', 'Managed Projects', 'abcupdater_projects_field_html', 'abcupdater_settings', 'abcupdater_main_section');
}

function abcupdater_projects_field_html() {
    $options = get_option( 'abcupdater_settings' );
    $projects = isset( $options['projects'] ) && is_array( $options['projects'] ) ? $options['projects'] : [];
    ?>
    <div id="abcupdater-projects-wrapper">
        <p class="description">Add and configure all themes and plugins you want to manage updates for from GitHub.</p>
        <div id="abcupdater-project-list">
            <?php if ( empty( $projects ) ) : ?>
                <p id="abcupdater-no-projects">No projects configured. Click "Add Project" to start.</p>
            <?php else : ?>
                <?php foreach ( $projects as $index => $project ) : ?>
                    <div class="abcupdater-project-box">
                        <h4>Project #<span class="project-index"><?php echo $index + 1; ?></span></h4>
                        <p>
                            <label><strong>Project Type</strong></label><br>
                            <select name="abcupdater_settings[projects][<?php echo $index; ?>][type]">
                                <option value="theme" <?php selected( $project['type'], 'theme' ); ?>>Theme</option>
                                <option value="plugin" <?php selected( $project['type'], 'plugin' ); ?>>Plugin</option>
                            </select>
                        </p>
                        <p>
                            <label><strong>Local Directory / Plugin File</strong> (Slug)</label><br>
                            <input type="text" name="abcupdater_settings[projects][<?php echo $index; ?>][local_slug]" value="<?php echo esc_attr( $project['local_slug'] ); ?>" size="50" placeholder="e.g., my-theme or my-plugin/my-plugin.php" />
                        </p>
                        <p>
                            <label><strong>GitHub Repository</strong></label><br>
                            <input type="text" name="abcupdater_settings[projects][<?php echo $index; ?>][github_repo]" value="<?php echo esc_attr( $project['github_repo'] ); ?>" size="50" placeholder="e.g., owner/repo-name" />
                        </p>
                        <p>
                            <label><strong>GitHub Personal Access Token</strong></label><br>
                            <input type="password" name="abcupdater_settings[projects][<?php echo $index; ?>][github_token]" value="<?php echo esc_attr( $project['github_token'] ); ?>" size="50" class="github-token-field" />
                        </p>
                        <button type="button" class="button button-secondary abcupdater-test-connection">Test Connection</button>
                        <button type="button" class="button button-secondary abcupdater-remove-project">Remove Project</button>
                        <span class="abcupdater-test-status"></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="abcupdater-add-project" class="button button-primary">Add Project</button>
    </div>

    <!-- JavaScript Template for new projects -->
    <script type="text/html" id="tmpl-abcupdater-project-template">
        <div class="abcupdater-project-box">
            <h4>Project #<span class="project-index">__INDEX__</span></h4>
            <p>
                <label><strong>Project Type</strong></label><br>
                <select name="abcupdater_settings[projects][__INDEX_RAW__][type]">
                    <option value="theme" selected>Theme</option>
                    <option value="plugin">Plugin</option>
                </select>
            </p>
            <p>
                <label><strong>Local Directory / Plugin File</strong> (Slug)</label><br>
                <input type="text" name="abcupdater_settings[projects][__INDEX_RAW__][local_slug]" value="" size="50" placeholder="e.g., my-theme or my-plugin/my-plugin.php" />
            </p>
            <p>
                <label><strong>GitHub Repository</strong></label><br>
                <input type="text" name="abcupdater_settings[projects][__INDEX_RAW__][github_repo]" value="" size="50" placeholder="e.g., owner/repo-name" />
            </p>
            <p>
                <label><strong>GitHub Personal Access Token</strong></label><br>
                <input type="password" name="abcupdater_settings[projects][__INDEX_RAW__][github_token]" value="" size="50" class="github-token-field" />
            </p>
            <button type="button" class="button button-secondary abcupdater-test-connection">Test Connection</button>
            <button type="button" class="button button-secondary abcupdater-remove-project">Remove Project</button>
            <span class="abcupdater-test-status"></span>
        </div>
    </script>
    <?php
}

function abcupdater_dashboard_page_html() {
    ?>
    <div class="wrap abcupdater-dashboard-wrap">
        <h1>ABCUPDATER Dashboard</h1>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <!-- Main Content -->
                <div id="post-body-content">
                    <div class="meta-box-sortables ui-sortable">
                        <div class="postbox">
                            <h2><span>Welcome to ABCUPDATER</span></h2>
                            <div class="inside">
                                <p>This is your central hub for managing all theme and plugin updates from GitHub. Use the settings page to configure your projects.</p>
                            </div>
                        </div>
                        <div class="postbox">
                            <h2><span>Managed Projects</span></h2>
                            <div class="inside">
                                <?php
                                $options = get_option('abcupdater_settings');
                                $projects = isset($options['projects']) && is_array($options['projects']) ? $options['projects'] : [];
                                if (empty($projects)) {
                                    echo '<p>No projects configured. <a href="' . admin_url('admin.php?page=abcupdater_settings') . '">Go to settings to add one.</a></p>';
                                } else {
                                    echo '<p>You are currently managing ' . count($projects) . ' project(s).</p>';
                                    // A full list will be implemented here later.
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sidebar -->
                <div id="postbox-container-1" class="postbox-container">
                    <div class="meta-box-sortables">
                        <div class="postbox">
                            <h2><span>ABCUPDATER News</span></h2>
                            <div class="inside">
                                <p>Fetching latest news...</p>
                                <!-- News content will be loaded here via AJAX -->
                            </div>
                        </div>
                        <div class="postbox">
                            <h2><span>Quick Links</span></h2>
                            <div class="inside">
                                <ul>
                                    <li><a href="https://github.com/ABCDO-TN/ABCUPDATER" target="_blank">GitHub Repository</a></li>
                                    <li><a href="<?php echo admin_url('admin.php?page=abcupdater_settings'); ?>">Settings</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
    <?php
}

function abcupdater_settings_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    ?>
    <div class="wrap">
        <h1>Update Manager Settings</h1>
        <p>This system allows for secure, direct updates for your themes and plugins from private or public GitHub repositories.</p>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'abcupdater_options' );
            do_settings_sections( 'abcupdater_settings' );
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <style>
        .abcupdater-project-box { border: 1px solid #ccd0d4; padding: 10px 20px; margin-top: 15px; background: #fff; border-radius: 4px; }
        .abcupdater-project-box h4 { margin-top: 5px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        #abcupdater-add-project { margin-top: 15px; }
        .abcupdater-test-status { margin-left: 10px; font-weight: bold; }
        .abcupdater-test-status.success { color: #28a745; }
        .abcupdater-test-status.error { color: #dc3545; }
    </style>
    <?php
}

add_action( 'admin_enqueue_scripts', 'abcupdater_enqueue_admin_scripts' );
function abcupdater_enqueue_admin_scripts( $hook ) {
    // Load on all ABCUPDATER pages
    if ( strpos($hook, 'abcupdater_') === false ) {
        return;
    }

    // Enqueue dashboard-specific styles and scripts
    if ($hook === 'toplevel_page_abcupdater_dashboard') {
        wp_enqueue_style('abcupdater-dashboard-styles', plugins_url('css/dashboard-styles.css', __FILE__), [], '0.13.0');
        wp_enqueue_script('abcupdater-dashboard-js', plugins_url('js/dashboard-scripts.js', __FILE__), ['jquery'], '0.13.0', true);
        wp_localize_script('abcupdater-dashboard-js', 'abcupdater_dashboard_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('abcupdater_dashboard_nonce'),
        ]);
    }
    
    // Load admin scripts only on the settings page
    if ($hook === 'abcupdater_page_abcupdater_settings') {
        wp_enqueue_script( 'abcupdater-admin-js', plugins_url( 'js/admin-scripts.js', __FILE__ ), [ 'jquery' ], '1.0.0', true );
        wp_localize_script( 'abcupdater-admin-js', 'abcupdater_ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'abcupdater_test_connection_nonce' ),
        ]);
    }
}
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'abcupdater_test_connection_nonce' ),
    ]);
}

function abcupdater_sanitize_settings( $input ) {
    $new_input = [];
    if ( isset( $input['projects'] ) && is_array( $input['projects'] ) ) {
        // Re-index the array to prevent gaps from removed projects
        $projects = array_values( $input['projects'] );

        foreach ( $projects as $project ) {
            if ( empty( $project['local_slug'] ) && empty( $project['github_repo'] ) ) {
                continue; // Skip empty entries
            }
            $sanitized_project = [
                'type'         => sanitize_text_field( $project['type'] ),
                'local_slug'   => sanitize_text_field( $project['local_slug'] ),
                'github_repo'  => sanitize_text_field( $project['github_repo'] ),
                // Do not sanitize the token here, as it might contain special characters.
                // It will be sanitized right before use.
                'github_token' => trim( $project['github_token'] ),
            ];
            $new_input['projects'][] = $sanitized_project;
        }
    }
    return $new_input;
}

// ===================================================================================
// 3. UPDATE CHECKER LOGIC
// ===================================================================================

/**
 * Generic update checker for both themes and plugins.
 *
 * @param object $transient The transient object.
 * @param string $type      The type of project to check ('theme' or 'plugin').
 * @return object The modified transient.
 */
function abcupdater_check_for_updates( $transient, $type ) {
    if ( empty( $transient->checked ) ) { return $transient; }

    $options = get_option( 'abcupdater_settings' );
    if ( ! isset( $options['projects'] ) || ! is_array( $options['projects'] ) ) {
        return $transient;
    }

    if ( ! class_exists( 'Parsedown' ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'Parsedown.php';
    }
    $Parsedown = new Parsedown();

    foreach ( $options['projects'] as $project ) {
        // Skip if the project type doesn't match the current check, or if data is incomplete
        if ( $project['type'] !== $type || empty( $project['local_slug'] ) || empty( $project['github_repo'] ) ) {
            continue;
        }

        $local_slug = $project['local_slug'];
        
        // Skip if this specific project is not in the list of items WordPress is checking
        if ( ! isset( $transient->checked[ $local_slug ] ) ) {
            continue;
        }

        $github_repo = $project['github_repo'];
        $github_token = $project['github_token'];

        // Basic validation for "owner/repo" format
        if ( ! preg_match( '/^[a-zA-Z0-9-]+\/[a-zA-Z0-9-._]+$/', $github_repo ) ) {
            continue;
        }
        list( $github_user, $repo_name ) = explode( '/', $github_repo );

        $api_url = "https://api.github.com/repos/{$github_user}/{$repo_name}/releases/latest";
        $headers = ['Accept' => 'application/vnd.github.v3+json'];
        if ( ! empty( $github_token ) ) {
            $headers['Authorization'] = 'token ' . sanitize_text_field($github_token);
        }

        $response = wp_remote_get( $api_url, ['headers' => $headers] );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            continue;
        }

        $release_data = json_decode( wp_remote_retrieve_body( $response ) );
        if ( ! is_object( $release_data ) || empty( $release_data->tag_name ) || empty( $release_data->assets ) ) {
            continue;
        }

        $new_version = ltrim( $release_data->tag_name, 'v' );
        $current_version = $transient->checked[ $local_slug ];

        if ( version_compare( $new_version, $current_version, '>' ) ) {
            $package_url = $release_data->assets[0]->browser_download_url;

            // For private repos, the browser_download_url is not sufficient.
            // We must get a signed URL by hitting the asset's API endpoint.
            if ( ! empty( $github_token ) ) {
                $asset_api_url = $release_data->assets[0]->url;
                $asset_headers = ['Accept' => 'application/octet-stream', 'Authorization' => 'token ' . sanitize_text_field($github_token)];
                // Use a HEAD request to get the redirect location without downloading the file
                $asset_response = wp_remote_head($asset_api_url, ['headers' => $asset_headers, 'timeout' => 15]);
                $redirect_url = wp_remote_retrieve_header($asset_response, 'location');
                if (!empty($redirect_url)) {
                    $package_url = $redirect_url;
                }
            }
            
            if ( ! empty($package_url) ) {
                $update_data = [
                    'slug'        => $local_slug,
                    'new_version' => $new_version,
                    'url'         => $release_data->html_url,
                    'package'     => $package_url,
                ];

                if ( $type === 'theme' ) {
                    $update_data['theme'] = $local_slug;
                }
                
                // Add changelog/description from the release body
                $update_data['sections'] = ['description' => $Parsedown->parse($release_data->body)];
                
                $transient->response[ $local_slug ] = (object) $update_data;
            }
        }
    }
    return $transient;
}

add_filter( 'pre_set_site_transient_update_themes', function( $transient ) {
    return abcupdater_check_for_updates( $transient, 'theme' );
}, 20, 1 );

add_filter( 'pre_set_site_transient_update_plugins', function( $transient ) {
    // First, handle the self-update for ABCUPDATER itself
    $transient = abcupdater_check_for_plugin_self_update( $transient );
    // Then, check for other configured plugins
    return abcupdater_check_for_updates( $transient, 'plugin' );
}, 20, 1 );


// ===================================================================================
// 4. AJAX HANDLERS
// ===================================================================================

add_action('wp_ajax_abcupdater_get_latest_news', 'abcupdater_get_latest_news_ajax_handler');
function abcupdater_get_latest_news_ajax_handler() {
    check_ajax_referer('abcupdater_dashboard_nonce', 'nonce');

    $api_url = "https://api.github.com/repos/ABCDO-TN/ABCUPDATER/releases?per_page=5";
    $response = wp_remote_get($api_url);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        wp_send_json_error(['message' => 'Could not fetch news from GitHub.']);
    }

    $releases = json_decode(wp_remote_retrieve_body($response));

    if (empty($releases)) {
        wp_send_json_error(['message' => 'No releases found.']);
    }

    wp_send_json_success($releases);
}

add_action( 'wp_ajax_abcupdater_test_connection', 'abcupdater_test_connection_ajax_handler' );
function abcupdater_test_connection_ajax_handler() {
    check_ajax_referer( 'abcupdater_test_connection_nonce', 'nonce' );

    $repo = isset( $_POST['repo'] ) ? sanitize_text_field( $_POST['repo'] ) : '';
    $token = isset( $_POST['token'] ) ? trim( $_POST['token'] ) : '';

    if ( empty( $repo ) ) {
        wp_send_json_error( [ 'message' => 'Repository slug is required.' ] );
    }

    if ( ! preg_match( '/^[a-zA-Z0-9-]+\/[a-zA-Z0-9-._]+$/', $repo ) ) {
        wp_send_json_error( [ 'message' => 'Invalid repository format. Use "owner/repo-name".' ] );
    }
    
    list( $github_user, $repo_name ) = explode( '/', $repo );

    $api_url = "https://api.github.com/repos/{$github_user}/{$repo_name}";
    $headers = ['Accept' => 'application/vnd.github.v3+json'];
    if ( ! empty( $token ) ) {
        $headers['Authorization'] = 'token ' . sanitize_text_field($token);
    }

    $response = wp_remote_get( $api_url, ['headers' => $headers] );
    $response_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );
    $data = json_decode($response_body);

    if ( $response_code === 200 ) {
        wp_send_json_success( [ 'message' => 'Connection successful! Repository found.' ] );
    } elseif ( $response_code === 404 ) {
        wp_send_json_error( [ 'message' => 'Repository not found. Check the slug and ensure the token has access.' ] );
    } elseif ( $response_code === 401 ) {
        wp_send_json_error( [ 'message' => 'Authentication failed. Check your Personal Access Token.' ] );
    } else {
        $error_message = isset($data->message) ? $data->message : "Received HTTP code {$response_code}.";
        wp_send_json_error( [ 'message' => "Error: " . $error_message ] );
    }
}


// ===================================================================================
// 5. PLUGIN SELF-UPDATER LOGIC
// ===================================================================================

function abcupdater_check_for_plugin_self_update( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    $plugin_slug = plugin_basename( __FILE__ );
    $github_user = 'ABCDO-TN';
    $github_repo = 'ABCUPDATER';

    // Do not use a token for the public self-updater repo
    $api_url = "https://api.github.com/repos/{$github_user}/{$github_repo}/releases/latest";
    $response = wp_remote_get( $api_url );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return $transient;
    }

    $release_data = json_decode( wp_remote_retrieve_body( $response ) );
    
    if ( ! is_object( $release_data ) || empty( $release_data->tag_name ) || empty( $release_data->assets[0]->browser_download_url ) ) {
        return $transient;
    }
    
    $plugin_data = get_plugin_data( __FILE__ );
    $current_version = $plugin_data['Version'];
    $new_version = ltrim( $release_data->tag_name, 'v' );

    if ( version_compare( $new_version, $current_version, '>' ) ) {
        if ( ! class_exists( 'Parsedown' ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'Parsedown.php';
        }
        $Parsedown = new Parsedown();

        $transient->response[ $plugin_slug ] = (object) [
            'slug'        => $plugin_slug, // FIX: Use the full plugin slug for details view
            'plugin'      => $plugin_slug,
            'new_version' => $new_version,
            'url'         => $release_data->html_url,
            'package'     => $release_data->assets[0]->browser_download_url,
            'icons' => [
                '1x' => 'https://raw.githubusercontent.com/ABCDO-TN/ABCUPDATER/main/assets/icon-128x128.jpg',
                '2x' => 'https://raw.githubusercontent.com/ABCDO-TN/ABCUPDATER/main/assets/icon-256x256.jpg',
            ],
            'sections'    => [
                'description' => $plugin_data['Description'],
                'changelog'   => $Parsedown->parse($release_data->body)
            ],
            'author'      => '<a href="' . esc_url($plugin_data['AuthorURI']) . '">' . esc_html($plugin_data['Author']) . '</a>',
            'author_profile' => $plugin_data['AuthorURI'],
        ];
    }

    return $transient;
}
