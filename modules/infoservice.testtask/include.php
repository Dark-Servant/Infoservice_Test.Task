<?
// Основные константы
define('INFS_TESTTASK_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_TESTTASK_' . $key, $value);
}

// Название типа инфоблока
define('INFS_LIST_IBLOCK_TYPE', 'lists');

// Символьные коды инфорблоков
define('INFS_TEST_TASK_IBLOCK', 'test_task');

// Символьные коды свойств инфоблоков
define('INFS_TT_IB_STR_PR', 'STR_PR');
define('INFS_TT_IB_INT_PR', 'INT_PR');

/**
 * Константы для обозначения в инфоблоке тех параметров, которые надо видеть как поля для списка-инфоблока
 * Стоит обратить внимание, что перед значениями констант стоит префикс (в виде имени инфоблока, но можно и
 * другое указать), отделенный точкой. При записи в системе информации о поле инфоблока сам префикс будет
 * проигнорирован, но запопнен в настройках модуля. Это необходимо, чтобы указанные как поля списка некоторые
 * параметры, например, название инфоблока (NAME), не могли затереть друг о друге информацию в параметрах модуля,
 * ведь информация о их добавлении в параметрах модуля сохраняется под полным значением константы
 * 
 * Константа для параметра инфоблока "Название"
 */
define('INFS_TT_IB_LIST_NAME', INFS_TEST_TASK_IBLOCK . '.NAME');
// Константа для параметра инфоблока "Детальное описание"
define('INFS_TT_IB_LIST_DETAIL_TEXT', INFS_TEST_TASK_IBLOCK . '.DETAIL_TEXT');
// Константа для параметра инфоблока "Дата создания"
define('INFS_TT_IB_LIST_DATE_CREATE', INFS_TEST_TASK_IBLOCK . '.DATE_CREATE');
// Константа для параметра инфоблока "Кем создан"
define('INFS_TT_IB_LIST_CREATED_BY', INFS_TEST_TASK_IBLOCK . '.CREATED_BY');