<?
namespace Infoservice\TestTask\Agents;

use \Bitrix\Main\Localization\Loc;

abstract class Tests
{
    /**
     * Агент, который записывает сообщение в лог.
     * Результат можно посмотреть на странице /bitrix/admin/event_log.php
     * 
     * @return string
     */
    public static function saveMarkToLog(string $message = '')
    {
        \CEventLog::Log(
            \CEventLog::SEVERITY_INFO,
            __METHOD__,
            __METHOD__, 0,
            $message ?: Loc::getMessage('TESTTASK_AGENTS_TESTS'),
            \CSite::GetDefSite()
        );
        return __METHOD__ . '(' . ($message ? '"' . addslashes($message) . '"' : '') . ');';
    }
}