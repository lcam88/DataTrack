<?php
namespace DataTrack\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Transactions Model
 *
 * @property \CakeDC\Users\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \DataTrack\Model\Entity\Transaction get($primaryKey, $options = [])
 * @method \DataTrack\Model\Entity\Transaction newEntity($data = null, array $options = [])
 * @method \DataTrack\Model\Entity\Transaction[] newEntities(array $data, array $options = [])
 * @method \DataTrack\Model\Entity\Transaction|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \DataTrack\Model\Entity\Transaction|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \DataTrack\Model\Entity\Transaction patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \DataTrack\Model\Entity\Transaction[] patchEntities($entities, array $data, array $options = [])
 * @method \DataTrack\Model\Entity\Transaction findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TransactionsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('transactions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('reftbl')
            ->maxLength('reftbl', 64)
            ->requirePresence('reftbl', 'create')
            ->notEmpty('reftbl');

        $validator
            ->scalar('refid')
            ->maxLength('refid', 255)
            ->requirePresence('refid', 'create')
            ->notEmpty('refid');

        $validator
            ->scalar('op')
            ->maxLength('op', 32)
            ->allowEmpty('op');

        $validator
            ->allowEmpty('dataset');

        $validator
            ->integer('flag')
            ->allowEmpty('flag');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
