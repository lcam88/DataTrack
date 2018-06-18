<?php
/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 * 
 */

namespace DataTrack\Event;

use ArrayObject;
use Cake\Controller\Component\AuthComponent;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;

/**
 * Class UserListener
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 *
 * Based on ceeram/blame LoggedInUserListener
 */

class UserListener implements EventListenerInterface {

/**
 * @var AuthComponent
 */
	protected $_Auth;
/**
 * Constructor
 *
 * @param \Cake\Controller\Component\AuthComponent $Auth Authcomponent
 */
	public function __construct(AuthComponent $Auth) {
		$this->_Auth = $Auth;
	}

/**
 * {@inheritDoc}
 */
	public function implementedEvents() {
		return [
			'Model.afterSave' => [
				'callable' => 'afterSave',
				'priority' => -100
			],
		];
	}

/**
 * Before save listener.
 *
 * @param \Cake\Event\Event $event The beforeSave event that was fired
 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
 * @param \ArrayObject $options the options passed to the save method
 * @return void
 */
	public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options) {
		if (empty($options['loggedInUser'])) {
			$options['loggedInUser'] = $this->_Auth->user();
		}
	}
}
