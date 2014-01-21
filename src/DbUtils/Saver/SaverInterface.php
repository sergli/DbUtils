<?PHp

namespace DbUtils\Saver;

use DbUtils\Adapter\AdapterInterface;

/**
 * Интерфейс класса, кот. сохраняет данные в БД
 *
 * Предназначен для автоматизированного сохранения/обновления
 * больших объёмов данных в таблице БД.
 *
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
interface SaverInterface extends \Countable {

	public function __construct(AdapterInterface $adapter,
		$tableName, array $columns = null);

	/**
	 * Устанавливает доп. опции
	 *
	 * @param int $options
	 * @access public
	 * @return void
	 */
	public function setOptions($options);

	/**
	 * Добавляет запись в буфер
	 *
	 * @param array $row
	 * @access public
	 * @return void
	 */
	public function add(array $row);

	/**
	 * Сохраняет данные в таблицу
	 *
	 * @access public
	 * @return void
	 */
	public function save();

	/**
	 * Обнуляет буферы
	 *
	 * @access public
	 * @return void
	 */
	public function reset();

	/**
	 * Текущее кол-во записей в буфере
	 *
	 * @access public
	 * @return int
	 */
	public function getSize();

	/**
	 * Возвращает размер порции для вставки
	 *
	 * @access public
	 * @return int
	 */
	public function getBatchSize();

	/**
	 * Устанавливает размер порции для вставки
	 *
	 * @param int $size
	 * @access public
	 * @return boolean
	 * @throws \Exception orlly?
	 */
	public function setBatchSize($size);
}
