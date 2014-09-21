<?php
namespace App\Model\Table;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table {

/**
 * Initialize method.
 *
 * @param array $config The configuration for the Table.
 *
 * @return void
 */
	public function initialize(array $config) {
		$this->table('users');
		$this->displayField('username');
		$this->primaryKey('id');

		$this->addBehavior('Timestamp');
		$this->addBehavior('Upload',
			[
				'fields' => [
					'avatar' => [
						'path' => 'upload/avatar/:id/:md5',
						'overwrite' => true,
						'prefix' => '../',
						'defaultFile' => 'avatar.png'
					]
				]
			]
		);
		$this->addBehavior('Sluggable',
			[
				'field' => 'username'
			]
		);

		$this->hasMany('BlogArticles',
			[
				'foreignKey' => 'user_id',
			]
		);
		$this->hasMany('BlogArticlesComments',
			[
				'foreignKey' => 'user_id',
			]
		);
		$this->hasMany('BlogArticlesLikes',
			[
				'foreignKey' => 'user_id',
			]
		);
	}

/**
 * Create validation rules.
 *
 * @param \Cake\Validation\Validator $validator The Validator instance.
 *
 * @return \Cake\Validation\Validator
 */
	public function validationCreate(Validator $validator) {
		$validator
			->validatePresence('username', 'create')
			->notEmpty('username', __("You must set an username"))
			->add('username',
				[
					'unique' => [
						'rule' => 'validateUnique',
						'provider' => 'table',
						'message' => __("This username is already used.")
					],
					'alphanumeric' => [
						'rule' => ['custom', '#^[A-Za-z0-9]+$#'],
						'message' => __("Only alphanumeric characters.")
					],
					'lengthBetween' => [
						'rule' => ['lengthBetween', 4, 20],
						'message' => __("Your username must be between {0} and {1} characters.", 4, 20)
					]
				]
			)
			->validatePresence('password', 'create')
			->notEmpty('password', __("You must specify your password."))
			->validatePresence('password_confirm', 'create')
			->notEmpty('password_confirm', __("You must specify your password (confirmation)."))
			->add('password_confirm',
				[
					'lengthBetween' => [
						'rule' => ['lengthBetween', 8, 20],
						'message' => __("Your password (confirmation) must be between {0} and {1} characters.", 8, 20)
					],
					'equalToPassword' => [
						'rule' => function ($value, $context) {
								return (new DefaultPasswordHasher)->check($value, $context['data']['password']);
						},
						'message' => __("Your password confirm must match with your password")
					]
				]
			)
			->validatePresence('email', 'create')
			->notEmpty('email')
			->add('email',
				[
					'unique' => [
						'rule' => 'validateUnique',
						'provider' => 'table',
						'message' => __("This E-mail is already used.")
					],
					'email' => [
						'rule' => 'email',
						'message' => __("You must specify a valid E-mail address.")
					]
				]
			);

		return $validator;
	}

/**
 * Account validation rules.
 *
 * @param \Cake\Validation\Validator $validator The Validator instance.
 *
 * @return \Cake\Validation\Validator
 */
	public function validationAccount(Validator $validator) {
		return $validator
			->provider('upload', 'App\Model\Validation\UploadValidator')
			->provider('purifier', 'App\Model\Validation\PurifierValidator')
			->allowEmpty('first_name')
			->add('first_name', 'maxLength',
				[
					'rule' => ['maxLength', 100],
					'message' => __("Your First Name can not contain more than {0} characters.", 100)
				]
			)
			->allowEmpty('last_name')
			->add('last_name', 'maxLength',
				[
					'rule' => ['maxLength', 100],
					'message' => __("Your Last Name can not contain more than {0} characters.", 100)
				]
			)
			->allowEmpty('avatar_file')
			->add('avatar_file',
				[
					'mimeType' => [
						'rule' => ['mimeType', ['image/jpeg', 'image/png']],
						'message' => __("The mineType is not allowed."),
						'on' => function ($context) {
								return !empty($context['data']['avatar_file']['name']);
						}
					],
					'fileExtension' => [
						'rule' => ['extension', ['jpg', 'jpeg', 'png']],
						'message' => __("The extension allowed are {0}.", '.jpg, .jeg and .png'),
						'on' => function ($context) {
								return !empty($context['data']['avatar_file']['name']);
						}
					],
					'fileSize' => [
						'rule' => ['fileSize', '<', '500KB'],
						'message' => __("The file exceeded the max allowed size of {0}", '500KB'),
						'on' => function ($context) {
								return !empty($context['data']['avatar_file']['name']);
						}
					],
					'maxDimension' => [
						'rule' => ['maxDimension', 230, 230],
						'provider' => 'upload',
						'message' => __(
							"The file exceeded the max allowed dimension. Max height : {0} Max width : {1}", 230, 230
						),
					]
				]
			)
			->allowEmpty('facebook')
			->add('facebook', 'maxLength',
				[
					'rule' => ['maxLength', 200],
					'message' => __("Your Facebook can not contain more than {0} characters.", 200)
				]
			)
			->allowEmpty('twitter')
			->add('twitter', 'maxLength',
				[
					'rule' => ['maxLength', 200],
					'message' => __("Your Twitter can not contain more than {0} characters.", 200)
				]
			)
			->allowEmpty('biography')
			->add('biography',
				[
					'maxLength' => [
						'rule' => ['purifierMaxLength', 3000],
						'provider' => 'purifier',
						'message' => __('Your biography can not contain more than {0} characters.', 3000)
					]
				]
			)
			->allowEmpty('signature')
			->add('signature',
				[
					'purifierMaxLength' => [
						'rule' => ['purifierMaxLength', 300],
						'provider' => 'purifier',
						'message' => __('Your biography can not contain more than {0} characters.', 3000)
					]
				]
			);
	}

/**
 * Settings validation rules.
 *
 * @param \Cake\Validation\Validator $validator The Validator instance.
 *
 * @return \Cake\Validation\Validator
 */
	public function validationSettings(Validator $validator) {
		return $validator
			->validatePresence('email', false)
			->notEmpty('email', __("Your E-mail can not be empty."))
			->add('email',
				[
					'email' => [
						'rule' => 'email',
						'message' => __("You must specify a valid E-mail address.")
					]
				]
			)
			->validatePresence('password', false)
			->notEmpty('password', __("You must specify your new password."))
			->validatePresence('password_confirm', false)
			->notEmpty('password_confirm', __("You must specify your password (confirmation)."))
			->add('password_confirm',
				[
					'lengthBetween' => [
						'rule' => ['lengthBetween', 8, 20],
						'message' => __("Your password (confirmation) must be between {0} and {1} characters.", 8, 20)
					],
					'equalToPassword' => [
						'rule' => function ($value, $context) {
								return (new DefaultPasswordHasher)->check($value, $context['data']['password']);
						},
						'message' => __("Your password confirm must match with your new password")
					]
				]
			);
	}
}