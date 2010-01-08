<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\data\source;

use \li3_doctrine\extensions\doctrine\mapper\ModelDriver;
use \Doctrine\Common\EventManager;
use \Doctrine\Common\Cache\ArrayCache;
use \Doctrine\ORM\Configuration;
use \Doctrine\ORM\EntityManager;
use \Doctrine\ORM\Query;

/**
 *
 */
class Doctrine extends \lithium\data\source\Database {
	/**
	 * Entity manager.
	 */
	protected $_em;

	/**
	 * Schema manager
	 */
	protected $_sm;

	/**
	 *
	 */
	public function __construct($config = array()) {
		if (isset($config['configuration'])) {
			$configuration = $config['configuration'];
			unset($config['configuration']);
		} else {
			$configuration = new Configuration();
		}

		$configuration->setProxyDir(LITHIUM_APP_PATH . '/models');
		$configuration->setProxyNamespace('\app\models');
		$configuration->setAutoGenerateProxyClasses(true);

		if (isset($config['eventManager'])) {
			$eventManager = $config['eventManager'];
			unset($config['eventManager']);
		} else {
			$eventManager = new EventManager();
		}

		$configuration->setMetadataCacheImpl(new ArrayCache());
		$configuration->setMetadataDriverImpl(new ModelDriver());

		$this->_em = EntityManager::create($config, $configuration, $eventManager);
		$this->_sm = $this->_em->getConnection()->getSchemaManager();
		parent::__construct($config);
	}

	/**
	 * Connects to the database using the options provided to the class constructor.
	 *
	 * @return boolean True if the database could be connected, else false.
	 */
	public function connect() {
		if (!$this->isConnected()) {
			return $this->getEntityManager()->getConnection()->connect();
		}
		return false;
	}


	/**
	 * Disconnects the adapter from the database.
	 *
	 * @return boolean True on success, else false.
	 */
	public function disconnect() {
		if ($this->isConnected()) {
			$this->getEntityManager()->getConnection()->close();
			return true;
		}
		return false;
	}

	/**
	 *
	 */
	public function isConnected($options = array()) {
		$defaults = array('autoConnect' => false);
		$options += $defaults;
		$connected = $this->getEntityManager()->getConnection()->isConnected();

		if (!$connected && $options['autoConnect']) {
			$this->connect();
			return $this->getEntityManager()->getConnection()->isConnected();
		}
		return $connected;
	}

	/**
	 *
	 */
	public function getEntityManager() {
		return $this->_em;
	}

	/**
	 *
	 */
	public function getSchemaManager() {
		return $this->_sm;
	}

	/**
	 * Returns the list of tables in the currently-connected database.
	 *
	 * @param string $model The fully-name-spaced class name of the model object making the request.
	 * @return array Returns an array of objects to which models can connect.
	 * @filter This method can be filtered.
	 */
	public function entities($class = null) {
		$tables = $this->getSchemaManager()->listTables();
		return $tables;
	}

	public function encoding($encoding = null) {
	}

	public function result($type, $resource, $context) {
		if ($type == 'next') {
			foreach($resource as $i => $object) {
				$context->offsetSet($i, $object);
			}
		}
	}

	public function error() {
	}


	/**
	 *
	 */
	public function describe($entity, $meta = array()) {
		$schema = array();
		$columns = $this->getSchemaManager()->listTableColumns($entity);
		foreach($columns as $field => $column) {
			$column['type'] = strtolower((string) $column['type']);
			$schema[$field] = $column;
		}

		return $schema;
	}

	/**
	 *
	 */
	public function create($query, $options) {
	}

	/**
	 *
	 * @return RecordSet
	 */
	public function read($query, $options) {
		$query = $query->export($this);
		$doctrineQuery = $this->_filter(__METHOD__, compact('query', 'options'), function($self, $params, $chain) {
			extract($params);
			$doctrineQuery = $self->getEntityManager()->createQueryBuilder();
			$doctrineQuery->from($options['model'], $options['model']::meta('name'));

			foreach($query['fields'] as $scope => $fields) {
				foreach($fields as $field) {
					$doctrineQuery->addSelect($scope::meta('name') . '.' . $field);
				}
			}

			if (isset($query['conditions'])) {
				$doctrineQuery->add('where', $query['conditions']);
			}
			return $doctrineQuery;
		});

		$query = $doctrineQuery->getQuery();
		$query->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true);
		return $query->getResult();
	}

	/**
	 *
	 */
	public function update($query, $options) {
	}

	/**
	 *
	 */
	public function delete($query, $options) {
	}

	/**
	 *
	 */
	public function conditions($conditions, $query) {
		$model = $query->model();
		return $this->_parseConditions($query->conditions(), array('alias'=>$model::meta('name')));
	}

	/**
	 *
	 */
	public function fields($fields, $query) {
		return $this->columns($query);
	}

	/**
	 *
	 */
	public function order($order, $query) {
		return $order ?: array();
	}

	/**
	 *
	 */
	public function limit($limit, $query) {
		return $limit ?: array();
	}

	protected function _parseConditions($conditions, $options) {
		$query = $this->getEntityManager()->createQueryBuilder();
		if (empty($conditions)) {
			return null;
		} else if (is_string($conditions)) {
			$query->$clause($conditions);
		} else {
			$expr = $query->expr();
			foreach($conditions as $key => $value) {
				if (is_string($key) && in_array(strtolower($key), array('or'))) {
					$clause = strtolower($key);
					$innerQuery = $this->getEntityManager()->createQueryBuilder();
					foreach((array) $value as $innerKey => $piece) {
						if (is_string($innerKey)) {
							$piece = array($innerKey => $piece);
						}
						$innerQuery->{$clause.'Where'}($this->_parseConditions($piece, $options));
					}
					$query->andWhere($innerQuery->getDqlPart('where'));
				} else if (is_string($key)) {
					if (strpos($key, '.') === false) {
						$key = $options['alias'] . '.' . $key;
					}
					if (is_array($value)) {
						foreach($value as $iv => $ivalue) {
							$value[$iv] = $expr->literal($ivalue);
						}
						$query->andWhere($expr->in($key, $value));
					} else {
						$query->andWhere($expr->eq($key, $expr->literal($value)));
					}
				} else {
					$query->andWhere($this->_parseConditions($value, $options));
				}
			}
		}

		return $query->getDqlPart('where');
	}
}

?>
