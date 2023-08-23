<?php

declare( strict_types=1 );

namespace AgentFire\Test_Task;

use AgentFire\Test_Task;
use AgentFire\Test_Task\Traits\Singleton;

/**
 * @package AgentFire\Test_Task
 */
class Admin {
	use Singleton;

	public function __construct() {
	}

	public function output($vars) {
		try {
			Template::get_instance()->display('admin.html', [
				// template data
                'cron_tasks' => Cron::get_instance()->get_tasks(),
			]);
		} catch (Exception\Template $e) {
			// printf('<div class="error">%s</div>', $e->getMessage());
		}
	}
}
