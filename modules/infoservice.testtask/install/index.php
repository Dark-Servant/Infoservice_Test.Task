<?php
use Bitrix\Main\{
    Localization\Loc,
    Loader,
    Config\Option
};
use Infoservice\TestTask\{
    EventHandles\Employment,
    Helpers\Modules\Lists\ListFieldList
};

class infoservice_testtask extends CModule
{
    public $MODULE_ID;
    public $MODULE_NAME;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_DESCRIPTION;

    protected $nameSpaceValue;
    protected $subLocTitle;
    protected $optionClass;
    protected $definedContants;

    protected static $defaultSiteID;

    const ADMIN_GROUP_ID = 1;
    const ALL_USER_GROUP_ID = 2;
    const SAVE_OPTIONS_WHEN_DELETED = true;
    
    /**
     * Опции, которые необходимо добавить в проект, сгруппированы по названиям, которые будут использоваться
     * в имени метода для их добавления. Опции описываются как ассоциативный массив, где "ключ" - центральная
     * часть имени метода, который будет вызван для добавления/удаления опций той группы, чье имя указано
     * в "ключе".
     * Для того, чтобы была инициализация опций в конкретной группе или их обработка перед удалением, необходимо
     * создать методы init<"Ключ">Options и remove<"Ключ">Options.
     * В каждой группе опций, которые так же оформлены, как ассоциативный массив,
     *      "ключ" - название константы, которая хранит название опции, эта константа должна быть объявлена в
     *      файле include.php у модуля, в этом "ключе" обычно описывается символьное имя элемента
     *      "значение" - настройки для инициализации каждого элемента из группы опций.
     * Итоговые данные элементов из групп опций после добавления будут сохранены в опциях модуля, каждый в
     * своей группе, для обращения к ним надо использовать класс Helpers\Options и методы по шаблону
     *     get<"Название группы опций">(<название конкретного элемента, необязательный параметр>)
     *
     * Если объявить в классе константу SAVE_OPTIONS_WHEN_DELETED со значением true, то все данные, добавленные
     * при установке модуля, при удалении модуля будут сохранены в системе и снова будут использоваться без
     * переустановки при новой установке модуля. Эта возможность автоматически унаследуется и для дочених модулей,
     * но эту константу можно переобъявить в дочерних модулях, изменив там необходимость сохранения данных
     * при удалении модуля
     * 
     * ВНИМАНИЕ. Не стоит в каждой группе объявлять настройки для более одного элемента группы под именем константы
     * в "ключе", пусть и со своим уникальным именем, но с тем же самым "значением" константы, иначе после установки
     * модуль просто потеряет все, кроме последнего, установленные данные по этому "значению", что может привести к
     * багу, а так же после удаления модуля в системе останется мусор, т.е. информация, которую модуль установил,
     * но не смог удалить при своем удалении, так как ничего о ней не знал. Настройки для каждого элемента той же
     * самой группы должны храниться под "ключом", который является именем константы, "значение" которой уникально для
     * этой группы данных. Для некоторых групп данных, например, свойств инфоблоков, полей списка предусмотрено
     * использование в "значении" префикса, отделенного точкой, сам префикс при установке элемента группы игнорируется,
     * а при хранении в опциях модуля позволяет избежать перезаписи информации установленного элемента группы информацией
     * о другом установленном элементе той же группы. Для элементов других групп нельзя использовать константы с тем же
     * самым "значением", но то же "значение" под любым именем константы в той же самой группе данных можно будет
     * использовать в следующем модуле. 
     */
    const OPTIONS = [
        /**
         * Настройки для создания типов инфоблока. В "значении" указываются параметры для создания типа инфоблока.
         * Обязательно нужен параметр LANG_CODE с именем языковой константы для названия
         */
        'IBlockTypes' => [],

        /**
         * Настройки для создания инфоблоков. В "значении" указываются параметры для создания инфоблоков. Обязательно
         * нужны параметры LANG_CODE с именем языковой константы для названия и IBLOCK_TYPE_ID с именем константы, в
         * которой хранится код типа инфоблока.
         * В параметре PERMISSIONS указываются права доступа к инфоблоку, где "ключ" - идентификатор пользовательской
         * группы, а "значение" - код права доступа. По-умолчанию, для администраторов  установлен "полный доступ",
         * а для всех пользователей - "чтение".
         * Права доступа:
         *     E - добавление элементов инфоблока в публичной части;
         *     S - просмотр элементов и разделов в административной части;
         *     T - добавление элементов инфоблока в административной части; 
         *     R - чтение; 
         *     U - редактирование через документооборот; 
         *     W - запись; 
         *     X - полный доступ (запись + назначение прав доступа на данный инфоблок).
         * Права доступа можно указывать и для групп, созданных модулем, просто указав в "ключе" строковый
         * идентификатор константы, используемый в UserGroup
         */
        'IBlocks' => [
            'INFS_TEST_TASK_IBLOCK' => [
                'LANG_CODE' => 'TEST_TASK_IBLOCK',
                'IBLOCK_TYPE_ID' => 'INFS_LIST_IBLOCK_TYPE',
                'BIZPROC' => 'Y',
            ]
        ],

        /**
         * Настройки для создания свойств инфоблоков. В "ключе" указывается название константы модуля, у которой в
         * "значении" указан символьным код свойства. В "значении" же указываются параметры для создания свойств инфоблоков.
         * Обязательно нужны параметры LANG_CODE с именем языковой константы для названия и IBLOCK_ID с именем константы,
         * которая использовалась в IBlocks как "ключ", под которым хранятся настройки инфоблока, или которая объявлена
         * в include.php с идентификатором уже существующего инфоблока.
         * Если тип свойства список (PROPERTY_TYPE = L), то в параметрах свойств можно указать параметр LIST_VALUES, в
         * значении которого указан массив, где каждый элемент содержит минимум один параметр с ключом LANG_CODE для
         * языковой константы, под которой хранится значение параметра, но название константы указывается не полностью,
         * а лишь ее последняя часть, что должна идти после префикса, который указан как название языковой константы в
         * LANG_CODE у самого свойства, т.е. название языковой константы в файлах с переводом для какого-то элемента
         * списка должно соответствовать шаблону
         *      <константа из LANG_CODE для свойства инфоблока><дополнительная часть языковой константы>
         * 
         * Если тип свойства связан с другим инфоблоком, то в параметрах используется параметр LINK_IBLOCK_ID, для значения
         * которого действуют те же правила, что и для параметра IBLOCK_ID
         * 
         * Как и в других группах, параметры каждого элемента группы, точнее свойства инфоблока указываются под "ключом",
         * который является константой из файла модуля include.php. Но тут возможна ошибка, так как могут быть использованы
         * разные инфоблоки, а группа настроек для свойств инфоблока у одного модуля одна, то может случиться ситуация, когда
         * были указаны настройки для свойств разных инфоблоков под разными названиями констант, но одинаковыми "значениями",
         * т.е. символьными именами свойств, из-за чего вся информация о всех добавленных свойствах инфоблока с этими символьными
         * именами, кроме последнего добавленного свойства, будет не сохранена, а, значит, модуль после удаления оставит мусор в
         * системе. Для решения такой проблемы в значении константы надо использовать префикс, например, для свойства STRING_PROPERTY
         * из инфоблока с символьным именем some_iblock можно указать some_iblock.STRING_PROPERTY, при установке будет использовано
         * именно STRING_PROPERTY
         */
        'IBlockProperties' => [
            'INFS_TT_IB_STR_PR' => [
                'IBLOCK_ID' => 'INFS_TEST_TASK_IBLOCK',
                'LANG_CODE' => 'TT_IB_STR_PR',
                'PROPERTY_TYPE' => 'S'
            ],

            'INFS_TT_IB_INT_PR' => [
                'IBLOCK_ID' => 'INFS_TEST_TASK_IBLOCK',
                'LANG_CODE' => 'TT_IB_INT_PR',
                'PROPERTY_TYPE' => 'N'
            ]
        ],
        
        /**
         * Настройки для создания полей для списков, которые в свою очередь являются инфоблоками, настройки каждого элемента этой
         * группы указываются под "ключом", который является именем константы, объявленной в файле include.php у модуля. Значение
         * этой константы должно указывать либо на параметры инфоблока, которые у того всегда, например, "название"  (NAME),
         * "детальное описание" (DETAIL_TEXT), либо на сивольные имена свойств инфоблока. Может вознукнуть ситуация, что потребуется
         * создать поле для одних и тех же параметров из разных инфоблоков, тогда в настройках модуля затрется информация о более ранее
         * созданном поле, чье значение в константе совпало со значением другой константы, используемой для другого поля. Чтобы
         * этого избежать, надо использовать префикс в значении константы, т.е. какой-то текст, отделенный точкой от настоящего имени
         * параметра инфоблока.
         * В "значениях" группы с настройками для полей списков указывается ассоциативный массив, где важным параметром является
         *      IBLOCK_ID - название константы модуля, в значении которой указан либо идентификатор существующего инфоблока, либо
         *      символьное имя, используемое в группе IBlocks для создания модулем своего инфоблока
         * А дополнительно можно указать
         *      SORT - значение сортировки поля, т.е. положение поля списка относительно других полей списка
         *      LANG_CODE - название языковой константы, в которой указано название поля. Если этой настройки нет, то за имя поля будет
         *      взято значение по-умолчанию, которое установлено в модуле lists или имя свойства инфоблока, если это найстройки
         *      для свойства инфоблока
         */
        'IBlockListFields' => [
            'INFS_TT_IB_LIST_NAME' => [
                'SORT' => 10,
                'IBLOCK_ID' => 'INFS_TEST_TASK_IBLOCK',
                'LANG_CODE' => 'TEST_TASK_LIST_NAME',
            ],

            'INFS_TT_IB_LIST_DETAIL_TEXT' => [
                'SORT' => 20,
                'IBLOCK_ID' => 'INFS_TEST_TASK_IBLOCK',
                'LANG_CODE' => 'TEST_TASK_LIST_DETAIL_TEXT',
            ],

            'INFS_TT_IB_LIST_DATE_CREATE' => [
                'SORT' => 30,
                'IBLOCK_ID' => 'INFS_TEST_TASK_IBLOCK',
                'LANG_CODE' => 'TEST_TASK_LIST_DATE_CREATE',
            ],

            'INFS_TT_IB_LIST_CREATED_BY' => [
                'SORT' => 40,
                'IBLOCK_ID' => 'INFS_TEST_TASK_IBLOCK',
                'LANG_CODE' => 'TEST_TASK_LIST_CREATED_BY',
            ],

            'INFS_TT_IB_STR_PR' => [
                'SORT' => 50,
                'IBLOCK_ID' => 'INFS_TEST_TASK_IBLOCK',
                'LANG_CODE' => 'TEST_TASK_LIST_STR_PR',
            ],

            'INFS_TT_IB_INT_PR' => [
                'SORT' => 60,
                'IBLOCK_ID' => 'INFS_TEST_TASK_IBLOCK',
                'LANG_CODE' => 'TEST_TASK_LIST_INT_PR',
            ]
        ]
    ];

