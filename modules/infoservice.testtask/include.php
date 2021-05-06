<?
// Основные константы
define('INFS_TESTTASK_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_TESTTASK_' . $key, $value);
}

/**
 * Создаваемые тематики групп соц. сетей
 *
 * Тематика "Тестовая тематика" для групп соц. сетей
 */
define('INFS_SOCNET_TEST_SUBJECT', 'test_subject');