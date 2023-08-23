<?php

declare( strict_types=1 );

namespace AgentFire\Test_Task;

use Twig\{Environment, Loader};
use AgentFire\Test_Task\Traits\Singleton;

/**
 * @package AgentFire\Test_Task
 *
 * Usage example: Template::get_instance()->display( 'main.twig' );
 */
class Template {
	use Singleton;

	/**
	 * @var Environment
	 */
	private $twig;

	/**
	 * Template constructor.
	 */
	public function __construct() {
		$this->twig = new Environment(
			new Loader\FilesystemLoader(AGENTFIRE_TEST_TASK_PATH . 'template/test_task')
		);
	}

	/**
	 * @param string $template
	 * @param array $atts
	 *
	 * @return string
	 * @throws Exception\Template
	 */
	public function render(string $template, array $atts = []): string {
		try {
			$result = $this->twig->render($template, $atts);
		} catch (\Exception $e) {
			throw new Exception\Template($e->getMessage(), $e->getCode());
		}

		return $result;
	}

	/**
	 * @param string $template
	 * @param array $atts
	 *
	 * @throws Exception\Template
	 */
	public function display(string $template, array $atts = []) {
		echo $this->render($template, $atts);
	}
}