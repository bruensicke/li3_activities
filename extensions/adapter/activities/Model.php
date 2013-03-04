<?php

namespace li3_activities\extensions\adapter\activities;

class Model extends \lithium\core\Object {

	/**
	 * Class constructor.
	 *
	 * @param array $config Settings used to configure the adapter. Available options:
	 *              - `'model'` _string_: Full qualified class name to use
	 */
	public function __construct(array $config = array()) {
		$defaults = array(
			'model' => '\li3_activities\models\Activities'
		);
		parent::__construct($config + $defaults);
	}

	/**
	 * Tracks an Activity using a model
	 *
	 * @param string $type what type is this event
	 * @param string $data additional data to be tracked for that event
	 * @param array $options additional options
	 * @return array an associative array containing all data
	 */
	public function track($type, array $data = array(), array $options = array()) {
		$config = $this->_config;
		$params = compact('type', 'data', 'options');
		return function($self, $params) use (&$config) {
			return $config['model']::track($params['type'], $params['data'], $params['options']);
		};
	}

	/**
	 * Returns Activities using a model
	 *
	 * @param string|array $scope if string is given, a check is made of a group with that name
	 *        exists and if so, the types for that group will be retrieved. If no group exists, it
	 *        will be used as name for event_type, without any further checks.
	 *        If it is an array, it can be used like `conditions` as on `Model::find()`, the most
	 *        useful use-case would be to pass in a foreign_id, like `array('user_id' => $id)`.
	 * @param array $options additional options, identical to those for `Model::find()`
	 * @return object a collection object, containing all resulting activities, also:
	 *     - `since`: id of last activity, to retrieve data since then
	 */
	public function get($scope, array $options = array()) {
		$config = $this->_config;
		return $config['model']::get($scope, $options);
	}
}

?>