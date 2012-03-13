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
}
