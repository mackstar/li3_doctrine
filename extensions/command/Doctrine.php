<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\command;

use \lithium\data\Connections;
use \Doctrine\Common\Cli\Configuration;
use \Doctrine\Common\Cli\CliController;

/**
 * The `doctrine` command provides a direct interface between the Doctrine command-line and Lithium
 * app/plugin models. The default connection used is 'default'. To specify a different connection
 * pass the `--connection` option with the name of the connection you would like to use. Any
 * connection used with this tool *must* be a Doctrine connection.
 */
class Doctrine extends \lithium\console\Command {

	/**
	 * Specifies the name of the connection in the `Connections` class that contains your Doctrine
	 * configuration. Defaults to 'default'.
	 *
	 * @var string
	 */
	public $connection = 'default';

	/**
	 * A fully-namespaced class path to the Doctrine CLI printer that should be used for output.
	 * This setting usually does not need to be configured.
	 *
	 * @var string
	 */
	public $printer = '\Doctrine\Common\Cli\Printers\NormalPrinter';

	public function run($args = array()) {
		$args = $this->_config['request']->args;
		$conn = Connections::get($this->connection);

		if (!$conn || !$conn instanceof \li3_doctrine\extensions\data\source\Doctrine) {
			$error = "Error: Could not get Doctrine proxy object from Connections, using";
			$error .= " configuration '{$this->connection}'. Please add the connection or choose";
			$error .= " an alternate configuration using the `--connection` flag.";
			$this->error($error);
			return;
		}

		$config = new Configuration();
		$config->setAttribute('em', $conn->getEntityManager());

		$printer = $this->printer;
		$printer = new $printer();
		$cli = new CliController($config, $printer);

		$cli->run($args);
	}
}

?>