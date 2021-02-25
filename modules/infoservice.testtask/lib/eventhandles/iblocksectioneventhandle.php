<?
namespace Infoservice\TestTask\EventHandles;

abstract class IBlockSectionEventHandle extends IBlockElementEventHandle
{
    /**
     * Обработчик события ДО ДОБАВЛЕНИЯ раздела
     *
     * @param array& $sectionData - данные раздела
     * @return mixed
     */
    public static function OnBeforeIBlockSectionAdd(array&$sectionData)
    {
        self::$noErrors = self::sendByIBlockId($sectionData['IBLOCK_ID'], __METHOD__, [$sectionData]) !== false;
        return self::$noErrors;
    }

    /**
     * Обработчик события ПОСЛЕ ДОБАВЛЕНИЯ раздела
     * 
     * @param array $sectionData - данные раздела
     * @return mixed
     */
    public static function OnAfterIBlockSectionAdd(array $sectionData)
    {
        if (!self::$noErrors) return;

        return self::sendByIBlockId($sectionData['IBLOCK_ID'], __METHOD__, [$sectionData]);
    }
    /**
     * Обработчик события ДО ОБНОВЛЕНИЯ раздела
     * 
     * @param array& $sectionData - данные раздела
     * @return mixed
     */
    public static function OnBeforeIBlockSectionUpdate(array&$sectionData)
    {
        self::$noErrors = self::sendByIBlockId($sectionData['IBLOCK_ID'], __METHOD__, [$sectionData]) !== false;
        return self::$noErrors;
    }

    /**
     * Обработчик события ПОСЛЕ ОБНОВЛЕНИЯ раздела
     * 
     * @param array $sectionData - данные раздела
     * @return mixed
     */
    public static function OnAfterIBlockSectionUpdate(array $sectionData)
    {
        if (!self::$noErrors) return;

        return self::sendByIBlockId($sectionData['IBLOCK_ID'], __METHOD__, [$sectionData]);
    }

    /**
     * Обработчик события ДО УДАЛЕНИЯ раздела
     * 
     * @param int $sectionId - идентификатор раздела
     * @return mixed
     */
    public static function OnBeforeIBlockSectionDelete(int $sectionId)
    {
        if (!Employment::setBussy()) return;

        \Bitrix\Main\Loader::includeModule('iblock');

        $iblock = \CIBlockSection::GetById($sectionId)->Fetch();
        self::$noErrors = Employment::sendOtherHandle($iblock['IBLOCK_CODE'], __METHOD__, [$sectionId]) !== false;
        return self::$noErrors;
    }

    /**
     * Обработчик события ПОСЛЕ УДАЛЕНИЯ раздела
     * 
     * @param array $sectionData - данные раздела
     * @return mixed
     */
    public static function OnAfterIBlockSectionDelete(array $sectionData)
    {
        if (!self::$noErrors) return;

        return self::sendByIBlockId($sectionData['IBLOCK_ID'], __METHOD__, [$sectionData]);
    }
}