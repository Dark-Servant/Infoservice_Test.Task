<?
// Основные константы
define('INFS_TESTTASK_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_TESTTASK_' . $key, $value);
}

/**
 * HighloadBlock
 * 
 * HighloadBlock для тестирования
 */
define('INFS_HL_TEST_MESSAGES', 'test_messages');

/**
 * Идентификаторы уже существующих HighloadBlock
 */
define('INFS_HL_ALLREADY_EXISTS_UNIT_ID', 1);

/**
 * Тестовые пользовательские поля для HighloadBlock
 * 
 * Пользовательское поле "Строковое поле"
 */
define('INFS_STR_FIELD', 'UF_STR_FIELD');

// Пользовательское поле "Числовое поле"
define('INFS_INT_FIELD', 'UF_INT_FIELD');

// Пользовательское поле "Поле для существующего highload"
define('INFS_ALLREADY_EXISTS_HL_FIELD', 'UF_ALLREADY_EXISTS');