<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use \lithium\core\Libraries;

if (!defined('DOCTRINE_LIB_PATH')) {
	define('DOCTRINE_LIB_PATH', LITHIUM_LIBRARY_PATH . '/doctrine/lib');
}

$libraries = array(
	'Doctrine_Common' => array(
		'path' => defined('DOCTRINE_LIB_COMMON_PATH') ? DOCTRINE_LIB_COMMON_PATH : DOCTRINE_LIB_PATH . '/vendor/doctrine-common/lib/Doctrine/Common',
		'prefix' => 'Doctrine\\Common\\'
	),
	'Doctrine_DBAL' => array(
		'path' => defined('DOCTRINE_LIB_DBAL_PATH') ? DOCTRINE_LIB_DBAL_PATH : DOCTRINE_LIB_PATH . '/vendor/doctrine-dbal/lib/Doctrine/DBAL',
		'prefix' => 'Doctrine\\DBAL\\'
	),
	'Doctrine_ORM' => array(
		'path' => defined('DOCTRINE_LIB_ORM_PATH') ? DOCTRINE_LIB_ORM_PATH : DOCTRINE_LIB_PATH . '/Doctrine/ORM',
		'prefix' => 'Doctrine\\ORM\\'
	),
	'Symfony' => array(
	'path' => defined('DOCTRINE_LIB_SYMFONY_PATH') ? DOCTRINE_LIB_SYMFONY_PATH : DOCTRINE_LIB_PATH . '/vendor/Symfony',
	'prefix' => 'Symfony\\'
	)
);

foreach($libraries as $name => $settings) {
	$library = Libraries::get($name);
	if (empty($library)) {
		Libraries::add($name, array('bootstrap'=>false) + $settings);
	}
}
?>