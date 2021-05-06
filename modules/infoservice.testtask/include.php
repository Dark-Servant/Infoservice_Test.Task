<?
// Основные константы
define('INFS_TESTTASK_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_TESTTASK_' . $key, $value);
}

// Иденификатор инфоблока "Оргструктура" -> "Подразделения"
define('INFS_DIVISION_IBLOCK_ID', 3);

/**
 * Пользовательское поле для подразделений, в нем указывается короткое название
 * подразделения
 */
define('INFS_DIVISION_SECTION_SHORT_NAME', 'UF_SHORT_NAME');