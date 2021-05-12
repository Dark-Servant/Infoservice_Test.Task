<?
// Основные константы
define('INFS_TESTTASK_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_TESTTASK_' . $key, $value);
}

/**
 * Константы для создаваемых модулем пользовтельских группах
 * 
 * Пользовательская группа "Первая тестовая"
 */
define('INFS_FIRST_USER_GROUP', 'first_user_group');

// Пользовательская группа "Вторая тестовая"
define('INFS_SECOND_USER_GROUP', 'second_user_group');

// Идентификаторы пользователей для второй тестовой группы
define('INFS_SECOND_USER_USER_IDS', [1]);