<?php
/**
 * Settings service provider.
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */

namespace Workfolio\Portfolio\Meta;

use Backdrop\Core\ServiceProvider;

/**
 * Sidebar Provider.
 *
 * @since  2.0.0
 * @access public
 */
class Provider extends ServiceProvider {
	/**
	 * Binds the implementation of the attributes contract to the container.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function register(): void {
		$this->app->singleton( 'bcp/portfolio/meta', Component::class );

	}

	/**
	 * Boots the implementation of the attributes contract to the container.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function boot(): void {
		$this->app->resolve( 'bcp/portfolio/meta' )->boot();
	}
}