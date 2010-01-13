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

	public $connection = 'default';

	public $printer = '\Doctrine\Common\Cli\Printers\NormalPrinter';

	public function run($args = array()) {
		$args = $this->_config['request']->args;
		$conn = Connections::get($this->connection);

		$config = new Configuration();
		$config->setAttribute('em', $conn->getEntityManager());

		$printer = $this->printer;
		$printer = new $printer();
		$cli = new CliController($config, $printer);

		$cli->run($args);
	}
}

?>