<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use \lithium\core\Libraries;

Libraries::add('Doctrine', array(
	'path' => LITHIUM_LIBRARY_PATH . '/doctrine/lib/Doctrine'
));

Libraries::add('DoctrineExtensions', array(
	'path' => dirname(dirname(__FILE__)) . '/libraries/activeentity/DoctrineExtensions'
));

?>