<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use \lithium\core\Libraries;



$libraries = array('Doctrine\Common', 'Doctrine\DBAL', 'Doctrine\ORM', 'Symfony');

foreach($libraries as $name) {
	if (!Libraries::get($name)) {
		Libraries::add($name, array('bootstrap' => false));
	}
}


var_dump(Libraries::get('Doctrine\ORM'));
var_dump(class_exists('Doctrine\ORM\Configuration'));
die();
?>