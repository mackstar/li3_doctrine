<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of Rad, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\commands;

use \lithium\data\Connections;
use \lithium\util\Inflector;
use \Doctrine\ORM\Tools\Cli;
use \ReflectionProperty;

/**
 * The Doctrine command handles transparently setting up the models directory
 * and other configuration values automatically for you based on your
 * application paths.
 *
 * This command will allow you to choose a connection source to use for the
 * Doctrine command you are about to issue. You can choose a connection by
 * passing the `--connection=<name>` option likeso: {{{
 * li3 doctrine <doctrine_command ...> --connection=test
 * }}}
 * By default, the `default` connection will be used.
 *
 * To supply a custom config to the Doctrine Cli, you can do it like this: {{{
 * li3 doctrine <doctrine_command ...> --config=/path/to/my/config.php
 * }}}
 * Note that `--option` arguments must be placed at the end of the arguments
 * list to work correctly.
 *
 * Pretty much every argument passed is going to be handed off to Doctrine for
 * internal processing. This command merely acts as a convience proxy between
 * your Lithium application and your Doctrine models.
 */
class Doctrine extends \lithium\console\Command {

	/**
	 * Path to Doctrine cli config file
	 *
	 * @var string
	 */
	public $config;

	/**
	 * Connection name
	 *
	 * @var string
	 */
	public $connection;

	/**
	 * Connection source
	 *
	 * @var object
	 */
	protected $_source;

	/**
	 * Set up Doctrine Cli and filter arguments through it. Preconfigure
	 * connection, cli config and models path.
	 */
	public function run() {
		$cli = new Cli();
		$args = func_get_args();
		if (empty($args)) {
			$this->header('Doctrine Commands');
			$tasks = new ReflectionProperty('\Doctrine\ORM\Tools\Cli', '_tasks');
			$tasks->setAccessible(true);
			foreach ($tasks->getValue($cli) as $task => $class) {
				$this->out(' - ' . Inflector::underscore($task));
			}
			return;
		}

		require_once LITHIUM_APP_PATH . '/config/connections.php';

		$this->connection = $this->connection ?: 'default';
		$this->_source = Connections::get($this->connection);
		if (!$this->_source || !method_exists($this->_source, 'entityManager')) {
			$this->header('Error');
			$this->out('Connection "' . $this->connection . '" is not a valid Doctrine connection.');
			$this->out('Please check config/connections.php and make sure the connection is valid.');
			return;
		}

		// Used to set the EntityManager in li3_doctrine/config/doctrine.php
		define('LI3_DOCTRINE_CLI_CONNECTION', $this->connection);

		// Normalize command name for Doctrine
		$args[0] = str_replace('_', '-', Inflector::underscore($args[0]));

		// Add default config path if none specified
		if (!$this->config) {
			$this->config = dirname(dirname(dirname(__FILE__))) . '/config/doctrine.php';
		}

		// Delegate any unknown properties to Doctrine, preformatted
		$skip = array('request', 'response');
		foreach ($this as $property => $value) {
			if (in_array($property, $skip) || $property[0] === '_') {
				continue;
			}
			$property = str_replace('_', '-', Inflector::underscore($property));
			$args[] = '--' . $property . '=' . $value;
		}

		$args = array_merge(array(__FILE__), $args);
		$cli->run($args);
	}

}

?>