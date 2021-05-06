<?
$moduleIncludeFile = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/infoservice.testtask/include.php';
if (file_exists($moduleIncludeFile)) {
    session_start();
    require_once $moduleIncludeFile;

    define('LANGUAGE_ID', INFS_LANGUAGE_ID);
}