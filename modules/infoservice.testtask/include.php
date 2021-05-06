<?
// Основные константы
define('INFS_TESTTASK_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_TESTTASK_' . $key, $value);
}

/**
 * Модуль указал свой язык сайта, который установится в файле dbconn.php,
 * что находится в ядре Битрикса, и который служит для указания параметров 
 * подключения к БД
 */
define('INFS_LANGUAGE_ID', 'ru');