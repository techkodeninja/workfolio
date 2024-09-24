<?php
namespace Workfolio\Portfolio\Widget\Themes;

use WP_Widget;
use Workfolio\Portfolio\Themes\Component as ThemeComponent;

class Component extends WP_Widget {

	public function __construct() {
		$widget_options = [
			'classname' => 'theme_widget',
			'description' => __('A widget to output theme information if a slug is set through the meta box.', 'workfolio'),
		];

		parent::__construct('portfolio_theme_info', 'Theme Info', $widget_options);
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id('title' ) ); ?>"><?php esc_html_e('Title: ', 'workfolio' ); ?></label><br />
			<input class="widefat" type="text" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($title); ?>">
		</p>
		<?php
	}

	/**
	 * Output the widget content on the front-end
	 */
	public function widget( $args, $instance ) {
		// Set global $post
		global $post;

		// Set the slug to the post type.
		$slug = $post->post_name;

		// Use the existing Component class to fetch the theme data
		$theme_component = new ThemeComponent();

		ob_start();
		echo wp_kses_post( $args['before_widget'] ) . wp_kses_post( $args['before_title'] ) . wp_kses_post( apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'] );

		// Call the existing method to display the latest release for the current post's slug (repository)
		$theme_component->display_latest_release( $slug );

		echo wp_kses_post( $args['after_widget'] );
	}
}
