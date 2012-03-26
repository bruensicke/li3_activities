<?php

namespace li3_activities\controllers;

use li3_activities\models\Activities;
use li3_activities\core\Activity;

class ActivitiesController extends \lithium\action\Controller {

	public function index($input = null) {
		$defaults = array('limit' => 250, 'order' => array('created' => 'DESC'));
		if (!empty($input) && is_array($input)) {
			$defaults += $input;
		}
		$options = $this->_options($defaults);
		$activities = Activities::find('all', $options);
		$config = Activity::config('default');
		$groups = $config['groups'];
		return compact('activities', 'groups');
	}

	public function view() {
		$_id = $this->request->params['id'];
		$activity = Activities::first($_id);
		return compact('activity');
	}

	public function group($group) {
		$this->_render['template'] = 'index';
		$type = Activity::group($group);
		$this->set(compact('group'));
		return $this->index(array('conditions' => compact('type')));
	}

	public function type($type) {
		$this->_render['template'] = 'index';
		return $this->index(array('conditions' => compact('type')));
	}

	public function purge() {
		Activities::remove();
		$this->redirect('Activities::index');
	}

	/**
	 * Generates options out of named params
	 *
	 * @param string $defaults all default options you want to have set
	 * @return array merged array with all $defaults, $options and named params
	 */
	protected function _options($defaults = array()) {
		$options = array();
		if (!empty($this->request->args)) {
			foreach ($this->request->args as $param) {
				if (stristr($param, ':')) {
					list($key, $val) = explode(':', $param);
				} else {
					$key = $param;
					$val = true;
				}
				$options[$key] = $val;
			}
		}
		$options = array_merge($defaults, $options);
		$this->set($options);
		return $options;
	}
}

?>