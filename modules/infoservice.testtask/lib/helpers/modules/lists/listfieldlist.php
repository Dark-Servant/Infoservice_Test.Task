<?
namespace Infoservice\TestTask\Helpers\Modules\Lists;

use \Bitrix\Main\Loader;

Loader::includeModule('lists');
Loader::includeModule('iblock');

/**
 * Класс для работы с полями списков, под которыми выступают инфоблоки.
 * Класс наследуется от класса из модуля CListFieldList, где реализована логира работы с полями списков,
 * причем ее реализация подразумевает указание как полей списка либо тех параметров инфоблока, которые
 * у инфоблока всегда (имя, описание, дата создания и т.д.), либо указание как полей списка свойств
 * инфоблока при условии, что они будут так же и созданы, что вызовет ошибку, если создаваемое свойство
 * в инфоблоке для поля списка имеет символьное имя, совпадающее с символьным именем уже существующих у
 * инфоблока свойств
 * 
 * Для реализации возможности указывать как поля списка свойства инфоблока, которые у инфоблока уже есть,
 * была изучена логика создания полей списка, и было выяснено, что для указания поля списка надо информацию
 * о списке сохранять в таблице b_lists_field, с чем отлично справляются классы
 *      CListElementField - для всегда имеющихся у инфоблока параметров 
 *      CListPropertyField - для свойств инфоблока
 * которые уже при своей инициализиции добавляют нужную информацию в таблицу b_lists_field.
 * Так же для сохранения информации о указанных поля списка необходимо вызвать скрытые для внешнего пользования
 * методы класса CListFieldList, которые сохраняют все указанные поля у списка в параметры пользователя
 * Если ограничиться только классами CListElementField и CListPropertyField, то при инициализации экземпляра
 * класса CListFieldList из таблицы b_lists_field будет прочитана вся информация о полях списка, затем проверено
 * наличие информации обо всех прочитанных полях в настройках пользователя, и все, что там не сохранено, будет
 * удалено из таблицы b_lists_field
 */
class ListFieldList extends \CListFieldList
{
    /**
     * Возвращает информацию обо всех свойствах инфоблока, чей идентификатор был
     * перевадан во входном параметре
     *
     * @param $id - идентификатор инфоблока
     * @return array
     */
    protected static function getIBlockProperties($id)
    {
        static $data = [];
        if (empty($data[$id])) {
            $data[$id] = [];
            $properties = \CIBlock::GetProperties($id);
            while ($property = $properties->Fetch()) {
                $data[$id][$property['CODE']] = $property;
            }
        }

        return $data[$id];
    }

    /**
     * Возвращает о параметре инфоблока краткую старую информацию о том, был ли он ранее уже
     * полем списка и какие параметры у него тогда были указаны. Как минимум в информацию
     * войдет
     *      WAS_FIELD - указывает на то, был ли параметр ранее использован как поле списка
     * а так же, если WAS_FIELD буде истинным
     *      NAME - старое название поля
     *      SORT - старое значение сортировки поля
     *
     * @param string $code - имя параметра инфоблока
     * @return array
     */
    protected function getOldData(string $code)
    {
        $result = ['WAS_FIELD' => isset($this->fields[$code])];
        if ($result['WAS_FIELD'])
            $result += [
                    'NAME' => $this->fields[$code]->GetLabel(),
                    'SORT' => $this->fields[$code]->GetSort()
                ];

        return $result;
    }

    /**
     * Указывает какое из его существующих параметров инфоблока использовать как поле для списка
     * Без вызова потом метода saveList работа по указанию полей будет отменена
     * 
     * @param string $code - имя параметра инфоблока
     * @param string $caption - значение названия поля
     * @param int $sortValue - значение сортировки поля
     *
     * @return array
     */
    public function setField(string $code, string $caption = null, int $sortValue = 10)
    {
        $result = ['IBLOCK_ID' => $this->iblock_id];
        $fieldName = \CListFieldTypeList::GetTypesNames()[$code];
      
        if ($fieldName) {
            $result += $this->getOldData($code);
            if (empty($caption)) $caption = $fieldName;

            $field = new \CListElementField($this->iblock_id, $code, $caption, $sortValue);

        } else {
            $field = self::getIBlockProperties($this->iblock_id)[$code];
            if (!$field) return false;
            
            $code = 'PROPERTY_' . $field['ID'];
            $result += $this->getOldData($code) + ['NAME' => $field['NAME'], 'IS_PROPERTY' => true];
            if (empty($caption)) $caption = $field['NAME'];
            
            $field = new \CListPropertyField($this->iblock_id, $code, $caption, $sortValue);
            $field->Update(['NAME' => $caption, 'SORT' => $sortValue]);
        }

        $this->fields[$code] = $field;
        return ['FIELD_ID' => $code] + $result;
    }

    /**
     * Сохраняет в параметрах пользователя все указанные поля списка
     *
     * @return void
     */
    public function saveList()
    {
        $this->_resort();
        $this->_save_form_settings($this->form_id);
    }
    
    /**
     * Убирает из списка конкретное поле. Возвращает true, если переданное символьное имя поля
     * было среди имен полей списка. Чтобы изменение оканчательно применилось, потом надо вызывать
     * метод saveList
     *
     * @param string $readyCode - имя параметра инфоблока
     * @return boolean
     */
    public function deactiveField(string $readyCode = null)
    {
        if ($readyCode && isset($this->fields[$readyCode])) {
            unset($this->fields[$readyCode]);
            return true;
        }

        return false;
    }
};