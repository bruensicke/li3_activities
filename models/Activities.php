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
			'fields' => array('created', 'type', 'message'),
		),
	);

	protected $_query = array(
		'order' => array('created' => 'DESC'),
		'limit' => 250,
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
			$scope = $self::getScope($params['data']);
			$data = array('type' => $params['type'], 'data' => $self::cleanData($params['data']));
			$activity = $self::create($scope + $data);
			$activity->message = Activity::message($activity->type, $activity->data->data());
			$activity->created = time();
			$success = $activity->save();
			if (!$success) {
				return false;
			}
			return $activity->data();
		};
		return static::_filter(__FUNCTION__, $params, $filter);
	}

	/**
	 * returns a list of activies, according to a given type, group or other conditions
	 *
	 * {{{
	 *   Activities::get('event_type');
	 *   Activities::get('group_name');
	 *   Activities::get(array('type' => 'event_type'));
	 *   Activities::get(array('type' => array('event_type1', 'event_type2')));
	 *   Activities::get(array('group' => 'group_name'));
	 *   Activities::get(array('foreign_id' => $id));
	 *   Activities::get(array('foreign_id' => array($id1, $id2)));
	 * }}}
	 *
	 * @see lithium\data\Model::find()
	 * @param string|array $scope if string is given, a check is made of a group with that name
	 *        exists and if so, the types for that group will be retrieved. If no group exists, it
	 *        will be used as name for event_type, without any further checks.
	 *        If it is an array, it can be used like `conditions` as on `Model::find()`, the most
	 *        useful use-case would be to pass in a foreign_id, like `array('user_id' => $id)`.
	 * @param array $options additional options, identical to those for `Model::find()`
	 * @return object a collection object, containing all resulting activities, also:
	 *     - `since`: id of last activity, to retrieve data since then
	 * @filter
	 */
	public static function get($scope, array $options = array()) {
		$defaults = array('since' => false);
		$options += $defaults;
		$params = compact('scope', 'options');
		return static::_filter(__METHOD__, $params, function($self, $params) {
			extract($params);
			if (is_string($scope)) {
				$types = Activity::group($scope);
				$scope = (!empty($types))
					? array('type' => $types)
					: array('type' => $scope);
			}
			if (isset($scope['group'])) {
				$scope['type'] = Activity::group($scope['group']);
				unset($scope['group']);
			}
			if ($options['since']) {
				$entity = $self::first($options['since'], array('fields' => 'created'));
				if ($entity) {
					$scope['created'] = array('>=' => $entity->created);
				}
			}
			unset($options['since']);
			$options['conditions'] = $scope;
			return $self::find('all', $options);
		});
	}

	/**
	 * retrieves a list of foreign ids out of various data items
	 *
	 * all array_keys, that end in `_id` are used, as well as the primary id, of all entities found.
	 *
	 * @see li3_activities\models\Activities::track()
	 * @param array $data an array of data, that is given to track() method.
	 * @return array an array of all foreign-ids that has been found, including their values.
	 * @filter
	 */
	public static function getScope(array $data = array()) {
		$scope = array();
		foreach($data as $key => $item) {
			if ($item instanceof Entity) {
				$scope[sprintf('%s_id', $key)] = (string) $item->{$item->key()};
			} elseif (is_array($item) && isset($item['_id'])) {
				$scope[$key] = (string) $item['_id'];
			} elseif (preg_match('/^(.+)_id$/', $key, $matches) && is_scalar($item)) {
				list($attribute, $name) = $matches;
				$scope[$attribute] = (string) $item;
			}
		}
		return $scope;
	}

	/**
	 * only use data of objects, in case they are contained within data
	 *
	 * @param array $data passed in data
	 * @return array returns data, without continaing objects
	 */
	public static function cleanData(array $data = array()) {
		foreach($data as $key => $item) {
			if (is_array($item)) {
				$data[$key] = static::cleanData($item);
			}
			if ($item instanceof \lithium\data\Entity) {
				$data[$key] = $item->data();
			}
		}
		return $data;
	}
}

?>