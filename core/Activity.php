<?php

namespace li3_activities\core;

use lithium\util\Set;
use lithium\util\String;

class Activity extends \lithium\core\Adaptable {

	/**
	 * Stores configurations for various authentication adapters.
	 *
	 * @var object `Collection` of authentication configurations.
	 */
	protected static $_configurations = array();

	/**
	 * Stores event-types and their messages.
	 *
	 * @var array
	 */
	protected static $_events = array();

	/**
	 * Libraries::locate() compatible path to adapters for this class.
	 *
	 * @see lithium\core\Libraries::locate()
	 * @var string Dot-delimited path.
	 */
	protected static $_adapters = 'adapter.activities';

	/**
	 * Dynamic class dependencies.
	 *
	 * @var array Associative array of class names & their namespaces.
	 */
	protected static $_classes = array();

	/**
	 * Acts as a proxy for the `track()` method, allowing Activites to be tracked
	 * as method-names, i.e.:
	 * {{{
	 * Activity::sync_complete(compact('name'));
	 * // This is equivalent to Activity::track('sync_complete', compact('name'))
	 * }}}
	 *
	 * @param string $type The name of the method called on the `Activity` class. This should map
	 *               to an event type.
	 * @param array $params An array of parameters passed in the method.
	 * @return boolean Returns `true` or `false`, depending on the success of the `track()` method.
	 */
	public static function __callStatic($type, $params) {
		$params += array(null, array());
		return static::track($type, $params[0], $params[1]);
	}

	/**
	 * Called when an adapter configuration is first accessed, this method sets the default
	 * configuration for session handling. While each configuration can use its own session class
	 * and options, this method initializes them to the default dependencies written into the class.
	 * For the session key name, the default value is set to the name of the configuration.
	 *
	 * @param string $name The name of the adapter configuration being accessed.
	 * @param array $config The user-specified configuration.
	 * @return array Returns an array that merges the user-specified configuration with the
	 *         generated default values.
	 */
	protected static function _initConfig($name, $config) {
		$defaults = array('adapter' => 'Model', 'events' => true, 'groups' => array());
		$config = parent::_initConfig($name, $config) + $defaults;
		return $config;
	}

	/**
	 * Tracks Activity in an application, using all Activity Loggers configured
	 *
	 * @param string $type what type is this event
	 * @param string $data additional data to be tracked for that event
	 * @param array $options additional options
	 * @return array an associative array containing all data
	 * @filter
	 */
	public static function track($type, array $data = array(), array $options = array()) {
		$defaults = array('name' => null);
		$options += $defaults;
		$result = true;

		if ($name = $options['name']) {
			$methods = array($name => static::adapter($name)->track($type, $data, $options));
		} else {
			$methods = static::_configsByType($type, $data, $options);
		}

		foreach ($methods as $name => $method) {
			$params = compact('type', 'data', 'options');
			$config = static::_config($name);
			$result &= static::_filter(__FUNCTION__, $params, $method, $config['filters']);
		}
		return $methods ? $result : false;
	}

	/**
	 * Configure Events that can be tracked.
	 *
	 * Expects an array, containing keys of a named-type and their according
	 * messages as values, with placeholders to be taken from data. Messages
	 * can contain placeholders, according to String::insert
	 *
	 * @see lithium\util\String::insert()
	 * @param string $events an array of Events to be trackable
	 * @param array $options additional options, e.g.
	 *              - `'merge'` boolean: whether to merge given events with
	 *                existing ones, or not, defaults to true.
	 *              - `'replace'` boolean: whether to replace given events with
	 *                existing ones, or not, defaults to false.
	 * @return array all valid events, that are present afterwards
	 */
	public static function events(array $events, array $options = array()) {
		$defaults = array('merge' => true, 'replace' => false);
		$options += $defaults;
		if ($options['replace'] || !$options['merge']) {
			static::$_events = $events;
		} else {
			$events = array_merge(static::$_events, $events);
		}
		return static::$_events = $events;
	}

	/**
	 * generates a parsed message, depending on given $type
	 *
	 * @param string $type what type is this event
	 * @param string $data additional data to be tracked for that event
	 * @return string|boolean parsed string of message, or false in case of error
	 */
	public static function message($type, $data = array()) {
		if (!array_key_exists($type, Activity::$_events)) {
			return false;
		}
		return String::insert(Activity::$_events[$type], Set::flatten($data));
	}

	/**
	 * Retrieve a list of event-types, according to group-name
	 *
	 * You can create type-groups in your configuration with a group-key
	 * and a set of types, that belong to that group. See bootstrap/activities.php
	 * for more details on how to use them.
	 *
	 * @param string $name Name of Group to retrieve types for
	 * @param array $options additional options, e.g.
	 *              - `'name'` string: Name of configuration to retrieve groups for,
	 *                         defaults to 'default'.
	 * @return array an array holding all types, that belong to given group, if no
	 *               group for given name is found, an empty array is returned.
	 */
	public static function group($name, array $options = array()) {
		$defaults = array('name' => 'default');
		$options += $defaults;
		$config = static::config($options['name']);
		if (!array_key_exists($name, $config['groups'])) {
			return array();
		}
		return $config['groups'][$name];
	}

	/**
	 * Gets the names of the adapter configurations that respond to a specific type. The list
	 * of adapter configurations returned will be used to write Activity with the given type.
	 *
	 * @param string $type The Type of message to be written.
	 * @param string $data Array with additional data about that Activity.
	 * @param array $options Adapter-specific options.
	 * @return array Returns an array of names of configurations which are set up to respond to the
	 *         message types specified in `$types`, or configured to respond to _all_ activities.
	 */
	protected static function _configsByType($type, $data, array $options = array()) {
		$configs = array();
		$key = 'events';
		foreach (array_keys(static::$_configurations) as $name) {
			$config = static::config($name);
			$nameMatch = ($config[$key] === true || $config[$key] === $type);
			$arrayMatch = (is_array($config[$key]) &&
			(in_array($type, $config[$key]) || array_key_exists($type, $config[$key])));

			if ($nameMatch || $arrayMatch) {
				$method = static::adapter($name)->track($type, $data, $options);
				$method ? $configs[$name] = $method : null;
			}
		}
		return $configs;
	}
}

?>