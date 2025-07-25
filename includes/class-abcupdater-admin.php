<?php
/**
 * ABCUPDATER Admin Class
 *
 * @package ABCUPDATER
 * @subpackage Includes
 */

namespace ABCUPDATER\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class ABCUPDATER_Admin
 *
 * Handles all admin-facing functionality, such as settings pages and notices.
 */
class ABCUPDATER_Admin {

    /**
     * The updater instance.
     *
     * @var ABCUPDATER_Updater
     */
    private $updater;

    /**
     * Constructor.
     *
     * @param ABCUPDATER_Updater $updater The updater instance.
     */
    public function __construct( $updater ) {
        $this->updater = $updater;

        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

        // AJAX hooks.
        add_action( 'wp_ajax_abcupdater_test_connection', array( $this, 'ajax_test_connection' ) );
        add_action( 'wp_ajax_abcupdater_get_latest_news', array( $this, 'ajax_get_latest_news' ) );
    }

    /**
     * Registers admin menu pages.
     */
    public function register_admin_menu() {
        add_menu_page(
            __( 'ABCUPDATER Dashboard', 'abcupdater' ),
            'ABCUPDATER',
            'manage_options',
            'abcupdater_dashboard',
            array( $this, 'render_dashboard_page' ),
            'dashicons-update-alt',
            90
        );

        add_submenu_page(
            'abcupdater_dashboard',
            __( 'Update Manager Settings', 'abcupdater' ),
            __( 'Settings', 'abcupdater' ),
            'manage_options',
            'abcupdater_settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Renders the dashboard page.
     */
    public function render_dashboard_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'ABCUPDATER Dashboard', 'abcupdater' ); ?></h1>
            <div id="dashboard-widgets-wrap">
                <div id="dashboard-widgets" class="metabox-holder">
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="meta-box-sortables">
                            <div class="postbox">
                                <h2 class="hndle"><span><?php esc_html_e( 'Latest News & Releases', 'abcupdater' ); ?></span></h2>
                                <div class="inside">
                                    <p><?php esc_html_e( 'Loading...', 'abcupdater' ); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Renders the settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Update Manager Settings', 'abcupdater' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'abcupdater_settings_group' );
                do_settings_sections( 'abcupdater_settings' );
                submit_button( __( 'Save Settings', 'abcupdater' ) );
                ?>
            </form>
        </div>
        <?php
        // Add the JS template for a new project.
        $this->render_project_template();
    }

    /**
     * Registers settings and fields.
     */
    public function register_settings() {
        register_setting( 'abcupdater_settings_group', 'abcupdater_projects' );

        add_settings_section(
            'abcupdater_projects_section',
            __( 'Managed Projects', 'abcupdater' ),
            null,
            'abcupdater_settings'
        );

        add_settings_field(
            'abcupdater_projects_field',
            '', // No label needed.
            array( $this, 'render_projects_field' ),
            'abcupdater_settings',
            'abcupdater_projects_section'
        );
    }

    /**
     * Renders the main projects field which contains the dynamic list.
     */
    public function render_projects_field() {
        $projects = get_option( 'abcupdater_projects', array() );
        ?>
        <div id="abcupdater-projects-wrapper">
            <div id="abcupdater-project-list">
                <?php if ( empty( $projects ) ) : ?>
                    <p id="abcupdater-no-projects"><?php esc_html_e( 'No projects configured. Click "Add Project" to start.', 'abcupdater' ); ?></p>
                <?php else : ?>
                    <?php foreach ( $projects as $index => $project ) : ?>
                        <?php $this->render_project_box( $index, $project ); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" class="button" id="abcupdater-add-project"><?php esc_html_e( 'Add Project', 'abcupdater' ); ?></button>
        </div>
        <?php
    }

