<?php
/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 * 
 */

namespace DataTrack\Controller;

use DataTrack\Event\UserListener;

/**
 * Class DataTrackTrait
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
trait DataTrackTrait {
/**
 * {@inheritDoc}
 */
	public function loadModel($modelClass = null, $type = 'Table') {
		$model = parent::loadModel($modelClass, $type);
		$listener = new UserListener($this->Auth);
		$model->eventManager()->attach($listener);
		return $model;
	}
} 