    /**
     * Запоминает и возвращает настоящий путь к текущему классу
     * 
     * @return string
     */
    protected function getModuleClassPath()
    {
        if ($this->moduleClassPath) return $this->moduleClassPath;

        $this->moduleClass = new \ReflectionClass(get_called_class());
        // не надо заменять на __DIR__, так как могут быть дополнительные модули $this->moduleClassPath
        $this->moduleClassPath = rtrim(preg_replace('/[^\/\\\\]+$/', '', $this->moduleClass->getFileName()), '\//');
        return $this->moduleClassPath;
    }

    /**
     * Запоминает и возвращает код модуля, к которому относится текущий класс
     * 
     * @return string
     */
    protected function getModuleId()
    {
        if ($this->MODULE_ID) return $this->MODULE_ID;

        return $this->MODULE_ID = basename(dirname($this->getModuleClassPath()));
    }

    /**
     * Запоминает и возвращает название именного пространства для классов из
     * библиотеки модуля
     * 
     * @return string
     */
    protected function getNameSpaceValue()
    {
        if ($this->nameSpaceValue) return $this->nameSpaceValue;

        return $this->nameSpaceValue = preg_replace('/\.+/', '\\\\', ucwords($this->getModuleId(), '.'));
    }

    /**
     * Запоминает и возвращает название класса, используемого для установки и сохранения
     * опций текущего модуля
     * 
     * @return string
     */
    protected function getOptionsClass()
    {
        if ($this->optionClass) return $this->optionClass;

        return $this->optionClass = $this->getNameSpaceValue() . '\\Helpers\\Options';
    }

