<?php

namespace li3_activities\models;

use li3_activities\core\Activity;

class Activities extends \lithium\data\Model {

	/**
	 * Custom find query properties, indexed by name.
	 *
	 * @var array
	 */
	public $_finders = array(
		'latest' => array(
			'limit' => 250,
		),
	);

	protected $_query = array(
		'order' => array('created' => 'DESC'),
	);

	/**
	 * initialize method
	 *
	 * @see lithium\data\Model
	 * @return void
	 */
	public static function __init(array $options = array()) {
		static::config($options);
		static::applyFilter('save', function ($self, $params, $chain) {
			$entity = &$params['entity'];
			if (!$entity->exists()) {
				$entity->created = date(DATE_ATOM);
			}
			$entity->message = Activity::message($entity->type, $entity->data->data());
			return $chain->next($self, $params, $chain);
		});
	}

	/**
	 * Tracks an Activity
	 *
	 * @param string $type what type is this event
	 * @param string $data additional data to be tracked for that event
	 * @param array $options additional options
	 * @return array an associative array containing all data
	 */
	public static function track($type, array $data = array(), array $options = array()) {
		$defaults = array();
		$options += $defaults;
		$params = compact('type', 'data', 'options');
		$filter = function($self, $params) {
			$data = array('type' => $params['type'], 'data' => $params['data']);
			$entity = Activities::create($data);
			$success = $entity->save();
			if (!$success) {
				return false;
			}
			return $entity->data();
		};
		return static::_filter(__FUNCTION__, $params, $filter);
	}
}

?>