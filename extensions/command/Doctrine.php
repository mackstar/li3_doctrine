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

	/**
	 * When installing Doctrine from a remote repository, this is the version that will be checked
	 * out. This must be a valid SVN tag in the Doctrine repository.
	 *
	 * @var string
	 */
	public $installVersion = '2.0.0-BETA1';

	/**
	 * Indicates the SVN command to use when installing Doctrine. Can be set to 'checkout' or
	 * 'export'.
	 *
	 * @var string
	 */
	public $installCmd = 'checkout';

	/**
	 * The libraries/ directory into which Doctrine should be installed. This should be either your
	 * application's local libraries directory, or the system-wide libraries directory.
	 *
	 * @var string
	 */
	public $installPath;

	/**
	 * The path to the Doctrine repository from which the version tag will be checked out.
	 *
	 * @var string
	 */
	protected $_repositoryPath = 'http://svn.doctrine-project.org/tags';

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

	public function install() {
		$this->installPath = $this->installPath ?: LITHIUM_APP_PATH . '/libraries';
		$this->out('');
		$this->out("Preparing to install Doctrine...", 2);
		$this->out("Checking permissions on {$this->installPath}...");

		if (!is_writable($this->installPath)) {
			$message = "Could not write to libraries directory, please run this command with";
			$this->out("{$message} appropriate privileges.");
			return;
		}

		if (!is_dir("{$this->installPath}/_source")) {
			mkdir("{$this->installPath}/_source");
		}

		$this->out("Checking Subversion access...");
		exec("svn --version", $result);
		$pattern = '/^svn, version \d+\.\d+\./';

		if (!is_array($result) || count($result) < 2 || !preg_match($pattern, $result[0])) {
			$message = "Unable to access the 'svn' command. It should be installed and accessible";
			$this->out("{$message} from your system path.");
		}
		$message = "Creating svn {$this->installCmd} of Doctrine {$this->installVersion} in";
		$this->in("{$message} {$this->installPath}/_source, press Enter to continue:");

		$repository = "{$this->_repositoryPath}/{$this->installVersion}";
		$local = "{$this->installPath}/_source/Doctrine-{$this->installVersion}";
		$install = "{$this->installPath}/Doctrine";

		passthru("svn {$this->installCmd} {$repository} {$local}");
		$target = "{$local}/lib/Doctrine";

		if (!file_exists($install)) {
			if (!symlink($target, $install)) {
				$this->out("Symlink creation failed. Please link {$local} to {$install}");
			}
		} elseif (!is_link($install) || readlink($install) == $target) {
			$this->out("A bad symlink for Doctrine exists. Please point {$local} to {$install}.");
		}

		$message = "Installation complete. You may now add Doctrine database connections to your";
		$this->out("{$message} application.", 2);

		$connections = LITHIUM_APP_PATH . '/config/connections.php';

		if (!in_array(str_replace('/', DIRECTORY_SEPARATOR, $connections), get_included_files())) {
			$message = "NOTE: config/bootstrap/connections.php is currently not being loaded in ";
			$message .= "your application. Uncomment the corresponding require statement in";
			$this->out("{$message} config/bootstrap.php.", 2);
		}
	}
}

?>