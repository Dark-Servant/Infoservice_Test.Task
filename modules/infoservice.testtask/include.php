<?
// Основные константы
define('INFS_TESTTASK_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_TESTTASK_' . $key, $value);
}

// Название типа инфоблока
define('INFS_TEST_TASK_IBLOCK_TYPE', 'test_task');

// Символьные коды инфорблоков
define('INFS_TEST_TASK_IBLOCK', 'test_task');

// Символьные коды свойств инфоблоков
define('INFS_TT_IB_STR_PR', 'STR_PR');
define('INFS_TT_IB_INT_PR', 'INT_PR');
