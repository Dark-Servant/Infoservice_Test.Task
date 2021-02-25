<?
namespace Infoservice\TestTask\EventHandles;

abstract class UserEventHandle
{
    /**
     * Обработчик ДО ИЗМЕНЕНИЯ ДАННЫХ ПОЛЬЗОВАТЕЛЯ
     *
     * @param array $data - Данные полей у изменяемого пользователя
     * @return void
     */
    public static function OnBeforeUserUpdate(array $data)
    {
        if (!Employment::setBussy()) return;
        // вызов методов хелдперов, решающих задачи
        Employment::setFree();
    }
};