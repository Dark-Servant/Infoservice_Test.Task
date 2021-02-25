<?
namespace Infoservice\TestTask\EventHandles;

/**
 *  Класс-пример для обработки событий инфоблока с таким же символьным
 *  кодом, как и название класса
 */
abstract class TestTask
{
    /**
     * Обработчик события ДО ДОБАВЛЕНИЯ элемента
     *
     * @param array $element - данные элемента
     * @return mixed
     */
    public static function OnBeforeIBlockElementAdd(array&$element)
    {
        // вызов методов хелдперов, решающих задачи
    }

    /**
     * Обработчик события ПОСЛЕ ДОБАВЛЕНИЯ элемента
     *
     * @param array $element - данные элемента
     * @return mixed
     */
    public static function OnAfterIBlockElementAdd(array&$element)
    {
        // вызов методов хелдперов, решающих задачи
    }

    /**
     * Обработчик события ДО ОБНОВЛЕНИЯ элемента
     *
     * @param array $element - данные элемента
     * @return mixed
     */
    public static function OnBeforeIBlockElementUpdate(array&$element)
    {
        // вызов методов хелдперов, решающих задачи
    }

    /**
     * Обработчик события ДО УДАЛЕНИЯ элемента
     *
     * @param integer $id - идентификатор элемента
     * @return mixed
     */
    public static function OnBeforeIBlockElementDelete($id)
    {
        // вызов методов хелдперов, решающих задачи
    }

};