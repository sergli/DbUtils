<?PHp

namespace autocomplete\complete\generate\utils\db;

/**
 * Интерфейс класса, кот. сохраняет данные в БД
 *
 * Предназначен для автоматизированного сохранения/обновления
 * больших объёмов данных в таблице БД.
 * 
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
interface iDBDataSaver {
	
	/**
	 * Устанавливает доп. опции
	 * 
	 * @param array $options 
	 * @access public
	 * @return void
	 */
	public function setOptions(array $options);

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
	 * @throws DBDataSaverException
	 */
	public function setBatchSize($size);
}
