<?php

namespace AgentFire;

if (!defined('WHMCS')) {
	die('This file cannot be accessed directly');
}

require_once(__DIR__.'/init.php');

Test_Task::get_instance()->add_hooks();
