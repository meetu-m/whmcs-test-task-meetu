<?php

declare( strict_types=1 );

namespace AgentFire\Test_Task;

use AgentFire\Test_Task\Integration\Slack;
use AgentFire\Test_Task\Traits\Singleton;
use Curl\Curl;
use WHMCS\Database\Capsule;

/**
 * @package AgentFire\Test_Task
 */
class Cron {
	use Singleton;

	/** 
	 * @param $params
	 */
	public function queue($params) {
		$selected_addons = Capsule::table('tbladdonmodules')->where('module', 'agentfire_test_task')->where('setting','addon_selection')->value('value');
		$selected_addons = ($selected_addons) ? explode(',', $selected_addons) : [];
		$payload = $params['vars'];
		if(in_array($payload['addonid'], $selected_addons)){
			Capsule::table('mod_agentfire_test_task_cron')->insert([
				'payload' 	 => json_encode($payload),
				'status' 	 => 'pending',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			]);
		}
	}

	/** 
	 * @param $params
	 */
	public function process($params) {

		$jobs = Capsule::table('mod_agentfire_test_task_cron')->where('status','pending')->get();

		foreach($jobs as $job){
			Capsule::table('mod_agentfire_test_task_cron')->where('id', $job->id)->update(['status' => 'running', 'updated_at' => date('Y-m-d H:i:s')]);

			$payload = json_decode($job->payload, true);
			$status  = $this->sendUpdateToClient($job);

			if($payload["action"] == "delete"){
				$this->sendSlackNotification($payload["userid"]);
			}

			Capsule::table('mod_agentfire_test_task_cron')->where('id', $job->id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
		}
	
	}

	/** 
	 * @param $job
	 */
	public function sendUpdateToClient($job){

		$end_point_url 	= Capsule::table('tbladdonmodules')->where('module', 'agentfire_test_task')->where('setting','rest_endpoint')->value('value');

		$job_payload   	= json_decode($job->payload, true);
		$hosting    	= Capsule::table('tblhosting')->where('id',$job_payload['hostingid'])->select('id','packageid')->first();

		$payload['clientID']  = intval($job_payload['userid']);
		$payload['productID'] = intval($hosting->packageid);
		$payload['serviceID'] = intval($hosting->id);
		$payload['addonID']   = intval($job_payload['addonid']);
		$payload['hostingAddonID'] = intval($job_payload['id']);
		$payload['oldStatus'] = '';
		$payload['newStatus'] = $job_payload['status'];
	
		$data_string = json_encode($payload);

		$server = Capsule::table('tblservers')->select('ipaddress','hostname','port')->first();
		$ip 		= $server->ipaddress;
		$hostHeader = $server->hostname;
		$port 		= $server->port;
		$customResolve = [
			"$hostHeader:$port:$ip",
		];

		$request = new Curl();
		$request->setHeader('Content-type', 'application/json');
		$request->setHeader('Content-Length', strlen($data_string));
		$request->setHeader('Host', $hostHeader);
		$request->setOpt(CURLOPT_RESOLVE, $customResolve);

		$request->post($end_point_url, $data_string);
		return ($request->error) ? 'failed' : 'completed';
	}

	/**
	 * Get tasks from DB
	 * @return array[]
	 */
	public function get_tasks() {
		return Capsule::table('mod_agentfire_test_task_cron')->orderBy('id','desc')->get()->toArray();
	}

	/** 
	 * @param integer $userid
	 */
	public function sendSlackNotification($userid) {

		$channel = Capsule::table('tbladdonmodules')->where('module', 'agentfire_test_task')->where('setting','slack_channel')->value('value');
		if($channel){
			$username 		= 'AgentFireTestTask';
			$title 			= 'Addon deleted!';
			$icon 			= ':robot_face:';
			$attachments	= $this->getSlackAttachments($userid);
			Slack::get_instance()->send($channel, $username, $title, $icon, $attachments);
		}
	}

	
	/**
	 * @param integer $userid
	 */
	public function getSlackAttachments($userid){
		$client_info 	 = Capsule::table('tblclients')->where('id', $userid)->first();
		$hosting 		 = Capsule::table('tblhosting')->where('userid',$userid)->first();
		$life_time_value = Capsule::table('tblinvoices')->where('userid',$userid)->where('status', 'Paid')->sum('total');
		return [
			[
				'color' => '#36a64f',
				'fields' => [
					[
						'title' => 'Name',
						'value' => ($client_info->firstname) ?? '' . " " . ($client_info->lastname) ?? '',
						'short' => true,
					],
					[
						'title' => 'Email',
						'value' => ($client_info->email) ?? '',
						'short' => true,
					],
					[
						'title' => 'Domain',
						'value' => ($hosting->domain) ?? '',
						'short' => true,
					],
					[
						'title' => 'Date Started',
						'value' => (date('m-d-Y', strtotime($hosting->regdate))) ?? '',
						'short' => true,
					],
					[
						'title' => 'Lifetime Value',
						'value' => ($life_time_value) ?? 0.00,
						'short' => true,
					],
				],
			],
		];
	}

}
