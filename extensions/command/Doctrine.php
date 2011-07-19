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
use \Doctrine\Common\ClassLoader;
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
	public $installVersion = '2.0.6';

	/**
	 * Indicates the SVN command to use when installing Doctrine. Can be set to 'checkout' or
	 * 'export'.
	 *
	 * @var string
	 */
	public $installCmd = 'clone';

	/**
	 * The path to the Doctrine repository from which the version tag will be checked out.
	 *
	 * @var string
	 */
	protected $_repositoryPath = 'git://github.com/doctrine/doctrine2.git';

	/**
	 * The path to the Migrations repository from which the version tag will be checked out.
	 *
	 * @var string
	 */
	protected $_migrationsRepositoryPath = 'git://github.com/doctrine/migrations.git';

	public function run($args = array()) {

		$defaults = array(
			'proxy' => array(
				'auto' => true,
				'path' => LITHIUM_APP_PATH . '/resources/tmp/cache/Doctrine/Proxies',
				'namespace' => 'Doctrine\Proxies'
			),
			'useModelDriver' => true,
			'mapping' => array('class' => null, 'path' => LITHIUM_APP_PATH . '/models'),
			'configuration' => null,
			'eventManager' => null,
		);

		if ($this->request->params['action'] != 'run') {
			$args = $this->request->argv;
		}

		// Check if we need to add the migration configuration file
		$migrationCommand = false;
		$migrationConfig = true;
		$migration = false;
		
		$conn = Connections::get($this->connection);
		$conn->_config = $conn->_config + $defaults;
		
		$i=0;
		foreach ($args as &$arg) {
			if (strstr($arg, 'migrations:')) {
				$migrationCommand = true;
			}
			if (strstr($arg, 'migrations')) {
				$migration = true;
			}
			if (strstr($arg, '--configuration=')) {
				$migrationConfig = false;
			}
			if (strstr($arg, '--connection')) {
				unset($args[$i]);
			}
			$i++;
		}
		
		if ($migrationCommand && $migrationConfig) {
			$args[]='--configuration='.dirname(dirname(__DIR__)).'/config/migrations.yml';
		}

		$input =  new \Symfony\Component\Console\Input\ArgvInput($args);

		
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
		$config->setProxyDir($conn->_config['proxy']['path']);
		$config->setProxyNamespace($conn->_config['proxy']['namespace']);
		
		//EntityManager
		$em = \Doctrine\ORM\EntityManager::create($conn->_config, $config);
		$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
			'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
			'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em),
			'dialog' => new \Symfony\Component\Console\Helper\DialogHelper()
		));
		
		//CLI
		$cli = new \Symfony\Component\Console\Application(
			'Doctrine Command Line Interface', \Doctrine\Common\Version::VERSION
		);
		$cli->setCatchExceptions(true);
		$cli->register('doctrine');
		$cli->setHelperSet($helperSet);
		
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

		// If command called is a doctrine migration command
		if ($migration) {
			$cli->addCommands(array(
			// Migration Commands
				new \Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand(),
				new \Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
				new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
				new \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
				new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
				new \Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand()
			));
		}
		$cli->run($input);
	}

	public function install() {
		
		$this->out('');
		$this->out("Preparing to install Doctrine...", 2);

		$this->checkGit();
		$this->writeDirectory($this->_getInstallPath().'/_source');
		
		$message = "Creating git {$this->installCmd} of Doctrine in";
		$this->in("{$message} ".$this->_getInstallPath()."/_source, press Enter to continue:");

		$repository = "{$this->_repositoryPath}";
		$local = "{$this->_getInstallPath()}/_source/Doctrine2";
		$install = $this->getDoctrineBase();

		passthru("git {$this->installCmd} {$repository} {$local}");
		$current = getcwd();
		chdir($local);
		passthru("git checkout {$this->installVersion}");
		passthru("git submodule update --init");
		$target = "{$local}/lib/Doctrine";
		chdir($current);

		$this->writeDirectory($install);

		$symLinks = array(
			array(
				'install' => "{$install}/Common",
				'target'=>"{$local}/lib/vendor/doctrine-common/lib/Doctrine/Common"
			),
			array(
				'install' => "{$install}/ORM",
				'target'=>"{$local}/lib/Doctrine/ORM"
			),
			array(
				'install' => "{$install}/DBAL",
				'target'=>"{$local}/lib/vendor/doctrine-dbal/lib/Doctrine/DBAL"
			),
			array(
				'install' => "{$this->_getInstallPath()}/Symfony",
				'target'=>"{$local}/lib/vendor/Symfony"
			),
		);

		foreach ($symLinks as $symLink) {
			$this->createSymLink($symLink['install'], $symLink['target']);
		}

		$message = "Installation complete. You may now add Doctrine database connections to your";
		$this->out("{$message} application.", 2);

		$connections = LITHIUM_APP_PATH . '/config/bootstrap/connections.php';

		if (!in_array(str_replace('/', DIRECTORY_SEPARATOR, $connections), get_included_files())) {
			$message = "NOTE: config/bootstrap/connections.php is currently not being loaded in ";
			$message .= "your application. Uncomment the corresponding require statement in";
			$this->out("{$message} config/bootstrap.php.", 2);
		}
	}

	public function migrationinstall(){
		$this->checkGit();
		if(!is_dir($this->_getInstallPath().'/_source')){
			$this->out('Please install Doctrine before installing Doctrine Migrations');
			$this->out('From your app route type: "li3 doctrine install"');
			return;
		}
		$this->writeDirectory(LITHIUM_APP_PATH.'/migrations');
		
		$message = "Creating git {$this->installCmd} of Doctrine Migrations in";
		$this->in("{$message} ".$this->_getInstallPath()."/_source, press Enter to continue:");
		
		$local = "{$this->_getInstallPath()}/_source/Migrations";

		passthru("git {$this->installCmd} {$this->_migrationsRepositoryPath} {$local}");

		$this->createSymLink("{$this->getDoctrineBase()}/DBAL/Migrations", "{$local}/lib/Doctrine/DBAL/Migrations");
		$message = "\n\nInstallation complete. You may now use Doctrine Migrations through your command ";
		$message .= "line. \nType li3 doctrine list migrations for a full list of options. \nType li3 ";
		$message .= "doctrine migrations:generate to create your first migration. \nThe migration file ";
		$message .= "will be created in yourapp/migrations. \nAll migration commands are avaible as in ";
		$message .= "http://www.doctrine-project.org/projects/migrations/2.0/docs/en \nbut with ./doctrine";
		$message .= "command being replaced by 'li3 doctrine' ";
		$this->out($message);
	}

	protected function createSymLink($install, $target){
		if (!file_exists($install)) {
			if (!symlink($target, $install)) {
				$this->out("Symlink creation failed. Please link {$target} to {$install}");
			}
		} elseif (!is_link($install) || readlink($install) != $target) {
			$this->out("A bad symlink exists. Please point {$target} to {$install}.");
		}
	}

	protected function checkWritable($directory) {
		$this->out("Checking permissions on {$directory}...");
		$info = pathinfo($directory);
		if(!is_writable($directory)){
			$message = "Could not write to {$info['basename']} directory ({$directory}), ";
			$this->out("{$message} please run this command with appropriate privileges.");
			return;
		}
	}

	protected function writeDirectory($directory) {
		if (!is_dir($directory)) {
			$info = pathinfo($directory);
			$this->checkWritable($info['dirname']);
			mkdir($directory);
		}
	}

	protected function getDoctrineBase() {
		return "{$this->_getInstallPath()}/Doctrine";
	}

	protected function checkGit() {
		$this->out("Checking git installation");
		exec("git --version", $result);
		$pattern = '/^git version \d+\.\d+\./';

		if (!is_array($result) || count($result) < 1 || !preg_match($pattern, $result[0])) {
			$message = "Unable to access the 'git' command. It should be installed and accessible";
			$this->out("{$message} from your system path.");
			return;
		}
	}
	
	
	protected function _getInstallPath() {
		return dirname(dirname(dirname(__DIR__)));
	}
}

?>