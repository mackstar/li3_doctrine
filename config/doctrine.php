<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of Rad, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use \lithium\data\Connections;

/**
 * Entity manager
 */
$em = Connections::get(LI3_DOCTRINE_CLI_CONNECTION)->entityManager();

/**
 * Arguments
 */
$args = array('class-dir' => LITHIUM_APP_PATH . '/models');

?>