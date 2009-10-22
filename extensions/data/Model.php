<?php

namespace li3_doctrine\extensions\data;

class Model extends \lithium\data\Model {
	
	protected $_classes = array(
		'query' => '\li3_doctrine\extensions\data\model\Query',
		'record' => '\li3_doctrine\extensions\data\model\Record',
		'recordSet' => '\li3_doctrine\extensions\data\model\RecordSet'
	);
	
}

?>