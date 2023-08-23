<?php

declare( strict_types=1 );

namespace AgentFire\Test_Task\Traits;

/**
 * @package AgentFire\Test_Task
 */
trait Singleton {
	/**
	 * @return self;
	 */
	public static function get_instance() {
		static $_instance = null;
		$class = __CLASS__;

		return $_instance ?: $_instance = new $class;
	}

	private function __construct() {
	}

	public function __clone() {
	}

	public function __wakeup() {
	}
}
