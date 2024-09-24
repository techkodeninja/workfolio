<?php
/**
 * Backdrop Core ( src/Tools/ServiceProvider.php )
 *
 * @package   Backdrop Core
 * @copyright Copyright (C) 2019-2021. Benjamin Lu
 * @license   GNU General PUblic License v2 or later ( https://www.gnu.org/licenses/gpl-2.0.html )
 * @author    Benjamin Lu ( https://getbenonit.com )
 */

/**
 * Define namespace
 */
namespace Workfolio\Portfolio\Widget;

use Backdrop\Core\ServiceProvider;

/**
 * Attr provider class.
 *
 * @since  1.0.0
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
		$this->app->singleton( 'succotash/portfolio/widget/theme', Component::class );

	}

	public function boot(): void {
		$this->app->resolve( 'succotash/portfolio/widget/theme' )->boot();
	}
}