<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use \lithium\core\Libraries;



$libraries = array('Doctrine\Common', 'Doctrine\DBAL', 'Doctrine\ORM', 'Symfony');


// Check directory exists so not to throw errors on this installer
if(is_dir(LITHIUM_APP_PATH.'/libraries/Doctrine')||is_dir(LITHIUM_LIBRARY_PATH.'/Doctrine')){

  foreach($libraries as $name) {
  	if (!Libraries::get($name)) {
  		Libraries::add($name, array('bootstrap' => false));
  	}
  }

}


?>