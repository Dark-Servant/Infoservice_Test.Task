<?
namespace Infoservice\TestTask\EventHandles;

abstract class SocNetGroupHandle
{
    /**
     * Обработчик ДО ДОБАВЛЕНИЯ группы соц. сети
     *
     * @param array $element - Данные элемента
     * @return void
     */
    public static function OnBeforeSocNetGroupAdd($element)
    {
        if (!Employment::setBussy()) return;
        // вызов методов хелдперов, решающих задачи
        Employment::setFree();
    }

    /**
     * Обработчик ДО ОБНОВЛЕНИЯ группы соц. сети
     *
     * @param integer $groupId - ID группы
     * @param array $element - Данные элемента
     * @return void
     */
    public static function OnBeforeSocNetGroupUpdate($groupId, $element)
    {
        if (!Employment::setBussy()) return;
        // вызов методов хелдперов, решающих задачи
        Employment::setFree();
    }

    /**
     * Обработчик ДО УДАЛЕНИЯ группы соц. сети
     *
     * @param integer $groupId - ID группы
     * @return void
     */
    public static function OnBeforeSocNetGroupDelete($groupId)
    {
        if (!Employment::setBussy()) return;
        // вызов методов хелдперов, решающих задачи
        Employment::setFree();
    }

    /**
     * Обработчик ДО ДОБАВЛЕНИЯ пользователя в группу соц. сети
     *
     * @param array $element - Данные элемента
     * @return void
     */
    public static function OnBeforeSocNetUserToGroupAdd($element)
    {
        if (!Employment::setBussy()) return;
        // вызов методов хелдперов, решающих задачи
        Employment::setFree();
    }

    /**
     * Обработчик ДО ОБНОВЛЕНИЯ пользователя в группе соц. сети
     *
     * @param integer $userGroupId - ID записи
     * @return void
     */
    public static function OnBeforeSocNetUserToGroupUpdate($userGroupId)
    {
        if (!Employment::setBussy()) return;
        // вызов методов хелдперов, решающих задачи
        Employment::setFree();
    }

    /**
     * Обработчик ДО УДАЛЕНИЯ пользователя из группы соц. сети
     *
     * @param integer $userGroupId - ID записи
     * @return void
     */
    public static function OnBeforeSocNetUserToGroupDelete($userGroupId)
    {
        if (!Employment::setBussy()) return;
        // вызов методов хелдперов, решающих задачи
        Employment::setFree();
    }
};