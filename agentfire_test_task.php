<?php
/**
 * AgentFire Test Task
 *
 * @package    WHMCS
 * @author     Alex Ulko <alex@agentfire.com>
 * @copyright  Copyright (c) AgentFire 2023
 * @link      https://agentfire.com
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

require_once __DIR__ . '/init.php';

function agentfire_test_task_config() {
	$configarray = [
		'name'        => 'AgentFire Test Task',
		'description' => 'AgentFire Test Task',
		'version'     => AGENTFIRE_TEST_TASK_VERSION,
		'author'      => '',
		'language'    => 'english',
		'fields'      => [
			'api_key'       => [
				'FriendlyName' => 'API Key',
				'Type'         => 'text',
				'Size'         => '100',
				'Description'  => 'AgentFire API server key',
				'Default'      => '',
			],
			'slack_channel' => [
				'FriendlyName' => 'Slack Channel',
				'Type'         => 'text',
				'Size'         => '100',
				'Description'  => 'Channel Name or ID',
				'Default'      => '',
			],
			'slack_webhook' => [
				'FriendlyName' => 'Slack Webhook',
				'Type'         => 'text',
				'Size'         => '200',
				'Description'  => 'Slack Webhook URL',
				'Default'      => '',
			],
			'rest_endpoint' => [
				'FriendlyName' => 'REST Endpoint',
				'Type'         => 'text',
				'Size'         => '200',
				'Description'  => 'REST Endpoint URL',
				'Default'      => '',
			]
		],
	];
	return $configarray;
}

function agentfire_test_task_activate() {
	if (!Capsule::schema()->hasTable('mod_agentfire_test_task_cron')) {
		Capsule::schema()->create('mod_agentfire_test_task_cron', function($table) {
			$table->increments('id');
            $table->longText('payload');
			$table->enum('status', ['pending', 'running', 'completed', 'failed']);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
		});
	}
}

function agentfire_test_task_deactivate() {
	Capsule::schema()->dropIfExists('mod_agentfire_test_task_cron');
}

function agentfire_test_task_output($vars) {

	if (!empty($_POST)) {
		$exists = Capsule::table('tbladdonmodules')->where('module', 'agentfire_test_task')->where('setting', 'addon_selection')->exists();
		if(!$exists) {
			Capsule::table('tbladdonmodules')->insert([
				'module' => 'agentfire_test_task', 
				'setting' => 'addon_selection', 
				'value' => ''
			]);
		}

		$addon_selection = implode(",", $_POST['addon_selection']);
		Capsule::table('tbladdonmodules')->where('module', 'agentfire_test_task')->where('setting', 'addon_selection')->update(['value' => $addon_selection]);		
		
		header("Location: " . $vars['modulelink']);
		exit;
	}

	addon_modules_form();
	global $CONFIG;
    AgentFire\Test_Task::get_instance()->set_config($CONFIG);
    AgentFire\Test_Task\Admin::get_instance()->output($vars);	
}

function addon_modules_form() {
	$selected_addons = Capsule::table('tbladdonmodules')->where('module', 'agentfire_test_task')->where('setting','addon_selection')->value('value');
	$selected_addons = ($selected_addons) ? explode(',', $selected_addons) : [];
	$addon_options   = Capsule::table('tbladdons')->pluck('name', 'id')->toArray();	

	$addon_modules_form = "<form method='post' action='addonmodules.php'>";
	$addon_modules_form .= "<h2>Choose the modules for which you want to get notified.</h2>";
	foreach ($addon_options as $key => $option) {
		$checked = in_array($key, $selected_addons) ? 'checked' : '';
		$addon_modules_form .= "<label>";
		$addon_modules_form .= "<input type='checkbox' name='addon_selection[]' value='$key' $checked>";
		$addon_modules_form .= " $option";
		$addon_modules_form .= "</label>";
		$addon_modules_form .= "</br>";
	}
	$addon_modules_form .= "<input type='hidden' name='module' value='agentfire_test_task'><br><input type='submit' name='submit' value='Submit'><br></form><br>";
	echo $addon_modules_form;
}