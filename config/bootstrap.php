<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use lithium\core\Libraries;
use lithium\core\ConfigException;

$libraries = array('Doctrine\Common', 'Doctrine\DBAL', 'Doctrine\ORM', 'Doctrine\Migrations', 'Symfony');

foreach($libraries as $name) {
	if (!Libraries::get($name)) {
		try {
			Libraries::add($name, array('bootstrap' => false));
		} catch (ConfigException $e) {
			continue;
		}
	}
}

?>