    /**
     * Запоминает и возвращает кода сайта по-умолчанию
     * 
     * @return string
     */
    protected static function getDefaultSiteID()
    {
        if (self::$defaultSiteID) return self::$defaultSiteID;

        return self::$defaultSiteID = CSite::GetDefSite();
    }

    /**
     * По переданному имени возвращает значение константы текущего класса с учетом того, что эта константа
     * точно была (пере)объявлена в этом классе модуля. Конечно, получить значение константы класса можно
     * и через <название класса>::<название константы>, но такая запись не учитывает для дочерних классов,
     * что константа не была переобъявлена, тогда она может хранить ненужные старые данные, из-за чего требуется
     * ее переобъявлять, иначе дочерние модули начнуть устанавливать то же, что и родительские, а переобъявление
     * требует дополнительного внимания к каждой константе и дополнительных строк в коде дочерних модулей
     * 
     * @param string $constName - название константы
     * @return array
     */
    protected function getModuleConstantValue(string $constName)
    {
        $constant = $this->moduleClass->getReflectionConstant($constName);
        if (
            ($constant === false)
            || ($constant->getDeclaringClass()->getName() != get_called_class())
        ) return [];

        return $constant->getValue();
    }

    function __construct()
    {
        $this->getOptionsClass();
        Loc::loadMessages($this->getModuleClassPath() . '/' . basename(__FILE__));

        $this->subLocTitle = strtoupper(get_called_class()) . '_';
        $this->MODULE_NAME = Loc::getMessage($this->subLocTitle . 'MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage($this->subLocTitle . 'MODULE_DESCRIPTION');

        include  $this->moduleClassPath . '/version.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
    }

    /**
     * Проверяет наличие языковой константы и ее значение
     * 
     * @param $langCode - название языковой константы
     * @param string $prefixErrorCode - префикс к языковым конcтантам для ошибок без указания ERROR_
     * в начале, но который должен быть у самой константы
     * 
     * @param array $errorParams - дополнительные параметры для ошибок
     * @return string
     */
    protected static function checkLangCode($langCode, string $prefixErrorCode, array $errorParams = [])
    {
        if (!isset($langCode))
            throw new Exception(Loc::getMessage('ERROR_' . $prefixErrorCode . '_LANG', $errorParams));
        
        $value = Loc::getMessage($langCode);
        if (empty($value))
            throw new Exception(
                Loc::getMessage('ERROR_' . $prefixErrorCode . '_EMPTY_LANG', $errorParams + [
                        'LANG_CODE' => $langCode
                    ])
            );
        return $value;
    }

    /**
     * У переданного значения параметра $name убирает префикс, т.е. текст в значении, идущий до последней
     * точки. Возвращает либо то же самое значение, что было передано, если префикса не окажется, либо то,
     * что стоит после префикса
     *
     * @param string $name - название параметра
     *
     * @return string
     */
    protected static function getNameWithoutPrefix(string $name)
    {
        return preg_match('/\w+\.(\S+)/', $name, $nameParts) ? $nameParts[1] : $name;
    }

    /**
     * Создание типа инфоблока
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return void
     * @throws
     */
    protected function initIBlockTypesOptions(string $constName, array $optionValue)
    {
        if (!Loader::includeModule('iblock')) return;

        $iblockTypeID = constant($constName);
        if (CIBlockType::GetList([], ['ID' => $iblockTypeID])->Fetch())
            return;

        $title = self::checkLangCode($optionValue['LANG_CODE'], 'IBLOCK_TYPE', ['TYPE' => $constName]);
        $data = ['ID' => $iblockTypeID, 'LANG' => ['RU' => ['NAME' => $title]]]
              + array_filter($optionValue, function($key) {
                    return !in_array($key, ['LANG_CODE']);
                }, ARRAY_FILTER_USE_KEY)
              + ['SECTIONS' => 'Y'];

        $list = new CIBlockType();
        if (!$list->Add($data))
            throw new Exception(
                Loc::getMessage('ERROR_IBLOCK_TYPE_CREATING', ['TYPE' => $constName])
                . PHP_EOL . $list->LAST_ERROR
            );
    }

    /**
     * По значению в параметре $value возвращает либо само значение, если оно имеет численный тип или состоит только
     * из цифр, либо идентификатор элемента какой-то группы из константы OPTIONS у модуля, название которой указано
     * в параметре $category
     *
     * @param $value - название константы модуля
     * @param string $category - название категории группы настроек, которая используется в константе OPTIONS
     *
     * @return mixed
     */
    protected function getCategoryIDByValue($value, string $category)
    {
        $methodName = 'get' . $category;
        if (
            empty($value)
            || (!is_integer($value) && !is_string($value))
            || (
                (is_integer($value) || preg_match('/^\d+$/', $value))
                && (($IDValue = intval($value)) < 1)
            )
            || (
                is_string($value)
                && empty($IDValue = $this->optionClass::$methodName($value))
            )
        ) return false;

        return is_array($IDValue) && isset($IDValue['ID']) ? $IDValue['ID'] : $IDValue;
    }

    /**
     * На основе прав доступа к конкретным группам пользователей, указанных в входном параметре $permissions,
     * создает и возвращает готовый массив с правами доступа и идентификаторами конкретных пользовательских
     * групп.
     * В параметре $permissions права досупа указываются так
     *      "ключ" - либо идентификатор существующей в системе группы, либо строковые значение с именем
     *      константы, значение которой хранит либо идентификатор, либо массив идентификаторов пользовательских
     *      групп, либо
     *      "значение" - код права доступа
     *
     * @param array $permissions - права доступа
     * @return array
     */
    protected function prepareGroupPermissions(array $permissions)
    {
        $resultPermissions = [];
        foreach ($permissions as $groupId => $accessValue) {
            if (is_integer($groupId)) {
                $resultPermissions[$groupId] = $accessValue;

            } elseif (
                is_string($groupId) && !empty($groupId)
                && defined($groupId) && !empty($groupId = constant($groupId))
             ) {
                if (!is_array($groupId)) $groupId = [$this->getCategoryIDByValue($groupId, 'UserGroup') ?: $groupId];

                foreach ($groupId as $gID) {
                    $resultPermissions[$gID] = $accessValue;
                }
            }
        }
        return $resultPermissions;
    }

    /**
     * Создание инфоблока
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return integer
     * @throws
     */
    protected function initIBlocksOptions(string $constName, array $optionValue)
    {
        // Инфоблок может создаваться в другом типе инфоблока
        if (!Loader::includeModule('iblock')) return;

        $title = self::checkLangCode($optionValue['LANG_CODE'], 'IBLOCK', ['#IBLOCK#' => $constName]);
        $data = [
                    'ACTIVE' => 'Y',
                    'NAME' => $title,
                    'CODE' => constant($constName),
                    'IBLOCK_TYPE_ID' => constant($optionValue['IBLOCK_TYPE_ID']),
                    /**
                     * VERSION определяет способ хранения значений свойств элементов инфоблока
                     *     1 - в общей таблице
                     *     2 - в отдельной
                     * Но выбрано строго 2, так ка при работе с множественными значениями свойств
                     * инфоблока могут быть проблемы из-за того, что при запросе элементов через
                     * GetList на каждое значение свойства будет дан столько же раз тот же элемент
                     */
                    'VERSION' => 2
                ]
              + array_filter($optionValue, function($key) {
                    return !in_array($key, ['LANG_CODE', 'PERMISSIONS']);
                }, ARRAY_FILTER_USE_KEY)
              + [
                    'DETAIL_PAGE_URL' => '',
                    'LIST_PAGE_URL' => '',
                    'WORKFLOW' => 'N',
                    'BIZPROC' => 'N',
                    'SITE_ID' => self::getDefaultSiteID()
                ];

        $iblock = new CIBlock;
        $iblockId = $iblock->Add($data);
        if (!$iblockId)
            throw new Exception(
                Loc::getMessage('ERROR_IBLOCK_CREATING', ['#IBLOCK#' => $constName])
                . PHP_EOL . $iblock->LAST_ERROR
            );

        CIBlock::SetPermission(
            $iblockId,
            self::prepareGroupPermissions(
                array_replace([self::ADMIN_GROUP_ID => 'X', self::ALL_USER_GROUP_ID => 'R'], $optionValue['PERMISSIONS'] ?: [])
            )
        );

        return $iblockId;
    }

    /**
     * Создание значений для свойства инфоблока типа "Список"
     * 
     * @param int $propertyId - ID свойства инфоблока
     * @param array $propertyValues - список значений
     * @param string $langCode - префикс к языковым константам для названий значений
     * @return array
     */
    protected function addIBlockPropertyListValues(int $propertyId, array $propertyValues, string $langCode)
    {
        $values = [];
        $ids = [];
        $list = new CIBlockPropertyEnum;
        foreach ($propertyValues as $unit) {
            $value = Loc::getMessage(($langCode ? $langCode . '_' : '') . $unit['LANG_CODE']);
            $lowerCaseValue = strtolower($value);
            if (empty($value) || in_array($lowerCaseValue, $values)) continue;

            $listUnitId = intval($list->Add(['PROPERTY_ID' => $propertyId, 'VALUE' => $value]));
            $ids['VALUES'][] = $listUnitId;
            $ids[$unit['LANG_CODE'] . '_ID'] = $listUnitId;
        }
        return $ids;
    }

    /**
     * Создание свойств инфоблока
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return integer
     * @throws
     */
    protected function initIBlockPropertiesOptions(string $constName, array $optionValue)
    {
        if (!Loader::includeModule('iblock')) return;

        $title = self::checkLangCode($optionValue['LANG_CODE'], 'IBLOCK_PROPERTY', ['#PROPERTY#' => $constName]);
        $iblockID = $this->getCategoryIDByValue(constant($optionValue['IBLOCK_ID']), 'IBlocks');
        if (!$iblockID) throw new Exception(Loc::getMessage('ERROR_BAD_PROPERTY_IBLOCK', ['#PROPERTY#' => $constName]));

        $data = [
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => $iblockID,
                    'NAME' => $title,
                    'CODE' => self::getNameWithoutPrefix(constant($constName))
                ]
              + array_filter($optionValue, function($key) {
                    return !in_array($key, ['LANG_CODE', 'LIST_VALUES']);
                }, ARRAY_FILTER_USE_KEY)
              + [
                    'IS_REQUIRED' => 'N',
                    'MULTIPLE' => 'N',
                    'MULTIPLE_CNT' => 5,
                    'LIST_TYPE' => 'L'
                ];

        if (array_key_exists('LINK_IBLOCK_ID', $data))
            $data['LINK_IBLOCK_ID'] = $this->getCategoryIDByValue(constant($data['LINK_IBLOCK_ID']), 'IBlocks');

        $property = new CIBlockProperty;
        $propertyId = $property->Add($data);
        if (!$propertyId)
            throw new Exception(
                Loc::getMessage('ERROR_IBLOCK_PROPERTY_CREATING', ['#PROPERTY#' => $constName])
                . PHP_EOL . $property->LAST_ERROR
            );

        $result = ['ID' => $propertyId];
        if (
            ($optionValue['PROPERTY_TYPE'] == 'L') && !$optionValue['USER_TYPE']
            && !empty($optionValue['LIST_VALUES'])
        ) $result += $this->addIBlockPropertyListValues(
                                    $result['ID'],
                                    $optionValue['LIST_VALUES'],
                                    $optionValue['LANG_CODE'] ?: ''
                                );
        return $result;
    }

    /**
     * Создания поля списка, в роли которого выступает инфоблок
     *
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     *
     * @return array
     * @throws
     */
    protected function initIBlockListFieldsOptions(string $constName, array $optionValue)
    {
        static $listFields = [];

        $fieldCode = self::getNameWithoutPrefix(trim(strval(constant($constName))));
        if (empty($fieldCode)) throw new Exception(Loc::getMessage('ERROR_LIST_FIELD_EMPTY_CODE', ['#CODE#' => $constName]));

        $iblockID = $this->getCategoryIDByValue(constant($optionValue['IBLOCK_ID']), 'IBlocks');
        if (!$iblockID) throw new Exception(Loc::getMessage('ERROR_BAD_LIST_FIELD_IBLOCK', ['#CODE#' => $constName]));

        $caption = isset($optionValue['LANG_CODE']) ? Loc::getMessage($optionValue['LANG_CODE']) : null;
        $sortValue = is_integer($optionValue['SORT']) && ($optionValue['SORT'] > 0) ? $optionValue['SORT'] : 10;

        if (empty($listFields[$iblockID])) $listFields[$iblockID] = new ListFieldList($iblockID);
        $result = $listFields[$iblockID]->setField($fieldCode, $caption, $sortValue);
        $listFields[$iblockID]->saveList();

        if (!$result) throw new Exception(Loc::getMessage('ERROR_BAD_LIST_IB_PROPERTY', ['#CODE#' => $constName]));

        return $result;
    }

    /**
     * Создание всех опций
     *
     * @return  void
     */
    protected function initOptions() 
    {
        $savedData = [];
        $saveDataWhenDeleted = constant(get_called_class() . '::SAVE_OPTIONS_WHEN_DELETED') === true;
        if ($saveDataWhenDeleted)
            $savedData = json_decode(Option::get('main', 'saved.' . $this->MODULE_ID, false, \CSite::GetDefSite()), true)
                       ?: [];

        foreach ($this->getModuleConstantValue('OPTIONS') as $methodNameBody => $optionList) {
            $methodName = 'init' . $methodNameBody . 'Options';
            if (!method_exists($this, $methodName)) continue;

            foreach ($optionList as $constName => $optionValue) {
                if (!defined($constName)) return;

                $constValue = constant($constName);
                $value = empty($savedData[$methodNameBody][$constValue])
                       ? $this->$methodName($constName, $optionValue)
                       : $savedData[$methodNameBody][$constValue];
                if (!isset($value)) continue;
                $optionMethod = 'add' . $methodNameBody;
                $this->optionClass::$optionMethod($constValue, $value);
            }
        }
    }

    /**
     * Подключает модуль и сохраняет созданные им константы
     * 
     * @return void
     */
    protected function initDefinedContants()
    {
        /**
         * array_keys нужен, так как в array_filter функция isset дает
         * лишнии результаты
         */
        $this->definedContants = array_keys(get_defined_constants());

        Loader::IncludeModule($this->MODULE_ID);
        $this->definedContants = array_filter(
            get_defined_constants(),
            function($key) {
                return !in_array($key, $this->definedContants);
            }, ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Выполняется основные операции по установке модуля
     * 
     * @return void
     */
    protected function runInstallMethods()
    {
        $this->initOptions();
    }

    /**
     * Проверяет у модуля наличие класса Employment в своем подпространстве имен EventHandles,
     * а так же наличие у него метода, название которого передано в параметре $methodName.
     * В случае успеха вызывает метод у своего Employment
     * 
     * @param string $methodName - название метода, который должен выступать как обработчик события
     * @return void
     */
    protected function checkAndRunModuleEvent(string $methodName)
    {
        $moduleEmployment = $this->nameSpaceValue . '\\EventHandles\\Employment';
        if (!class_exists($moduleEmployment) || !method_exists($moduleEmployment, $methodName))
            return;

        $moduleEmployment::$methodName();
    }

    /**
     * Функция, вызываемая при установке модуля
     *
     * @param bool $stopAfterInstall - указывает модулю остановить после
     * своей установки весь процесс установки
     * 
     * @return void
     */
    public function DoInstall(bool $stopAfterInstall = true) 
    {
        global $APPLICATION;
        RegisterModule($this->MODULE_ID);
        $this->initDefinedContants();

        try {
            if (!class_exists($this->optionClass))
                throw new Exception(Loc::getMessage('ERROR_NO_OPTION_CLASS', ['#CLASS#' => $this->optionClass]));
            Employment::setBussy();
            $this->checkAndRunModuleEvent('onBeforeModuleInstallationMethods');
            $this->runInstallMethods();
            $this->optionClass::setConstants(array_keys($this->definedContants));
            $this->optionClass::setInstallShortData([
                'INSTALL_DATE' => date('Y-m-d H:i:s'),
                'VERSION' => $this->MODULE_VERSION,
                'VERSION_DATE' => $this->MODULE_VERSION_DATE,
            ]);
            $this->optionClass::save();
            $this->checkAndRunModuleEvent('onAfterModuleInstallationMethods');
            Employment::setFree();
            if ($stopAfterInstall)
                $APPLICATION->IncludeAdminFile(
                    Loc::getMessage($this->subLocTitle . 'MODULE_WAS_INSTALLED'),
                    $this->moduleClassPath . '/step1.php'
                );

        } catch (Exception $error) {
            $this->removeAll();
            $_SESSION['MODULE_ERROR'] = $error->getMessage();
            Employment::setFree();
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage($this->subLocTitle . 'MODULE_NOT_INSTALLED'),
                $this->moduleClassPath . '/error.php'
            );
        }
    }

    /**
     * Удаление типа инфоблока
     * 
     * @param $constName - название константы
     * @return void
     */
    protected function removeIBlockTypesOptions(string $constName)
    {
        if (!Loader::includeModule('iblock')) return;

        $iblockTypeID = constant($constName);
        $iblocks = CIBlock::GetList([], ['CHECK_PERMISSIONS' => 'N']);
        while ($iblock = $iblocks->Fetch()) {
            if ($iblock['IBLOCK_TYPE_ID'] != $iblockTypeID) continue;

            CIBlock::Delete($iblock['ID']);
        }

        CIBlockType::Delete($iblockTypeID);
    }

    /**
     * Удаление инфоблока. Метод нужен, не смотря на то, что при удалении типа инфоблока
     * удаляются и все его инфоблоки, но модуль можно заставить создавать инфоблоки в
     * других типах инфоблоков
     * 
     * @param $constName - название константы
     * @return void
     */
    protected function removeIBlocksOptions(string $constName)
    {
        if (!Loader::includeModule('iblock')) return;

        $iblockCode = constant($constName);
        if (empty($iblockId = $this->optionClass::getIBlocks($iblockCode)) || !is_integer($iblockId))
            return;

        CIBlock::Delete($iblockId);
    }
    
    /**
     * Удаление свойств инфоблока
     * 
     * @param string $constName - название константы
     * @return void
     */
    protected function removeIBlockPropertiesOptions(string $constName)
    {
        if (
            !Loader::includeModule('iblock')
            || empty($iblockProperty = $this->optionClass::getIBlockProperties(constant($constName)))
        ) return;

        CIBlockProperty::Delete($iblockProperty['ID']);
    }

    /**
     * Удаление поля списка, роль которого выполняет инфоблок
     *
     * @param string $constName - название константы
     * @return void
     */
    protected function removeIBlockListFieldsOptions(string $constName)
    {
        static $listFields = [];

        $constantValue = constant($constName);
        $data = $this->optionClass::getIBlockListFields($constantValue);
        if (empty($listFields[$data['IBLOCK_ID']])) $listFields[$data['IBLOCK_ID']] = new ListFieldList($data['IBLOCK_ID']);

        $fieldCode = self::getNameWithoutPrefix($constantValue);

        // 
        if (!empty($data['NAME'])) $listFields[$data['IBLOCK_ID']]->setField($fieldCode, $data['NAME'], $data['SORT'] ?? 10);
        if (!$data['WAS_FIELD']) $listFields[$data['IBLOCK_ID']]->deactiveField($data['FIELD_ID']);

        $listFields[$data['IBLOCK_ID']]->saveList();
    }

    /**
     * Удаление всех созданных модулем данных согласно прописанным настройкам в
     * OPTIONS
     * 
     * @return void
     */
    protected function removeOptions() 
    {
        $saveDataWhenDeleted = constant(get_called_class() . '::SAVE_OPTIONS_WHEN_DELETED') === true;
        $savedData = [];
        foreach (array_reverse($this->getModuleConstantValue('OPTIONS')) as $methodNameBody => $optionList) {
            $methodName = $saveDataWhenDeleted && !in_array(strtolower($methodNameBody), ['agents'])
                        ? 'get' . $methodNameBody
                        : 'remove' . $methodNameBody . 'Options';

            foreach ($optionList as $constName => $optionValue) {
                if (!defined($constName)) continue;

                if ($saveDataWhenDeleted) {
                    $constValue = constant($constName);
                    $data = $this->optionClass::$methodName($constValue);
                    if (empty($data)) continue;
                    $savedData[$methodNameBody][$constValue] = $data;

                } elseif (method_exists($this, $methodName)) {
                    $this->$methodName($constName, $optionValue);
                }
            }
        }
        if (!empty($savedData))
            Option::set('main', 'saved.' . $this->MODULE_ID, json_encode($savedData));
    }

    /**
     * Выполняется основные операции по удалению модуля
     * 
     * @return void
     */
    protected function runRemoveMethods()
    {
        $this->removeOptions();
    }

    /**
     * Основной метод, очищающий систему от данных, созданных им
     * при установке
     * 
     * @return void
     */
    protected function removeAll()
    {
        if (class_exists($this->optionClass)) $this->runRemoveMethods();
        UnRegisterModule($this->MODULE_ID); // удаляем модуль
    }

    /**
     * Функция, вызываемая при удалении модуля
     *
     * @param bool $stopAfterDeath - указывает модулю остановить после
     * своего удаления весь процесс удаления
     * 
     * @return void
     */
    public function DoUninstall(bool $stopAfterDeath = true) 
    {
        global $APPLICATION;
        Loader::IncludeModule($this->MODULE_ID);
        Employment::setBussy();
        $this->checkAndRunModuleEvent('onBeforeModuleRemovingMethods');
        $this->definedContants = array_fill_keys($this->optionClass::getConstants() ?? [], '');
        array_walk($this->definedContants, function(&$value, $key) { $value = constant($key); });
        $this->removeAll();
        Option::delete($this->MODULE_ID);
        $this->checkAndRunModuleEvent('onAfterModuleRemovingMethods');
        Employment::setFree();
        if ($stopAfterDeath)
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage($this->subLocTitle . 'MODULE_WAS_DELETED'),
                $this->moduleClassPath . '/unstep1.php'
            );
    }

}
