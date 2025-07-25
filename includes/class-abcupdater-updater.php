<?php
/**
 * ABCUPDATER Updater Class
 *
 * @package ABCUPDATER
 * @subpackage Includes
 */

namespace ABCUPDATER\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class ABCUPDATER_Updater
 *
 * Handles the core update logic for themes and plugins.
 */
class ABCUPDATER_Updater {

    /**
     * The main plugin file path for self-updating.
     *
     * @var string
     */
    private $plugin_path;

    /**
     * The plugin slug for self-updating.
     *
     * @var string
     */
    private $plugin_slug;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->plugin_path = ABCUPDATER_PLUGIN_FILE;
        $this->plugin_slug = plugin_basename( $this->plugin_path );
    }

    /**
     * Initialize the update checks.
     */
    public function init_hooks() {
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_plugin_updates' ) );
        add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_theme_updates' ) );
        add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
    }

    /**
     * Checks for plugin updates.
     *
     * @param object $transient The WordPress update transient.
     * @return object The modified transient.
     */
    public function check_plugin_updates( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $projects = get_option( 'abcupdater_projects', array() );

        // Add self-updater project.
        $projects['self_updater'] = array(
            'project_type' => 'plugin',
            'slug'         => $this->plugin_slug,
            'github_repo'  => 'ABCDO-TN/ABCUPDATER',
            'github_token' => '', // Public repo.
        );

        foreach ( $projects as $project ) {
            if ( 'plugin' !== $project['project_type'] ) {
                continue;
            }

            $current_version = isset( $transient->checked[ $project['slug'] ] ) ? $transient->checked[ $project['slug'] ] : '0';
            $release_info    = $this->get_github_release_info( $project['github_repo'], $project['github_token'] );

            if ( $release_info && version_compare( $release_info->tag_name, $current_version, '>' ) ) {
                $transient->response[ $project['slug'] ] = (object) array(
                    'slug'        => dirname( $project['slug'] ),
                    'plugin'      => $project['slug'],
                    'new_version' => $release_info->tag_name,
                    'url'         => $release_info->html_url,
                    'package'     => $release_info->zipball_url,
                    'icons'       => array(
                        'default' => ABCUPDATER_PLUGIN_URL . 'assets/icon-256x256.jpg',
                    ),
                );
            }
        }

        return $transient;
    }

    /**
     * Checks for theme updates.
     *
     * @param object $transient The WordPress update transient.
     * @return object The modified transient.
     */
    public function check_theme_updates( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $projects = get_option( 'abcupdater_projects', array() );

        foreach ( $projects as $project ) {
            if ( 'theme' !== $project['project_type'] ) {
                continue;
            }

            $theme           = wp_get_theme( $project['slug'] );
            $current_version = $theme->get( 'Version' );
            $release_info    = $this->get_github_release_info( $project['github_repo'], $project['github_token'] );

            if ( $release_info && version_compare( $release_info->tag_name, $current_version, '>' ) ) {
                $transient->response[ $project['slug'] ] = array(
                    'theme'       => $project['slug'],
                    'new_version' => $release_info->tag_name,
                    'url'         => $release_info->html_url,
                    'package'     => $release_info->zipball_url,
                );
            }
        }

        return $transient;
    }

    /**
     * Filter for the plugins_api call.
     *
     * @param false|object|array $result The result object.
     * @param string             $action The type of information being requested.
     * @param object             $args   Plugin API arguments.
     * @return false|object The modified result object.
     */
    public function plugins_api_filter( $result, $action, $args ) {
        if ( 'plugin_information' !== $action || ! isset( $args->slug ) ) {
            return $result;
        }

        $projects = get_option( 'abcupdater_projects', array() );
        $projects['self_updater'] = array(
            'project_type' => 'plugin',
            'slug'         => $this->plugin_slug,
            'github_repo'  => 'ABCDO-TN/ABCUPDATER',
            'github_token' => '',
        );

        foreach ( $projects as $project ) {
            if ( 'plugin' === $project['project_type'] && dirname( $project['slug'] ) === $args->slug ) {
                $release_info = $this->get_github_release_info( $project['github_repo'], $project['github_token'] );

                if ( ! $release_info ) {
                    return $result;
                }

                require_once ABCUPDATER_PLUGIN_DIR . 'Parsedown.php';
                $parsedown = new \Parsedown();

                $result = (object) array(
                    'name'              => esc_html( $release_info->name ),
                    'slug'              => $args->slug,
                    'version'           => esc_html( $release_info->tag_name ),
                    'author'            => '<a href="' . esc_url( $release_info->author->html_url ) . '" target="_blank">' . esc_html( $release_info->author->login ) . '</a>',
                    'requires'          => '5.5',
                    'tested'            => '6.0',
                    'requires_php'      => '7.4',
                    'last_updated'      => esc_html( $release_info->published_at ),
                    'sections'          => array(
                        'description' => 'Latest update from GitHub.',
                        'changelog'   => wp_kses_post( $parsedown->text( $release_info->body ) ),
                    ),
                    'download_link'     => esc_url( $release_info->zipball_url ),
                );
                return $result;
            }
        }

        return $result;
    }

    /**
     * Tests the connection to a GitHub repository.
     *
     * @param string $repo The repository slug.
     * @param string $token The GitHub personal access token.
     * @return true|\WP_Error True on success, WP_Error on failure.
     */
    public function test_github_connection( $repo, $token ) {
        $response = $this->fetch_from_github_api( $repo, $token );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Gets the latest releases for the plugin's own repository.
     *
     * @return array|\WP_Error Array of releases on success, WP_Error on failure.
     */
    public function get_latest_releases() {
        $repo         = 'ABCDO-TN/ABCUPDATER';
        $transient_key = 'abcupdater_news_feed';
        $cached       = get_transient( $transient_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $response = $this->fetch_from_github_api( $repo, '', '/releases' );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        set_transient( $transient_key, $response, HOUR_IN_SECONDS );

        return $response;
    }

    /**
     * Gets the latest release information for a repository.
     *
     * @param string $repo The repository slug.
     * @param string $token The GitHub token.
     * @return object|false The release object or false on failure.
     */
    private function get_github_release_info( $repo, $token ) {
        $transient_key = 'abcupdater_release_' . md5( $repo . $token );
        $cached        = get_transient( $transient_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $response = $this->fetch_from_github_api( $repo, $token, '/releases/latest' );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        set_transient( $transient_key, $response, HOUR_IN_SECONDS );

        return $response;
    }

    /**
     * A generic helper to fetch data from the GitHub API.
     *
     * @param string $repo The repository slug.
     * @param string $token The GitHub token.
     * @param string $endpoint The API endpoint to hit.
     * @return object|array|\WP_Error The decoded JSON response or a WP_Error.
     */
    private function fetch_from_github_api( $repo, $token, $endpoint = '' ) {
        // Security: Validate repo format.
        if ( ! preg_match( '/^[a-zA-Z0-9-]+\/[a-zA-Z0-9-._]+$/', $repo ) ) {
            return new \WP_Error( 'invalid_repo_format', 'Invalid repository format. Please use owner/repo.' );
        }

        $url = 'https://api.github.com/repos/' . $repo . $endpoint;

        $args = array(
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
            ),
        );

        if ( ! empty( $token ) ) {
            $args['headers']['Authorization'] = 'token ' . $token;
        }

        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $http_code = wp_remote_retrieve_response_code( $response );
        $body      = wp_remote_retrieve_body( $response );

        if ( 200 !== $http_code ) {
            $error_data = json_decode( $body );
            $message    = isset( $error_data->message ) ? $error_data->message : 'An unknown error occurred.';
            return new \WP_Error( 'github_api_error', 'GitHub API Error: ' . $message, array( 'status' => $http_code ) );
        }

        return json_decode( $body );
    }
}
