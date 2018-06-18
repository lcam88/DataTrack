<?php
namespace DataTrack\Model\Entity;

use Cake\ORM\Entity;

/**
 * Transaction Entity
 *
 * @property int $id
 * @property string $reftbl
 * @property string $refid
 * @property string $op
 * @property string|resource $dataset
 * @property string $user_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $flag
 *
 * @property \CakeDC\Users\Model\Entity\User $user
 */
class Transaction extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'reftbl' => true,
        'refid' => true,
        'op' => true,
        'dataset' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'flag' => true,
        'user' => true
    ];
}
