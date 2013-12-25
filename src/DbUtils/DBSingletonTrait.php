<?php

namespace DbUtils;

trait DBSingletonTrait {

	private static $_instances = [];

	public static function setOptions(array $options = []) {

		if (!$options) {
			return;
		}

		//	только валидные ключи
		$options = array_intersect_key($options, static::$_options);

		//	ничего нового
		if (!array_diff_assoc($options, static::$_options)) {
			return;
		}

		if (!empty(static::$_instances)) {
			throw new \Exception('Соединения уже установлены');
		}

		static::$_options = $options + static::$_options;
	}


	final public static function getInstance($tag = 0, $options = []) {
		if (is_string($options)) {
			$options = [ 'dbname' => $options ];
		}
		$options = (array) $options;
		static::setOptions($options);

		$tag = abs($tag);
		if (!isset(static::$_instances[$tag])) {
			static::$_instances[$tag] = new static;
		}

		return static::$_instances[$tag];
	}

	final private function __construct() {
		static::_init();
	}

	protected static function _init() {
	}


	final private function __clone() {
	}

	final private function __sleep() {
	}

	final private function __wakeup() {
	}
}
