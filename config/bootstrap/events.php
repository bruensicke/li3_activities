<?php

use li3_activities\core\Activity;

/**
 * Events should be configured, in order to parse a message
 * for a given type. It can contain placeholders and given
 * data will be used to replace those. Make sure, you
 * always put in the data you need in order to generate
 * the messages, you may want (even later on).
 * Also, have a look at String::insert() to see, how
 * placeholders work.
 *
 * @see li3_activities\core\Activity::events()
 * @see li3_activities\core\Activity
 * @see lithium\util\String::insert()
 */
Activity::events(array(
	'saved' => '{:name} [{:id}] {:type}.',
	// 'action' => '{:controller}::{:action} called',
));

/**
 * Here we filter our-self into the whole application to
 * track Activity on whatever interests us.
 *
 * @see lithium\core\StaticObject::applyFilter()
 * @see li3_activities\core\Activity
 */

/**
 * Write an Activity for any Model::save().
 *
 * In order to have this working, copy it to your applications bootstrap
 * and adjust the model namespace and name to your own model
 *
 * @see lithium\data\Model
 * @see li3_activities\core\Activity
 */
// lithium\data\Model::applyFilter('save', function($self, $params, $chain) {
// 	$entity = &$params['entity'];
// 	$data = array(
// 		'name' => $entity->model(),
// 		'type' => ($entity->exists()) ? 'updated' : 'created',
// 	);
// 	if (!$result = $chain->next($self, $params, $chain)) {
// 		return false;
// 	}
// 	$data['id'] = (string)$entity->{$entity->key()};
// 	Activity::track('saved', $data);
// 	return $result;
// });

/**
 * Track all calls to Dispatcher and log Activity about called Controller::action.
 *
 * @see lithium\action\Dispatcher
 * @see li3_activities\core\Activity
 */
// lithium\action\Dispatcher::applyFilter('run', function($self, $params, $chain) {
// 	$result = $chain->next($self, $params, $chain);
// 	Activity::action($params['request']->params);
// 	return $result;
// });

