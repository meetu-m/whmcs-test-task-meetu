<?php

namespace AgentFire\Test_Task;

use AgentFire\Test_Task;

use WHMCS\Module\AbstractWidget;
use WHMCS\Database\Capsule;

use App;

class Widget extends AbstractWidget {
	/**
	 * @type string
	 */
	protected $title = 'Test Task Stats';
	/**
	 * @type string
	 */
	protected $description = 'An overview of Test Task Stats.';
	/**
	 * @type int
	 */
	protected $weight = 1;
	/**
	 * @type int
	 */
	protected $columns = 1;
	/**
	 * @type bool
	 */
	protected $cache = true;
	/**
	 * @type int
	 */
	protected $cacheExpiry = 3600;
	/**
	 * @type bool
	 */
	protected $cachePerUser = false;
	/**
	 * @type string
	 */
	protected $requiredPermission = '';

	public function getId() {
        return str_replace("\\", "", get_class($this));
    }

	public function getData() {

		$tasks_queued = Capsule::table('mod_agentfire_test_task_cron')->whereIn('status', ['pending','running'])->count();
		$tasks_executed = Capsule::table('mod_agentfire_test_task_cron')->where('status', 'completed')->count();
		$tasks_failed = Capsule::table('mod_agentfire_test_task_cron')->where('status', 'failed')->count();

		return [
			'tasks_queued'   => $tasks_queued,
			'tasks_executed' => $tasks_executed,
			'tasks_failed' => $tasks_failed,
		];
	}

	public function generateOutput($options) {
		$data = $this->getData();
		ob_start();

		?>
		<div class="icon-stats">
			<div class="row">
				<div class="col-sm-6">
					<div class="item">
						<div class="icon-holder text-center">
							<i class="pe-7s-clock"></i>
						</div>
						<div class="data">
							<div class="note">
								Tasks Queued
							</div>
							<div class="number">
								<?= $data['tasks_queued'] ?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="item">
						<div class="icon-holder text-center">
							<i class="pe-7s-check"></i>
						</div>
						<div class="data">
							<div class="note">
								Tasks Executed
							</div>
							<div class="number">
								<?= $data['tasks_executed'] ?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="item">
						<div class="icon-holder text-center">
							<i class="pe-7s-close-circle"></i>
						</div>
						<div class="data">
							<div class="note">
								Tasks Failed
							</div>
							<div class="number">
								<?= $data['tasks_failed'] ?>
							</div>
						</div>
					</div>
				</div>
    		</div>
        </div>
		<?php

		return ob_get_clean();
	}
}
