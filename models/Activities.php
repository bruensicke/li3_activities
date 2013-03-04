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
			$scope = $self::getScope($params['data']);
			$entity = $self::create($data + $scope);
			$entity->message = Activity::message($entity->type, $entity->data->data());
			$entity->created = time();
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