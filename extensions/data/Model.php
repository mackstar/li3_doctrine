<?php

namespace li3_doctrine\extensions\data;

class Model extends \lithium\data\Model {
	
	protected $_classes = array(
		'query' => '\li3_doctrine\extensions\data\model\Query',
		'recordSet' => '\li3_doctrine\extensions\data\model\RecordSet'
	);
	
}

?>