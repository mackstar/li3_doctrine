<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 *
 */

namespace li3_doctrine\extensions\command;

use \lithium\data\Connections;
use \Doctrine\ORM\Configuration;
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

	public function run($args = array()) {
		$args = $this->_config['request']->args;
		$input =  new \Symfony\Component\Console\Input\ArgvInput($args);
		$conn = Connections::get($this->connection);
		
		if (!$conn || !$conn instanceof \li3_doctrine\extensions\data\source\Doctrine) {
			$error = "Error: Could not get Doctrine proxy object from Connections, using";
			$error .= " configuration '{$this->connection}'. Please add the connection or choose";
			$error .= " an alternate configuration using the `--connection` flag.";
			$this->error($error);
			return;
		}

		/*
		 * New Doctrine ORM Configuration
		 * TODO: load multiple drivers [Annotations, YAML & XML]
		 * 
		 */
		$config = new \Doctrine\ORM\Configuration();
		$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);

		//Annotation Driver
		$driver = $config->newDefaultAnnotationDriver(array(LITHIUM_APP_PATH . '/models'));	
		$config->setMetadataDriverImpl($driver);
		
		//Proxy configuration
		$config->setProxyDir($conn->_config['proxyDir']);
		$config->setProxyNamespace($conn->_config['proxyNamespace']);
		
		//EntityManager
		$em = \Doctrine\ORM\EntityManager::create($conn->_config, $config);
		$helpers = array(
                'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
                'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
		);
		
		//CLI
		$cli = new \Symfony\Component\Console\Application('Doctrine Command Line Interface', \Doctrine\Common\Version::VERSION);
		$cli->setCatchExceptions(true);
		$cli->register('doctrine');
		$helperSet = $cli->getHelperSet();
		foreach ($helpers as $name => $helper) {
			$helperSet->set($helper, $name);
		}
		
		$cli->addCommands(array(
		// DBAL Commands
		new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
		new \Doctrine\DBAL\Tools\Console\Command\ImportCommand(),

		// ORM Commands
		new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
		new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
		new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
		new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
		new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
		new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand(),
		new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
		new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand(),
		new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
		new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand(),
		new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand(),
		new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand(),
		new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),
		new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand(),

		));
		$cli->run($input);
	}
}

?>