<?php

namespace autocomplete\complete\generate\utils\mysqli;

die('TODO');

require_once __DIR__ . '/../../init.php';

/**
 * Фабрика для создания классов MysqliSaver
 * 
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
class MysqliSaverFactory {
	
	/**
	 * Допустимые сейверы
	 * 
	 * @var string[]
	 * @static
	 */
	private static $saverTypes = array(
		'load_data'		=>	'MysqliLoadDataSaver',
		'bulk_insert'	=>	'MysqliBulkInsertSaver',
		'bulk_update'	=>	'MysqliBulkUpdater',
	);

	/**
	 * Создать и вернуть сейвер, заданный по тегу
	 * 
	 * @param string $saverType тег
	 * @param array $options (mysqli, table, structure) 
	 * @static
	 * @access public
	 * @return MysqliSaver
	 */
	public static function getSaver($saverType, array $options) {

		if (!isset(self::$saverTypes[$saverType])) {
			require_once __DIR__ . '/MysqliSaverException.class.php';
			throw new MysqliSaverException("Неверный сейвер: $saverType");
		}

		$className = self::$saverTypes[$saverType];

		require_once __DIR__ . '/' . $className . '.class.php';

		$className = __NAMESPACE__ . '\\' . $className;
		
		$reflect = new \ReflectionClass($className);
		return $reflect->newInstanceArgs($options);
	}

	/**
	 * Возвращает массив с названиями допустимых сейверов
	 * 
	 * @return string[]
	 * @see $types
	 * @static
	 */
	public static function getSaverTypes() {
		return self::$saverTypes;
	}
}

