<?php
/**
 * ABCUPDATER Main Class
 *
 * @package ABCUPDATER
 * @subpackage Includes
 */

namespace ABCUPDATER\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class ABCUPDATER_Main
 *
 * The main plugin class responsible for loading other classes and registering hooks.
 */
class ABCUPDATER_Main {

    /**
     * The single instance of the class.
     *
     * @var ABCUPDATER_Main
     */
    protected static $instance = null;

    /**
     * The updater instance.
     *
     * @var ABCUPDATER_Updater
     */
    public $updater;

    /**
     * The admin instance.
     *
     * @var ABCUPDATER_Admin
     */
    public $admin;

    /**
     * Main ABCUPDATER_Main Instance.
     *
     * Ensures only one instance of ABCUPDATER_Main is loaded or can be loaded.
     *
     * @return ABCUPDATER_Main - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define constants.
     */
    private function define_constants() {
        if ( ! defined( 'ABCUPDATER_VERSION' ) ) {
            define( 'ABCUPDATER_VERSION', '0.13.5' );
        }
        if ( ! defined( 'ABCUPDATER_PLUGIN_DIR' ) ) {
            define( 'ABCUPDATER_PLUGIN_DIR', plugin_dir_path( ABCUPDATER_PLUGIN_FILE ) );
        }
        if ( ! defined( 'ABCUPDATER_PLUGIN_URL' ) ) {
            define( 'ABCUPDATER_PLUGIN_URL', plugin_dir_url( ABCUPDATER_PLUGIN_FILE ) );
        }
    }

    /**
     * Include required files.
     */
    private function includes() {
        require_once ABCUPDATER_PLUGIN_DIR . 'includes/class-abcupdater-updater.php';
        require_once ABCUPDATER_PLUGIN_DIR . 'includes/class-abcupdater-admin.php';
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
    }

    /**
     * Runs on plugins_loaded action.
     * Initializes the plugin components.
     */
    public function on_plugins_loaded() {
        $this->updater = new ABCUPDATER_Updater();
        $this->admin   = new ABCUPDATER_Admin( $this->updater );

        // Initialize the update checking hooks.
        $this->updater->init_hooks();
    }
}
