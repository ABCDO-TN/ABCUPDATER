<?php
/**
 * Plugin Name:       ABCUPDATER
 * Description:       Manages automatic updates created by ABCDO & (Gemini Pro 2.5).
 * Version:           0.10.0
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
// 2. THEME UPDATER - SETTINGS PAGE
// ===================================================================================

function abcupdater_add_admin_menu() {
    add_options_page('Theme Update Manager', 'Theme Update Manager', 'manage_options', 'abcdo_updater_settings', 'abcupdater_settings_page_html');
}
add_action( 'admin_menu', 'abcupdater_add_admin_menu' );

function abcupdater_settings_init() {
    register_setting( 'abcdo_updater_options', 'abcdo_updater_settings' );
    add_settings_section('abcdo_updater_main_section', 'Theme Update Connection Settings', null, 'abcdo_updater_settings');
    add_settings_field('abcdo_updater_fields', 'Connection Details', 'abcupdater_fields_html', 'abcdo_updater_settings', 'abcdo_updater_main_section');
}
add_action( 'admin_init', 'abcupdater_settings_init' );

function abcupdater_fields_html() {
    $options = get_option( 'abcdo_updater_settings', [] );
    ?>
    <p>
        <label for="local_theme_slug" style="font-weight:bold;">1. Local Theme Folder</label><br>
        <input type="text" id="local_theme_slug" name="abcdo_updater_settings[local_theme_slug]" value="<?php echo isset( $options['local_theme_slug'] ) ? esc_attr( $options['local_theme_slug'] ) : ''; ?>" size="50" />
        <p class="description">Enter the folder name of the theme installed on WordPress (e.g., <code>woodmart</code>).</p>
    </p>
    <hr>
    <p>
        <label for="github_repo_slug" style="font-weight:bold;">2. GitHub Update Repository</label><br>
        <input type="text" id="github_repo_slug" name="abcdo_updater_settings[github_repo_slug]" value="<?php echo isset( $options['github_repo_slug'] ) ? esc_attr( $options['github_repo_slug'] ) : ''; ?>" size="50" />
        <p class="description">Enter the name of the repository on GitHub that contains the theme updates (e.g., <code>woodmart-client-updates</code>).</p>
    </p>
    <hr>
    <p>
        <label for="abcdo_github_token" style="font-weight:bold;">3. License Key (Token)</label><br>
        <input type="password" id="abcdo_github_token" name="abcdo_updater_settings[github_token]" value="<?php echo isset( $options['github_token'] ) ? esc_attr( $options['github_token'] ) : ''; ?>" size="50" />
        <p class="description">Enter your license key (GitHub Personal Access Token) to enable theme updates.</p>
    </p>
    <?php
}

function abcupdater_settings_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p>This system allows for secure, direct updates for your theme. Please enter the information provided to you below.</p>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'abcdo_updater_options' );
            do_settings_sections( 'abcdo_updater_settings' );
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}

// ===================================================================================
// 3. THEME UPDATER - UPDATE LOGIC
// ===================================================================================

function abcupdater_check_for_theme_update( $transient ) {
    if ( empty( $transient->checked ) ) { return $transient; }

    $options = get_option( 'abcdo_updater_settings', [] );
    $local_theme_slug = isset($options['local_theme_slug']) ? $options['local_theme_slug'] : '';
    $github_repo_slug = isset($options['github_repo_slug']) ? $options['github_repo_slug'] : '';
    $theme_github_token = isset($options['github_token']) ? $options['github_token'] : '';
    $github_user      = 'ABCDO-TN';

    if ( empty( $local_theme_slug ) || empty( $github_repo_slug ) || empty( $theme_github_token ) ) { return $transient; }
    if ( ! isset( $transient->checked[ $local_theme_slug ] ) ) { return $transient; }
    if ( ! class_exists( 'Parsedown' ) ) { require_once plugin_dir_path( __FILE__ ) . 'Parsedown.php'; }
    
    $api_url = "https://api.github.com/repos/{$github_user}/{$github_repo_slug}/releases/latest";
    $response = wp_remote_get( $api_url, ['headers' => ['Accept' => 'application/vnd.github.v3+json', 'Authorization' => 'token ' . $theme_github_token,]] );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) { return $transient; }

    $release_data = json_decode( wp_remote_retrieve_body( $response ) );

    if ( ! is_object( $release_data ) || empty( $release_data->tag_name ) || empty( $release_data->assets[0]->url ) ) { return $transient; }

    $new_version = ltrim( $release_data->tag_name, 'v' );
    $current_version = $transient->checked[ $local_theme_slug ];

    if ( version_compare( $new_version, $current_version, '>' ) ) {
        $asset_api_url = $release_data->assets[0]->url;
        $asset_response = wp_remote_get($asset_api_url, ['timeout' => 60, 'redirection' => 0, 'headers' => ['Accept' => 'application/octet-stream', 'Authorization' => 'token ' . $theme_github_token,],]);
        $download_url = wp_remote_retrieve_header($asset_response, 'location');

        if ( ! empty($download_url) ) {
            $Parsedown = new Parsedown();
            $changelog = $Parsedown->parse($release_data->body);
            $transient->response[ $local_theme_slug ] = ['theme' => $local_theme_slug, 'new_version' => $new_version, 'url' => $release_data->html_url, 'package' => $download_url, 'sections' => ['description' => $changelog,],];
        }
    }
    return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'abcupdater_check_for_theme_update', 20 );

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

function abcupdater_check_for_plugin_update( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    // --- Plugin's own details (hard-coded as it knows itself) ---
    $plugin_slug = plugin_basename( __FILE__ );
    $github_user = 'ABCDO-TN';
    $github_repo = 'ABCUPDATER';

    // No token needed as the plugin's repository is public.
    $api_url = "https://api.github.com/repos/{$github_user}/{$github_repo}/releases/latest";
    $response = wp_remote_get( $api_url );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return $transient;
    }

    $release_data = json_decode( wp_remote_retrieve_body( $response ) );
    
    if ( ! is_object( $release_data ) || empty( $release_data->tag_name ) || empty( $release_data->assets[0]->browser_download_url ) ) {
        return $transient;
    }
    
    // Get the current installed version
    $plugin_data = get_plugin_data( __FILE__ );
    $current_version = $plugin_data['Version'];
    $new_version = ltrim( $release_data->tag_name, 'v' );

    // Compare versions and update transient if a new version is available.
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
add_filter( 'pre_set_site_transient_update_plugins', 'abcupdater_check_for_plugin_update' );
