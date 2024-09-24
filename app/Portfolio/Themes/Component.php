<?php
/**
 * Settings component
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/workfolio
 */

namespace Workfolio\Portfolio\Themes;

use Backdrop\Contracts\Bootable;

class Component implements Bootable {

	/**
	 * Settings page name.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var string
	 */
	public $settings_page = '';

	/**
	 * Sets up custom admin menus.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function admin_menu() {
		// Create the settings page.
		$this->settings_page = add_submenu_page(
			'edit.php?post_type=' . 'portfolio',
			esc_html__( 'ClassicPress Settings', 'workfolio' ),
			esc_html__( 'ClassicPress', 'workfolio' ),
			'manage_options',
			'cp-settings',
			array( $this, 'settings_page' )
		);

		if ( $this->settings_page ) {
			// Register settings.
			add_action( 'admin_init', array( $this, 'register_settings' ) );

            // Fetch and store `active_installs` in the transient on the admin side.
            add_action( 'admin_init', array( $this, 'fetch_active_installs' ) );
		}
	}

	/**
	 * Registers the theme settings.
	 *
	 * @return array
	 * @since  1.0.0
	 * @access public
	 */
	public function register_settings() {
		$items = [];

		$type = [
			'post_type' => 'portfolio',
			'numberposts' => -1,
		];

		$posts = get_posts( $type );

		foreach ( $posts as $post ) {
			$items[] = $post->post_name;
		}
		$items = array_unique( $items );
		asort( $items );

		return $items;
	}

    public function fetch_active_installs() {
        // List of repositories
        $slugs = $this->register_settings();
    
        foreach ( $slugs as $repository ) {
            // Fetch theme data from WordPress.org using `themes_api()`
            if ( ! function_exists( 'themes_api' ) && file_exists( trailingslashit( ABSPATH ) . 'wp-admin/includes/theme.php' ) ) {
                require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/theme.php' );
            }
    
            if ( function_exists( 'themes_api' ) ) {
                $args = array(
                    'slug'   => $repository,
                    'fields' => array(
                        'active_installs' => true,
                    ),
                );
                $theme_info = themes_api( 'theme_information', $args );
    
                // Store the `active_installs` in a transient if available
                if ( ! is_wp_error( $theme_info ) && isset( $theme_info->active_installs ) ) {
                    set_transient( 'luthemes_active_installs_' . $repository, $theme_info->active_installs, 12 * HOUR_IN_SECONDS );
                }
            }
        }
    }
    

