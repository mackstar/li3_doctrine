<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use \lithium\core\Libraries;

$doctrine = Libraries::get('Doctrine');
if (empty($doctrine)) {
	if (!defined('DOCTRINE_PATH')) {
		define('DOCTRINE_PATH', LITHIUM_LIBRARY_PATH . '/doctrine/lib/Doctrine');
	}
	Libraries::add('Doctrine', array('path' => DOCTRINE_PATH));
}

?>
