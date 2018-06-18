<?php
/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 * 
 */

namespace DataTrack\Model\Behavior;

use ArrayObject;
use Cake\Error\Debugger;
use Cake\ORM\Behavior;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventDispatcherTrait;
use Cake\Utility\Text;
use Cake\Controller\Component\AuthComponent;

/*
 * Class DataTrackBehavior
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class DataTrackBehavior extends Behavior {
/**
 * {@inheritDoc}
 */
	public function initialize(array $config) {
		parent::initialize($config);

		 // $this->loadHelper('CakeDC/Users.User');
		// Debugger::dump($this,5,'debug');
	}
/**
 * Before save listener.  This method creates tracks that records the new state
 * of the data.  Since tracks are accumulated, this provides a history of
 * changes.  The functionality can be disabled by setting the 'datatrack'
 * option to false.  Future may permit rolling back a record to some moment in
 * the past.
 *
 * @param \Cake\Event\Event $event The beforeSave event that was fired
 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
 * @param \ArrayObject $options the options passed to the save method
 * @return void
 */
	public function afterSave(Event $ev, EntityInterface $entity, ArrayObject $options) {
		if ((strcmp($this->_table->getTable(), 'transactions') != 0) && ((! isset($options)) || (! isset($options['datatrack'])) || ($options['datatrack'] == true))) {
			$transaction = TableRegistry::get('DataTrack.Transactions');
			$trans = $transaction->newEntity();
			$trans->tblref = $this->_table->getTable();
			$trans->tblid = $this->getTableID($entity);
			$trans->op = ($entity->isNew()) ? 'insert' : 'update';
			$trans->dataset = $this->getDataset($entity);
			$trans->user_id = $options['loggedInUser']['id'];
			Debugger::dump($trans,5);
			$transaction->save($trans);
		}
	}
/**
 * Before Find listener for tables that support the soft delete method, we
 * filter the deleted records from the search.  To retrieve deleted items as
 * well, the $options['recovery'] value should be set to 'true'.
 *
 * @param \Cake\Event\Event $event The beforeSave event that was fired
 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
 * @param \ArrayObject $options the options passed to the save method
 * @return void
 */
	public function beforeFind(Event $ev, Query $q, ArrayObject $options, $primary) {

		if ($primary && ((! isset($options)) || (! isset($options['recovery'])) || ($options['recovery'] != true)) && $this->_table->hasField('flag')) {
			$q->where(['flag & 0x1 = ' => 1]); // respect the softDelete modifications.
		}
		// Debugger::dump($q, 4);
	}
/**
 * Before delete listener.  For tables that support the soft delete method, we
 * perform the soft delete.  Soft deletes work independently to datatracks,
 * however by setting the 'datatrack' option to false, a traditional delete
 * will occure and tracks will not be registered.
 *
 * @param \Cake\Event\Event $event The beforeSave event that was fired
 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
 * @param \ArrayObject $options the options passed to the save method
 * @return void
 */
	public function beforeDelete(Event $ev, EntityInterface $entity, ArrayObject $options) {
		if (! (isset($options) && isset($options['datatrack']) && ($options['datatrack'] == false))) {
			if ($this->softDelete($entity)) {
				$ev->stopPropagation();
			}
		}
		return true; // apply soft delete if it registers.
	}

/**
 * get the entity dataset and returns a base64_encoded serialized associative array.
 *
 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
 * @return string
 */
	protected function getDataset(EntityInterface $entity) {
		$cols= $this->_table->getSchema()->columns();
		$dataset = array();
		foreach($cols as $col) {
			$dataset[$col] = $entity->get($col);
		}
		return base64_encode(serialize($dataset));
	}

/**
 * Retrieves a keyref for an entity and returns an array imploded on the colon ":" that is base64_encoded
 *
 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
 * @return string
 */
	public function getTableID(EntityInterface $entity) {
		$keys = (array)$this->_table->getPrimaryKey();
		$vals = array();
		foreach($keys as $k) {
			$vals[] = $entity->get($k);
		}
		return base64_encode(implode($vals, ":"));
	}

/**
 * soft delete a record by zeroing the 0x1 bit on the flag attribute.
 *
 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
 * @param \string | int $id The id of the tuple being deleted.
 * @return boolean
 */
	public function softDelete(EntityInterface $entity, $id = null) {
		if ($id) {
			$entity->id = $id;
		}
		if (! $entity->id) {
			return false;
		}
		$deleteCol = 'flag';
		if (! $this->_table->hasField($deleteCol)) {
			return false;
		}
	 	if (!empty($entity->whitelist)) {
	   		$this->whitelist[] = $deleteCol;
		}

		$colval = $entity->get($deleteCol) & ~0x1;
		$entity->set($deleteCol, $colval);

		$options = array(
			'atomic' =>  false, // we don't want to call the afterSave event.
			'associated' => false,
			'_primary' => false,
		);

		$success = $this->_table->save($entity, $options);

		if ($success) {
			$this->_table->dispatchEvent('Model.afterDelete', ['entity' => $entity, 'options' => $options]);
			$this->_table->dispatchEvent('Model.afterDeleteCommit', ['entity' => $entity, 'options' => $options]);
		}

		return $success;
	}

/**
 * an undelete method.  For records that support the soft delete method, we
 * permit undeleting by restoring the 0x1 bit to the flag attribute.
 *
 * @param \Cake\Event\Event $event The beforeSave event that was fired
 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
 * @param \ArrayObject $options the options passed to the save method
 * @return void
 */
	public function undelete(EntityInterface $entity, $id = null) {
		if ($id) {
			$this->id = $id;
		}
		if (! $this->id) {
			return false;
		}
		$deleteCol = 'flag';
		if (! $this->_table->hasField($deleteCol)) {
			return false;
		}

		$colval = $entity->get($deleteCol) | 0x1;
		$entity->set($deleteCol, $colval);

		$options = array( 
			'atomic' =>  false,
			'checkRules' => true,
			'_primary' => false,
		);

		$success = $this->_table->save($entity, $options);

		if ($success) {
			$this->_table->dispatchEvent('Model.afterSave', ['entity' => $entity, 'options' => $options]);
			$this->_table->dispatchEvent('Model.afterSaveCommit', ['entity' => $entity, 'options' => $options]);
		}

		return $success;
	}
}
