<?php

declare( strict_types=1 );

namespace AgentFire;

use AgentFire\Test_Task\Admin;
use AgentFire\Test_Task\Cron;
use AgentFire\Test_Task\Traits\Singleton;
use AgentFire\Test_Task\Widget;
use WHMCS\Database\Capsule;


/**
 * @package AgentFire\Test_Task
 */
class Test_Task {
	use Singleton;

    public $config = null;

	var $addon_actions = [
		'AddonAdd',
		'AddonEdit',
		'AddonUnsuspended',
		'AddonActivated',
		'AddonSuspended',
		'AddonTerminated',
		'AddonCancelled'
	];

	public function __construct() {
		Admin::get_instance();
	}

    public function set_config($config) {
        $this->config = $config;
    }

	public function add_hooks() {
		foreach ($this->addon_actions as $addon_action) {
			add_hook($addon_action, 1, [$this, 'addon_edited']);
		}
		add_hook('AddonDeleted', 1, [$this, 'addon_deleted']);

		add_hook('AdminHomeWidgets', 1, function() {
			return new Widget();
		});
	}

	/**
	 * @param array $vars
	 */
	public function addon_edited($vars) {
		$hosting_addon 		= $this->get_hosting_addon($vars['id']);
		$vars["hostingid"]	= $hosting_addon->hostingid;
		$vars["status"] 	= $hosting_addon->status;
		$vars["action"] 	= "edit";
		Cron::get_instance()->queue([
			'vars' => $vars
		]);
	}

	/**
	 * @param array $vars
	 */
	public function addon_deleted($vars) {
		$hosting_addon 		= $this->get_hosting_addon($vars['id']);
		$vars["userid"] 	= $hosting_addon->userid;
		$vars["addonid"] 	= $hosting_addon->addonid;
		$vars["hostingid"] 	= $hosting_addon->hostingid;
		$vars["status"] 	= $hosting_addon->status;
		$vars["action"] 	= "delete";
		Cron::get_instance()->queue([
			'vars' => $vars
		]);
	}

	/**
	 * @param integer $id
	 */
	private function get_hosting_addon($id){
		return Capsule::table('tblhostingaddons')->where('id',$id)->select('id','hostingid','status','userid','addonid')->first();
	}
}
