<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\tests\mocks\analysis;

class MockLoggerAdapter extends \lithium\core\Object {
	public static $lines = array();
	public function write($name, $value) {
		return function($self, $params, $chain) {
			$class = __CLASS__;
			$class::$lines[] = $params;
		};
	}
}

?>
