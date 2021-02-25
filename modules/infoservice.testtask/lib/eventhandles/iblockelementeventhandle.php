<?
namespace Infoservice\TestTask\EventHandles;

use Bitrix\Main\Loader;

abstract class IBlockElementEventHandle
{
    protected static $noErrors = true;

    /**
     * Метод для вызова обработчика для указанного события конкретного инфоблока
     *
     * @param $iblockId - идентификатор инфоблока
     * @param string $methodName - название события
     * @param array $parameters - список параметров для обработчика
     * @return mixed
     */
    protected static function sendByIBlockId($iblockId, string $methodName, array $parameters)
    {
        if (!Employment::setBussy()) return;

        Loader::includeModule('iblock');

        $iblock = \CIBlock::GetById($iblockId)->Fetch();
        return Employment::sendOtherHandle($iblock['CODE'], $methodName, $parameters);
    }

    /**
     * Обработчик события ПРИ НАЧАЛЕ ДОБАВЛЕНИЯ элемента
     *
     * @param array $element - данные элемента
     * @return mixed
     */
    public static function OnStartIBlockElementAdd(array&$element)
    {
        self::$noErrors = self::sendByIBlockId($element['IBLOCK_ID'], __METHOD__, [$element]) !== false;
        return self::$noErrors;
    }
    
    /**
     * Обработчик события ДО ДОБАВЛЕНИЯ элемента
     *
     * @param array $element - данные элемента
     * @return mixed
     */
    public static function OnBeforeIBlockElementAdd(array&$element)
    {
        self::$noErrors = self::sendByIBlockId($element['IBLOCK_ID'], __METHOD__, [$element]) !== false;
        return self::$noErrors;
    }

    /**
     * Обработчик события НА ДОБАВЛЕНИЕ элемента
     * 
     * @param array $element - данные элемента
     * @return mixed
     */
    public static function OnIBlockElementAdd($element)
    {
        if (!self::$noErrors) return;

        return self::sendByIBlockId($element['IBLOCK_ID'], __METHOD__, [$element]);
    }

    /**
     * Обработчик события ПОСЛЕ ДОБАВЛЕНИЯ элемента
     * 
     * @param array $element - данные элемента
     * @return mixed
     */
    public static function OnAfterIBlockElementAdd($element)
    {
        if (!self::$noErrors) return;

        return self::sendByIBlockId($element['IBLOCK_ID'], __METHOD__, [$element]);
    }

    /**
     * Обработчик события ПРИ НАЧАЛЕ ОБНОВЛЕНИЯ элемента
     * 
     * @param array $element - данные элемента
     * @return mixed
     */
    public static function OnStartIBlockElementUpdate(array&$element)
    {
        self::$noErrors = self::sendByIBlockId($element['IBLOCK_ID'], __METHOD__, [$element]) !== false;
        return self::$noErrors;
    }
    
    /**
     * Обработчик события ДО ОБНОВЛЕНИЯ элемента
     * 
     * @param array $element - данные элемента
     * @return mixed
     */
    public static function OnBeforeIBlockElementUpdate(array&$element)
    {
        self::$noErrors = self::sendByIBlockId($element['IBLOCK_ID'], __METHOD__, [$element]) !== false;
        return self::$noErrors;
    }

    /**
     * Обработчик события НА ОБНОВЛЕНИЕ элемента
     * 
     * @param array $shortInfo - краткая информация об элементе и его инфоблоке
     * @return mixed
     */
    public static function OnIBlockElementUpdate($shortInfo)
    {
        if (!self::$noErrors) return;

        return self::sendByIBlockId($shortInfo['IBLOCK_ID'], __METHOD__, [$shortInfo]);
    }

    /**
     * Обработчик события ПОСЛЕ ОБНОВЛЕНИЯ элемента
     * 
     * @param array $shortInfo - краткая информация об элементе и его инфоблоке
     * @return mixed
     */
    public static function OnAfterIBlockElementUpdate($shortInfo)
    {
        if (!self::$noErrors) return;

        return self::sendByIBlockId($shortInfo['IBLOCK_ID'], __METHOD__, [$shortInfo]);
    }

    /**
     * Обработчик события ДО УДАЛЕНИЯ элемента
     * 
     * @param integer $id - идентификатор элемента
     * @return mixed
     */
    public static function OnBeforeIBlockElementDelete($id)
    {
        if (!Employment::setBussy()) return;

        Loader::includeModule('iblock');

        $iblock = \CIBlockElement::GetById($id)->Fetch();
        self::$noErrors = Employment::sendOtherHandle($iblock['IBLOCK_CODE'], __METHOD__, [$id]) !== false;
        return self::$noErrors;
    }

    /**
     * Обработчик события НА УДАЛЕНИЕ элемента
     * 
     * @param integer $id - идентификатор элемента
     * @param array $shortInfo - краткая информация об элементе и его инфоблоке
     * @return mixed
     */
    public static function OnIBlockElementDelete($id, $shortInfo)
    {
        if (!self::$noErrors) return;

        return self::sendByIBlockId($shortInfo['IBLOCK_ID'], __METHOD__, [$id, $shortInfo]);
    }

    /**
     * Обработчик события ПОСЛЕ УДАЛЕНИЯ элемента
     * 
     * @param array $shortInfo - краткая информация об элементе и его инфоблоке
     * @return mixed
     */
    public static function OnAfterIBlockElementDelete($shortInfo)
    {
        if (!self::$noErrors) return;

        return self::sendByIBlockId($shortInfo['IBLOCK_ID'], __METHOD__, [$shortInfo]);
    }

    /**
     * Обработчик события ДО ОБНОВЛЕНИЯ свойств элемента инфоблока методом 
     * CIBlockElement::SetPropertyValuesEx
     *
     * @param integer $elementID - идентификатор элемента инфоблока
     * @param integer $iblockID - идентификатор инфоблока
     * 
     * @return mixed
     */
    public static function OnIBlockElementSetPropertyValuesEx($elementID, $iblockID)
    {
        self::$noErrors = self::sendByIBlockId($iblockID, __METHOD__, func_get_args()) !== false;
        return self::$noErrors;
    }
    
    /**
     * Обработчик события ПОСЛЕ ОБНОВЛЕНИЯ свойств элемента инфоблока методом 
     * CIBlockElement::SetPropertyValuesEx
     *
     * @param integer $elementID - идентификатор элемента инфоблока
     * @param integer $iblockID - идентификатор инфоблока
     * 
     * @return mixed
     */
    public static function OnAfterIBlockElementSetPropertyValuesEx($elementID, $iblockID)
    {
        if (!self::$noErrors) return;

        return self::sendByIBlockId($iblockID, __METHOD__, func_get_args());
    }
}