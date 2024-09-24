<?php
/**
 *
 */

namespace Workfolio\Portfolio\Widget;

use Workfolio\Portfolio\Widget\Themes\Component as Themes;
// use Succotash\Portfolio\Widget\Subjects\Component as Subjects;

use Backdrop\Contracts\Bootable;


class Component implements Bootable{

	public function theme_info() {
		register_widget( Themes::class );
	}

	public function boot(): void {

		add_action('widgets_init',  [ $this, 'theme_info'] );
	}
}