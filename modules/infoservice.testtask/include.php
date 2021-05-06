<?
// Основные константы
define('INFS_TESTTASK_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_TESTTASK_' . $key, $value);
}

// тип календаря "Тип календаря "Тестирование"
define('INFS_CALENDAR_TYPE_TESTING', 'type_testing');

// секция "Первая секция для тестирования" для типа календаря "Тип календаря "Тестирование"
define('INFS_CALENDAR_SECTION_FIRST_TESTING', 'section_first_testing');

// секция "Вторая секция для тестирования" для типа календаря "Тип календаря "Тестирование"
define('INFS_CALENDAR_SECTION_SECOND_TESTING', 'section_second_testing');