<?
// Основные константы
define('INFS_TESTTASK_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_TESTTASK_' . $key, $value);
}

/**
 * Агенты
 *
 * Агент для записи в лог отметки со значением по-умолчанию
 */
define('INFS_TESTTASK_AGENT_NAME_WITHOUT_PARAM', 'Agents\\Tests::saveMarkToLog');

// Агент для записи в лог отметки с указанным значением
define('INFS_TESTTASK_AGENT_NAME_WITH_PARAM', 'Agents\\Tests::saveMarkToLog("CONSTANT")');