<?php
/**
 * Settings component
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */

namespace Workfolio\Portfolio\Meta;

use Backdrop\Contracts\Bootable;

class Component implements Bootable {

	/**
	 * Boot the component.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function boot(): void {

        add_action( 'add_meta_boxes', array( $this, 'add_theme_slug_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_theme_slug_meta_box' ) );
	}

    public function add_theme_slug_meta_box($post_type) {
        $post_types = array('portfolio');
        $post_types = apply_filters('theme_slug_meta_box_post_type', $post_types);
        
        if (in_array($post_type, $post_types, true)) {
            add_meta_box(
                'theme_slug_meta_box', esc_html__('Theme Slug', 'rudimentary-information', 'theme_slug_meta_box_nonce'), array($this, 'theme_slug_meta_box_content'), $post_type, 'side', 'high'
            );
        }
    }

    public function save_theme_slug_meta_box($post_id) {
		if (!isset($_POST['theme_slug_meta_box_nonce'])) {
			return $post_id;
		}
        
        $nonce = $_POST['theme_slug_meta_box_nonce'];
		if (!wp_verify_nonce($nonce, 'theme_slug_meta_box_inner_nonce')) {
			return $post_id;
		}
        
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}
        
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return $post_id;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return $post_id;
			}
		}
        
		$data_slug = sanitize_text_field(
            $_POST['theme_slug_field']);
		update_post_meta($post_id, '_theme_slug', $data_slug);
    }
    
	public function theme_slug_meta_box_content($post) {
		wp_nonce_field('theme_slug_meta_box_inner_nonce', 'theme_slug_meta_box_nonce');
		$slug = get_post_meta($post->ID, '_theme_slug', true);
		?>
		<label for="theme_slug_field">
			<?php esc_html_e('Please enter a theme slug to be attach to the Jetpack Portfolio Custom Post Type.', 'rudimentary-information'); ?>
		</label>
        <p>
		<input class="widefat" type="text" id="theme_slug_field" name="theme_slug_field" value="<?php echo esc_attr($slug); ?>" />
        </p>
		<?php
	}
}
