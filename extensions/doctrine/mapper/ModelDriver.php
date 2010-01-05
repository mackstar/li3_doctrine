<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\doctrine\mapper;

use \Doctrine\ORM\Mapping\ClassMetadataInfo;

class ModelDriver implements \Doctrine\ORM\Mapping\Driver\Driver {

	public function loadMetadataForClass($className, ClassMetadataInfo $metadata) {
	}

	public function getAllClassNames() {
	}

	public function isTransient($class) {
	}
}

?>