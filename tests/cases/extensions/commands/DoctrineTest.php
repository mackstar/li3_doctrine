<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of Rad, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\tests\cases\extensions\commands;

use \li3_doctrine\extensions\commands\Doctrine;

/**
 * Doctrine command line integration tests
 */
class DoctrineTest extends \lithium\test\Unit {

	/**
	 * Shared instance to a Doctrine command
	 *
	 * @var object
	 */
	protected $_shell;

	/**
	 * Bootstrap Doctrine
	 */
	public function setUp() {
		$plugin = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
		$bootstrap = $plugin . '/config/bootstrap.php';
		if (is_readable($bootstrap)) {
			include $bootstrap;
		}
		$this->_shell = new Doctrine();
	}

	/**
	 * Tests argument passing/delegation
	 */
	public function testArguments() {

	}

}

?>