<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\doctrine\logger;

use \lithium\analysis\Logger;

class SqlLogger extends \lithium\core\Object {
	public function logSQL($sql, array $params = null) {
		Logger::write('default', $sql);
	}
}

?>