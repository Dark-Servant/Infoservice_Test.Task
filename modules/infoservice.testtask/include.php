<?
// Основные константы
define('INFS_TESTTASK_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_TESTTASK_' . $key, $value);
}

// Пользовательское поле для задач "Тестовое поле"
define('INFS_TEST_FIELD', 'UF_TEST_FIELD');