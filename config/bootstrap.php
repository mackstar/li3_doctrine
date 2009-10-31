<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of Rad, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use \lithium\core\Libraries;

/**
 * Change 'path' to where your doctrine/lib is. Alternatively place 'doctrine'
 * into your Lithium libraries path and it should automatically work.
 */
Libraries::add('doctrine', array(
	'path' => LITHIUM_LIBRARY_PATH . '/doctrine/lib',
	'loader' => array('Doctrine', 'autoload'),
	'prefix' => 'Doctrine',
	'bootstrap' => 'Doctrine.php'
));

?>