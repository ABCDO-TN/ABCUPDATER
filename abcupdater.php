<?php
/**
 * Plugin Name:       ABCUPDATER
 * Description:       Manages automatic updates for multiple themes and plugins from GitHub. Created by ABCDO.
 * Version:           0.12.0
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Plugin URI:        http://abcdo.tn/abcupdate
 * Author:            ABCDO
 * Author URI:        http://abcdo.tn
 * Text Domain:       abcupdater
 * License:           MIT
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ===================================================================================
// 1. ACTIVATION REQUIREMENTS CHECK
// ===================================================================================

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
register_activation_hook( __FILE__, 'abcupdater_activation_check' );

// ===================================================================================
// 2. UPDATER SETTINGS PAGE
// ===================================================================================

function abcupdater_add_admin_menu() {
    add_options_page('Update Manager', 'Update Manager', 'manage_options', 'abcupdater_settings', 'abcupdater_settings_page_html');
}
add_action( 'admin_menu', 'abcupdater_add_admin_menu' );

function abcupdater_settings_init() {
    register_setting( 'abcupdater_options', 'abcupdater_settings', 'abcupdater_sanitize_settings' );
    add_settings_section('abcupdater_main_section', 'GitHub Connection Settings', null, 'abcupdater_settings');
    add_settings_field('abcupdater_projects_field', 'Managed Projects', 'abcupdater_projects_field_html', 'abcupdater_settings', 'abcupdater_main_section');
}
add_action( 'admin_init', 'abcupdater_settings_init' );

function abcupdater_projects_field_html() {
    $options = get_option( 'abcupdater_settings' );
    $projects = isset( $options['projects'] ) && is_array( $options['projects'] ) ? $options['projects'] : [];
    ?>
    <div id="abcupdater-projects-wrapper">
        <p class="description">Add and configure all themes and plugins you want to manage updates for.</p>
        <div id="abcupdater-project-list">
            <?php if ( empty( $projects ) ) : ?>
                <p id="abcupdater-no-projects">No projects configured. Click "Add Project" to start.</p>
            <?php else : ?>
                <?php foreach ( $projects as $index => $project ) : ?>
                    <div class="abcupdater-project-box">
                        <h4>Project #<span class="project-index"><?php echo $index + 1; ?></span></h4>
                        <p>
                            <label>Project Type</label><br>
                            <select name="abcupdater_settings[projects][<?php echo $index; ?>][type]">
                                <option value="theme" <?php selected( $project['type'], 'theme' ); ?>>Theme</option>
                                <option value="plugin" <?php selected( $project['type'], 'plugin' ); ?>>Plugin</option>
                            </select>
                        </p>
                        <p>
                            <label>Local Folder/Slug</label><br>
                            <input type="text" name="abcupdater_settings[projects][<?php echo $index; ?>][local_slug]" value="<?php echo esc_attr( $project['local_slug'] ); ?>" size="50" placeholder="e.g., my-theme or my-plugin/my-plugin.php" />
                        </p>
                        <p>
                            <label>GitHub Repository</label><br>
                            <input type="text" name="abcupdater_settings[projects][<?php echo $index; ?>][github_repo]" value="<?php echo esc_attr( $project['github_repo'] ); ?>" size="50" placeholder="e.g., owner/repo-name" />
                        </p>
                        <p>
                            <label>License Key (GitHub Token)</label><br>
                            <input type="password" name="abcupdater_settings[projects][<?php echo $index; ?>][github_token]" value="<?php echo esc_attr( $project['github_token'] ); ?>" size="50" />
                        </p>
                        <button type="button" class="button button-secondary abcupdater-remove-project">Remove Project</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="abcupdater-add-project" class="button button-primary">Add Project</button>
    </div>

    <!-- JavaScript Template -->
    <script type="text/html" id="tmpl-abcupdater-project-template">
        <div class="abcupdater-project-box">
            <h4>Project #<span class="project-index">__INDEX__</span></h4>
            <p>
                <label>Project Type</label><br>
                <select name="abcupdater_settings[projects][__INDEX_RAW__][type]">
                    <option value="theme" selected>Theme</option>
                    <option value="plugin">Plugin</option>
                </select>
            </p>
            <p>
                <label>Local Folder/Slug</label><br>
                <input type="text" name="abcupdater_settings[projects][__INDEX_RAW__][local_slug]" value="" size="50" placeholder="e.g., my-theme or my-plugin/my-plugin.php" />
            </p>
            <p>
                <label>GitHub Repository</label><br>
                <input type="text" name="abcupdater_settings[projects][__INDEX_RAW__][github_repo]" value="" size="50" placeholder="e.g., owner/repo-name" />
            </p>
            <p>
                <label>License Key (GitHub Token)</label><br>
                <input type="password" name="abcupdater_settings[projects][__INDEX_RAW__][github_token]" value="" size="50" />
            </p>
            <button type="button" class="button button-secondary abcupdater-remove-project">Remove Project</button>
        </div>
    </script>
    <?php
}

function abcupdater_settings_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p>This system allows for secure, direct updates for your themes and plugins from GitHub.</p>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'abcupdater_options' );
            do_settings_sections( 'abcupdater_settings' );
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <style>
        .abcupdater-project-box { border: 1px solid #ccd0d4; padding: 10px 20px; margin-top: 15px; background: #fff; }
        .abcupdater-project-box h4 { margin-top: 5px; }
        #abcupdater-add-project { margin-top: 15px; }
    </style>
    <?php
}

function abcupdater_enqueue_admin_scripts( $hook ) {
    if ( 'settings_page_abcupdater_settings' !== $hook ) {
        return;
    }
    wp_enqueue_script( 'abcupdater-admin-js', plugins_url( '/admin.js', __FILE__ ), [ 'jquery' ], '0.20.0', true );
}
add_action( 'admin_enqueue_scripts', 'abcupdater_enqueue_admin_scripts' );


function abcupdater_sanitize_settings( $input ) {
    $new_input = [];
    if ( isset( $input['projects'] ) && is_array( $input['projects'] ) ) {
        foreach ( $input['projects'] as $project ) {
            if ( empty( $project['local_slug'] ) && empty( $project['github_repo'] ) ) {
                continue; // Skip empty entries
            }
            $sanitized_project = [
                'type'         => sanitize_text_field( $project['type'] ),
                'local_slug'   => sanitize_text_field( $project['local_slug'] ),
                'github_repo'  => sanitize_text_field( $project['github_repo'] ),
                'github_token' => sanitize_text_field( $project['github_token'] ),
            ];
            $new_input['projects'][] = $sanitized_project;
        }
    }
    return $new_input;
}

// ===================================================================================
// 3. UPDATE CHECKER LOGIC
// ===================================================================================

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
        if ( $project['type'] !== $type ) {
            continue;
        }

        $local_slug = $project['local_slug'];
        $github_repo = $project['github_repo'];
        $github_token = $project['github_token'];

        if ( empty( $local_slug ) || empty( $github_repo ) || ! isset( $transient->checked[ $local_slug ] ) ) {
            continue;
        }

        list( $github_user, $repo_name ) = explode( '/', $github_repo );
        if ( empty( $github_user ) || empty( $repo_name ) ) {
            continue;
        }

        $api_url = "https://api.github.com/repos/{$github_user}/{$repo_name}/releases/latest";
        $headers = ['Accept' => 'application/vnd.github.v3+json'];
        if ( ! empty( $github_token ) ) {
            $headers['Authorization'] = 'token ' . $github_token;
        }

        $response = wp_remote_get( $api_url, ['headers' => $headers] );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            continue;
        }

        $release_data = json_decode( wp_remote_retrieve_body( $response ) );
        if ( ! is_object( $release_data ) || empty( $release_data->tag_name ) || empty( $release_data->assets[0] ) ) {
            continue;
        }

        $new_version = ltrim( $release_data->tag_name, 'v' );
        $current_version = $transient->checked[ $local_slug ];

        if ( version_compare( $new_version, $current_version, '>' ) ) {
            $package_url = $release_data->assets[0]->browser_download_url;

            // For private repos, we need to get a signed URL
            if ( ! empty( $github_token ) ) {
                $asset_api_url = $release_data->assets[0]->url;
                $asset_response = wp_remote_get($asset_api_url, ['timeout' => 60, 'redirection' => 0, 'headers' => ['Accept' => 'application/octet-stream', 'Authorization' => 'token ' . $github_token]]);
                $package_url = wp_remote_retrieve_header($asset_response, 'location');
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
                    $update_data['sections'] = ['description' => $Parsedown->parse($release_data->body)];
                }
                
                $transient->response[ $local_slug ] = (object) $update_data;
            }
        }
    }
    return $transient;
}

add_filter( 'pre_set_site_transient_update_themes', function( $transient ) {
    return abcupdater_check_for_updates( $transient, 'theme' );
}, 20 );

add_filter( 'pre_set_site_transient_update_plugins', function( $transient ) {
    // First, handle the self-update for ABCUPDATER itself
    $transient = abcupdater_check_for_plugin_self_update( $transient );
    // Then, check for other configured plugins
    return abcupdater_check_for_updates( $transient, 'plugin' );
}, 20 );


// ===================================================================================
// 4. THEME UPDATER - SELF-HEALING FUNCTION
// ===================================================================================

function abcupdater_cleanup_after_theme_update( $response, $hook_extra, $result ) {
    if ( is_wp_error( $result ) || ! isset( $hook_extra['theme'] ) ) { return $response; }
    
    $updated_theme_slug = $hook_extra['theme'];
    $old_file_path = get_theme_root() . '/' . $updated_theme_slug . '/inc/classes/class-autoupdates.php';
    if ( file_exists( $old_file_path ) ) { unlink( $old_file_path ); }

    $functions_path = get_theme_root() . '/' . $updated_theme_slug . '/functions.php';
    if ( is_writable( $functions_path ) ) {
        $content = file_get_contents( $functions_path );
        $new_content = preg_replace( "/^.*'class-autoupdates\.php'.*$/m", '', $content );
        if ( $new_content !== $content ) { file_put_contents( $functions_path, $new_content ); }
    }
    return $response;
}
add_filter( 'upgrader_post_install', 'abcupdater_cleanup_after_theme_update', 10, 3 );


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
        $transient->response[ $plugin_slug ] = (object) [
            'slug'        => $plugin_slug,
            'new_version' => $new_version,
            'url'         => $release_data->html_url,
            'package'     => $release_data->assets[0]->browser_download_url,
        ];
    }

    return $transient;
}
