<?php

declare( strict_types=1 );

namespace AgentFire\Test_Task\Integration;

use AgentFire\Test_Task\Traits\Singleton;
use Curl\Curl;
use Exception;
use WHMCS\Database\Capsule;

/**
 * @package AgentFire\Test_Task
 */
class Slack {

	use Singleton;

	/**
	 * @param string $channel
	 * @param string $username
	 * @param string $title
	 * @param string $icon
	 * @param array $attachments
	 * @throws Exception
	 */
	function send($channel, $username, $title, $icon, $attachments) {

		$webhook_URL = Capsule::table('tbladdonmodules')->where('module', 'agentfire_test_task')->where('setting','slack_webhook')->value('value');

		$data = [
			'channel'     => $channel,
			'username'    => $username,
			'text'        => $title,
			'icon_emoji'  => $icon,
			'attachments' => $attachments,
		];

		$data_string = json_encode($data);

		$request = new Curl();
		$request->setHeader('Content-type', 'application/json');
		$request->setHeader('Content-Length', strlen($data_string));

		$request->post($webhook_URL, $data_string);
		if ($request->error) {
			logActivity($request->errorMessage, 0);
		} else {
			logActivity($request->response, 0);
		}
	}

}