	/**
	 * General section callback.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function section_general() { ?>
		<p class="description">
			<?php esc_html_e( 'General portfolio settings for your site.', 'workfolio' ); ?>
		</p>
	<?php }

	/**
	 * Renders the settings page.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function settings_page() {
		?>
		<div class="wrap">
			<h1>Latest Theme Releases</h1>

			<?php

			$slugs = $this->register_settings();

			echo '<ul class="grid-items" style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; grid-gap: 1.125rem;">';
			foreach ( $slugs as $repo ) {
				echo '<li class="grid-items">';
					$this->display_latest_release( $repo );
				echo '</li>';
			}
			echo '</ul>';
			?>

		</div>
		<?php
	}

    public function display_latest_release( $repository ) {
        $transient_base = 'luthemes_portfolio_theme_';
        $transient = $transient_base . $repository;
        $theme = get_transient( $transient );
    
        if ( ! $theme ) {
            // First, attempt to fetch from ClassicPress Directory API
            $url_classicpress = "https://directory.classicpress.net/wp-json/wp/v2/themes?byslug=" . $repository;
    
            $response_classicpress = wp_remote_get( $url_classicpress );
            $theme = json_decode( wp_remote_retrieve_body( $response_classicpress ), true );
    
            // If no theme data is found, fallback to WordPress API using `themes_api()`
            if ( empty( $theme ) || ! is_array( $theme ) ) {
                // If `themes_api()` isn't available, load the file that holds the function
                if ( ! function_exists( 'themes_api' ) && file_exists( trailingslashit( ABSPATH ) . 'wp-admin/includes/theme.php' ) ) {
                    require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/theme.php' );
                }
    
                // Fetch theme information using `themes_api()`
                if ( function_exists( 'themes_api' ) ) {
                    $args = array(
                        'slug'   => $repository,
                        'fields' => array(
                            'description'     => true,
                            'sections'        => true,
                            'rating'          => true,
                            'ratings'         => false,
                            'downloaded'      => true,
                            'downloadlink'    => true,
                            'last_updated'    => true,
                            'homepage'        => true,
                            'tags'            => true,
                            'template'        => true,
                            'parent'          => true,
                            'versions'        => false,
                            'screenshot_url'  => true,
                        ),
                    );
                    $theme = themes_api( 'theme_information', $args );
    
                    // Cache the response for 60 seconds
                    set_transient( $transient, $theme, 60 );
                }
            }
        }
    
        if ( empty( $theme ) || is_wp_error( $theme ) ) {
            echo '<div class="notice notice-info"><p>No theme data found for slug: ' . esc_html( $repository ) . '</p></div>';
        } else {
            // Check if the theme data is an array (ClassicPress) or an object (WordPress)
            if ( is_array( $theme ) ) {
                // Theme data retrieved from ClassicPress API
                $theme_data = $theme[0];
                $name = $theme_data['title']['rendered'] ?? $repository;
                $version = $theme_data['meta']['current_version'] ?? 'N/A';
                $cp_version = $theme_data['meta']['requires_cp'] ?? 'N/A';
                $php_version = $theme_data['meta']['requires_php'] ?? 'N/A';
                $download_link = $theme_data['meta']['download_link'] ?? '';
                $active = $theme_data['meta']['active_installations'] ?? 'N/A';
                $published_at = $theme_data['meta']['published_at'] ?? '';
                $last_updated = !empty( $published_at ) ? gmdate( 'F d, Y', $published_at ) : 'N/A';
                $parse_link = wp_parse_url( $download_link );
                $path_segments = explode('/', $parse_link['path']);
                $base_url = $parse_link['scheme'] . '://' . $parse_link['host'] . '/' . $path_segments[1] . '/' . $path_segments[2];
            } else {
                // Theme data retrieved from WordPress API using `themes_api()`
                $name = $theme->name ?? $repository;
                $version = $theme->version ?? 'N/A';
                $php_version = $theme->requires_php ?? 'N/A';
                $download_link = $theme->download_link ?? '';
                $base_url = 'https://github.com/luthemes/' . $theme->slug;
    
                // Fetch active installs from the transient
                $active = get_transient( 'luthemes_active_installs_' . $repository ) ?? 'N/A';
                $last_updated = !empty( $theme->last_updated ) ? gmdate( 'F d, Y', strtotime( $theme->last_updated ) ) : 'N/A';
            }
    
            // Display the theme information
            echo '<h3 class="widget-title">' . esc_html( $name ) . '</h3>';
            echo '<table class="theme-info widefat fixed striped">';
            echo '<tbody>';
            echo '<tr>';
            echo '<th style="text-align: left;">' . esc_html__( 'Version', 'workfolio' ) . '</th>';
            echo '<td style="text-align: right;">' . esc_html( $version ) . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<th style="text-align: left;">' . esc_html__( 'Last Updated', 'workfolio' ) . '</th>';
            echo '<td style="text-align: right;">' . esc_html( $last_updated ) . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<th style="text-align: left;">' . esc_html__( 'Requires PHP: ', 'workfolio' ) . '</th>';
            echo '<td style="text-align: right;">' . esc_html( $php_version ) . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<th style="text-align: left;">' . esc_html__( 'Repository: ', 'workfolio' ) . '</th>';
            echo '<td style="text-align: right;"><a href="' . esc_url( $base_url ) . '">' . esc_html__( 'GitHub', 'workfolio' ). '</a></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<th style="text-align: left;">' . esc_html__( 'Active Installs ', 'workfolio' ) . '</th>';
            echo '<td style="text-align: right;">' . esc_html( $active ) . '</td>';
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
        }
    }
    
    

    /**
	 * Renders the shortcode content.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 */
	public function shortcode_content( $args ) {
		global $post;

		// Process the shortcode attributes
		$args = shortcode_atts( [
			'repository' => $post->post_name,
		], $args );

		// Extract the repository from the attributes
		$repository = $args['repository'];

		// Call the method to display the latest release
		ob_start();
		$this->display_latest_release( $repository );
		return ob_get_clean();
	}

	/**
	 * Registers the shortcode.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function register_shortcode() {
		add_shortcode( 'latest_release', array( $this, 'shortcode_content' ) );
	}

	/**
	 * Boot the component.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function boot(): void {
		// Custom columns on the edit portfolio items screen.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// Register settings (if any)
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Register shortcode
		add_action( 'init', array( $this, 'register_shortcode' ) );
	}
}
