<?
// Основные константы
define('INFS_CHILDMODULE_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('INFS_CHILDMODULE_' . $key, $value);
}

/**
 * Здесь надо указывать константы для идентификаторов и названий, которые начинают
 * использоваться в дочернем модуле
 */