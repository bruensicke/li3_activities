<?php

use li3_activities\core\Activity;

/**
 * Default configuration uses built-in Model to track
 * all Activity on your application. Please provide
 * useful messages in `events.php`. Currently
 * there is only one Adapter, Model which you can
 * also use to track Activity with your own custom
 * Model. In that case, you should implement a static
 * method track() with the same signature as provided
 * by the built-in Activity Model.
 *
 * @see li3_activities\core\Activity
 * @see li3_activities\extensions\adapter\activities\Model
 * @see li3_activities\models\Activities
 * @see lithium\core\Adaptable
 */
Activity::config(array(
	'default' => array(
		'adapter' => 'Model'
	)
));

/**
 * Example of a custom Activity Class
 *
 * In order to allow for that, you should implement a static
 * method `track` in your Model, that works as the one provided
 * with this library. Make sure, you parse the message to benefit
 * from pre-parsed messages while reading Activity logs.
 *
 * @see li3_activities\core\Activity
 * @see li3_activities\extensions\adapter\activities\Model
 * @see li3_activities\models\Activities
 * @see lithium\core\Adaptable
 */
// Activity::config(array(
// 	'custom' => array(
// 		'adapter' => 'Model',
// 		'model' => '\app\models\CustomActivity',
// 		'events' => array(
// 			'created',
// 			'updated'
// 		),
//		'groups' => array(
//			'groupname' => array('created', 'updated')
//		)
// 	)
// ));
//

?>