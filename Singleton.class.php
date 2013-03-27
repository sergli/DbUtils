<?php

namespace db_utils;

trait Singleton {

	private static $_instances = [];

	final public static function getInstance($tag = 0) {
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