    /**
     * Renders a single project configuration box.
     *
     * @param int   $index The project index.
     * @param array $project The project data.
     */
    private function render_project_box( $index, $project = array() ) {
        $project_type = isset( $project['project_type'] ) ? $project['project_type'] : 'plugin';
        $slug         = isset( $project['slug'] ) ? $project['slug'] : '';
        $repo         = isset( $project['github_repo'] ) ? $project['github_repo'] : '';
        $token        = isset( $project['github_token'] ) ? $project['github_token'] : '';
        ?>
        <div class="abcupdater-project-box postbox">
            <h2 class="hndle">
                <span><?php esc_html_e( 'Project', 'abcupdater' ); ?> #<span class="project-index"><?php echo esc_html( $index + 1 ); ?></span></span>
                <button type="button" class="button-link abcupdater-remove-project"><?php esc_html_e( 'Remove', 'abcupdater' ); ?></button>
            </h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'Project Type', 'abcupdater' ); ?></label></th>
                        <td>
                            <select name="abcupdater_projects[<?php echo esc_attr( $index ); ?>][project_type]">
                                <option value="plugin" <?php selected( $project_type, 'plugin' ); ?>><?php esc_html_e( 'Plugin', 'abcupdater' ); ?></option>
                                <option value="theme" <?php selected( $project_type, 'theme' ); ?>><?php esc_html_e( 'Theme', 'abcupdater' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'Plugin File / Theme Folder', 'abcupdater' ); ?></label></th>
                        <td><input type="text" class="regular-text" name="abcupdater_projects[<?php echo esc_attr( $index ); ?>][slug]" value="<?php echo esc_attr( $slug ); ?>" placeholder="e.g., my-plugin/my-plugin.php"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'GitHub Repository', 'abcupdater' ); ?></label></th>
                        <td><input type="text" class="regular-text" name="abcupdater_projects[<?php echo esc_attr( $index ); ?>][github_repo]" value="<?php echo esc_attr( $repo ); ?>" placeholder="e.g., owner/repo-name"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'GitHub Token', 'abcupdater' ); ?></label></th>
                        <td>
                            <input type="password" class="regular-text" name="abcupdater_projects[<?php echo esc_attr( $index ); ?>][github_token]" value="<?php echo esc_attr( $token ); ?>" placeholder="<?php esc_attr_e( 'Required for private repos', 'abcupdater' ); ?>">
                            <button type="button" class="button abcupdater-test-connection"><?php esc_html_e( 'Test Connection', 'abcupdater' ); ?></button>
                            <span class="abcupdater-test-status"></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Renders the JS template for a new project.
     */
    private function render_project_template() {
        ?>
        <script type="text/template" id="tmpl-abcupdater-project-template">
            <?php $this->render_project_box( '__INDEX_RAW__' ); ?>
        </script>
        <?php
    }

    /**
     * Enqueues admin styles and scripts.
     *
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_styles_scripts( $hook_suffix ) {
        // Only load on our plugin's pages.
        if ( 'toplevel_page_abcupdater_dashboard' !== $hook_suffix && 'abcupdater_page_abcupdater_settings' !== $hook_suffix ) {
            return;
        }

        wp_enqueue_style(
            'abcupdater-admin-styles',
            ABCUPDATER_PLUGIN_URL . 'css/dashboard-styles.css',
            array(),
            ABCUPDATER_VERSION
        );

        if ( 'toplevel_page_abcupdater_dashboard' === $hook_suffix ) {
            wp_enqueue_script(
                'abcupdater-dashboard-scripts',
                ABCUPDATER_PLUGIN_URL . 'js/dashboard-scripts.js',
                array( 'jquery' ),
                ABCUPDATER_VERSION,
                true
            );
            wp_localize_script(
                'abcupdater-dashboard-scripts',
                'abcupdater_dashboard_ajax',
                array(
                    'nonce' => wp_create_nonce( 'abcupdater_dashboard_nonce' ),
                )
            );
        }

        if ( 'abcupdater_page_abcupdater_settings' === $hook_suffix ) {
            wp_enqueue_script(
                'abcupdater-admin-scripts',
                ABCUPDATER_PLUGIN_URL . 'js/admin-scripts.js',
                array( 'jquery' ),
                ABCUPDATER_VERSION,
                true
            );
            wp_localize_script(
                'abcupdater-admin-scripts',
                'abcupdater_ajax',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'abcupdater_ajax_nonce' ),
                )
            );
        }
    }

    /**
     * Handles the AJAX request to test a GitHub connection.
     */
    public function ajax_test_connection() {
        check_ajax_referer( 'abcupdater_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'abcupdater' ) ) );
        }

        $repo  = isset( $_POST['repo'] ) ? sanitize_text_field( wp_unslash( $_POST['repo'] ) ) : '';
        $token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

        if ( empty( $repo ) ) {
            wp_send_json_error( array( 'message' => __( 'Repository slug is required.', 'abcupdater' ) ) );
        }

        // The actual logic is in the Updater class.
        $result = $this->updater->test_github_connection( $repo, $token );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => __( 'Connection successful!', 'abcupdater' ) ) );
    }

    /**
     * Handles the AJAX request to get the latest news.
     */
    public function ajax_get_latest_news() {
        check_ajax_referer( 'abcupdater_dashboard_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'abcupdater' ) ) );
        }

        // The actual logic is in the Updater class.
        $releases = $this->updater->get_latest_releases();

        if ( is_wp_error( $releases ) ) {
            wp_send_json_error( array( 'message' => $releases->get_error_message() ) );
        }

        wp_send_json_success( $releases );
    }
}
