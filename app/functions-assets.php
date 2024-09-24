<?php
/**
 * Workfolio ( functions-scripts.php )
 *
 * @package   Workfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2024 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://luthemes.com/portfolio/workfolio
 */

/**
 * Define namespace
 */
namespace Workfolio;

use function Backdrop\Mix\childAsset;

/**
 * Enqueue Scripts and Styles
 *
 * @since  1.0.0
 * @access public
 * @return void
 *
 * @link   https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 * @link   https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 */
add_action( 'wp_enqueue_scripts', function() {

	// Rather than enqueue the main style.css stylesheet, we are going to enqueue screen.css.
	wp_enqueue_style( 'workfolio-screen', childAsset( 'css/screen.css' ), null, null );

	// Enqueue theme scripts
	wp_enqueue_script( 'workfolio-app', childAsset( 'js/app.js' ), [ 'jquery' ], null, true );
} );