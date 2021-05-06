<?php
use Bitrix\Main\{
    Localization\Loc,
    Loader,
    Config\Option
};
use Infoservice\TestTask\EventHandles\Employment;

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
         * Настройки для создания типов календарей. Обязателен параметр LANG_CODE.
         * Для указания описания к типу, если оно нужно, надо использовать DESCRIPTION_LANG_CODE.
         * В параметре ACCESS можно указать доступы пользователей. В "ключе" указывается
         * код для пользовательской группы (G<ID>) или пользователя (U<ID>) или название
         * константы модуля, под значением которой находится либо идентификатор пользователькой группы,
         * либо она использовалась в настройках пользовательской группы, создаваемой
         * этим модулем. В "значении" указывается символьный ключ того, какой доступ
         * предоставить:
         *    D - доступ закрыт
         *    R - просмотр
         *    W - редактирование событий и календарей
         *    X - полный доступ
         */

         /**
          * Это тестовый календарь, для его проверки надо зайти на страницу <ДОМЕН>/local/modules/infoservice.testtask/install/public
          */
        'CalendarTypes' => [
            'INFS_CALENDAR_TYPE_TESTING' => [
                'LANG_CODE' => 'CALENDAR_TYPE_TESTING',
                'ACCESS' => ['G1' => 'X', 'G3' => 'R']
            ],
        ],

        /**
         * Настройки для создания секций типов календаря. Необходимые параметры
         *    LANG_CODE - назвние языковой константы с название секции
         *    TYPE - строковое название константы, которое указано в файле include.php
         *    у модуля. В константе должно быть указано символьное имя типа календаря,
         *    под которым в CalendarTypes прописаны настройки типа календаря, или в
         *    случае отсутствия этого описания там, то символьное имя уже существующего
         *    в системе типа календаря
         *
         * Дополнительно можно указать параметры:
         *    DESCRIPTION_LANG_CODE - название языковой коснтанты с описанием секции типа календаря
         *    ACCESS - доступ к секции конкретных пользователей или пользовательских групп. Указывать это параметр
         *      можно аналогично настройкам этого параметра в части для типов календаря CalendarTypes. Доступны
         *      следующие коды: D, O, P, R, W, X
         *      
         */

         /**
          * Это тестовые типы секций календаря, для их проверки надо зайти на страницу <ДОМЕН>/local/modules/infoservice.testtask/install/public
          */
        'CalendarTypeSections' => [
            'INFS_CALENDAR_SECTION_FIRST_TESTING' => [
                'LANG_CODE' => 'CALENDAR_SECTION_FIRST_TESTING',
                'TYPE' => 'INFS_CALENDAR_TYPE_TESTING'
            ],

            'INFS_CALENDAR_SECTION_SECOND_TESTING' => [
                'LANG_CODE' => 'CALENDAR_SECTION_SECOND_TESTING',
                'TYPE' => 'INFS_CALENDAR_TYPE_TESTING'
            ],
        ],
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
     * Подготавливает и возвращает массив доступов к каким-нибудь данным, используя символьный
     * код группы данных, к которой относятся массив с доступами.
     * Массив с доступами представляется из себя массив, где под "ключами" могут быть явно указаны коды с идентификаторами
     * конкретных пользовательских групп (G<ID>), пользователей (U<ID>) или имя какой-то константы модуля, в значении которой
     * могут храниться либо идентификатор конкретной пользовательской группы, либо она используется в настройках модуля для
     * создания пользовательской группы (UserGroup)
     * 
     * Возвращает правильное описание доступов для использования в дальнейшем при создании
     * 
     * @param string $code - символьный код группы, к которой относятся доступы
     * @param array $templates - массив с доступами
     * @return array
     */
    protected function getCalendarAccessByTemplate(string $code, array $templates)
    {
        static $accessType = null;
        static $accessCodes = null;
        if ($accessType != $code) {
            $caledarAccess = Bitrix\Main\TaskTable::GetList(['filter' => ['BINDING' => $accessType = $code]]);
            $accessCodes = [];
            while ($access = $caledarAccess->Fetch()) {
                $accessCodes[$access['LETTER']] = $access['ID'];
            }
        }

        $accessResult = [];
        foreach ($templates as $who => $accessCode) {
            if (empty($accessCodes[$accessCode]) || empty($who)) continue;

            if (is_string($who) && defined($who)) {
                if (empty($who = constant($who))) continue;

                $group = preg_match('/^\d+$/', strval($who)) ? $who : $this->optionClass::getUserGroup($who);
                if (empty($group)) continue;

                if (!is_array($group)) $group = [$group];

                foreach ($group as $groupId) {
                    $accessResult['G' . $groupId] = $accessCodes[$accessCode];
                }

            } else {
                $accessResult[$who] = $accessCodes[$accessCode];
            }
        }
        return $accessResult;
    }

    /**
     * Создание типа календаря
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return mixed
     */
    protected function initCalendarTypesOptions(string $constName, array $optionValue)
    {
        if (!Loader::includeModule('calendar')) return;
 
        $title = self::checkLangCode($optionValue['LANG_CODE'], 'CALENDAR_TYPE_UNIT', ['CALENDAR_TYPE' => $constName]);
        $data = [
            'NEW' => true,
            'arFields' => [
                'XML_ID' => constant($constName), 
                'NAME' => $title, 
                'DESCRIPTION' => $optionValue['DESCRIPTION_LANG_CODE']
                               ? Loc::getMessage($optionValue['DESCRIPTION_LANG_CODE'])
                               : '',
                'ACTIVE' => 'Y',
                'ACCESS' => $this->getCalendarAccessByTemplate('calendar_type', $optionValue['ACCESS'] ?? [])
            ]
        ];

        $calendar = \CCalendarType::Edit($data);
        if ($calendar === false)
            throw new \Exception(
                            Loc::getMessage(
                                'ERROR_CALENDAR_TYPE_UNIT_CREATING',
                                ['CALENDAR_TYPE' => $constName]
                            )
                        );
        return $calendar;
    }

    /**
     * Создание секций для типов календарей
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return mixed
     */
    protected function initCalendarTypeSectionsOptions(string $constName, array $optionValue)
    {
        if (!Loader::includeModule('calendar')) return;

        if (empty($optionValue['TYPE']))
            throw new Exception(Loc::getMessage('ERROR_CALENDAR_SECTION_EMPTY_TYPE_NAME', ['CALENDAR_SECTION' => $constName]));

        $title = self::checkLangCode($optionValue['LANG_CODE'], 'CALENDAR_SECTION_UNIT', ['CALENDAR_SECTION' => $constName]);
        $data = [
                'XML_ID' => constant($constName),
                'CAL_TYPE' => defined($optionValue['TYPE']) ? constant($optionValue['TYPE']) : $optionValue['TYPE'],
                'NAME' => $title, 
                'DESCRIPTION' => $optionValue['DESCRIPTION_LANG_CODE']
                               ? Loc::getMessage($optionValue['DESCRIPTION_LANG_CODE'])
                               : '',
                'ACTIVE' => 'Y',
                'ACCESS' => $this->getCalendarAccessByTemplate('calendar_section', $optionValue['ACCESS'] ?? []),
            ] +
            array_filter(
                $optionValue,
                function($key) {
                    return !in_array($key, ['LANG_CODE', 'DESCRIPTION_LANG_CODE', 'TYPE', 'ID']);
                }, ARRAY_FILTER_USE_KEY
            ) + [
                'OWNER_ID' => 0
            ];

        $calendarSectionId = \CCalendarSect::Edit(['arFields' => $data]);
        if (!$calendarSectionId)
            throw new Exception(Loc::getMessage('ERROR_CALENDAR_SECTION_UNIT_CREATING', ['CALENDAR_SECTION' => $constName]));
        return $calendarSectionId;
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
     * Удаление типов календарей
     * 
     * @param string $constName - название константы
     * @return integer
     */
    protected function removeCalendarTypesOptions(string $constName)
    {
        if (!Loader::includeModule('calendar')) return;

        $calendar = $this->optionClass::getCalendarTypes(constant($constName));
        if (!$calendar) return;

        \CCalendarType::Delete($calendar);
    }
    
    /**
     * Удаление секций для типов календарей
     * 
     * @param string $constName - название константы
     * @return integer
     */
    protected function removeCalendarTypeSectionsOptions(string $constName)
    {
        if (!Loader::includeModule('calendar')) return;

        $calendarSectionId = $this->optionClass::getCalendarTypeSections(constant($constName));
        if (!$calendarSectionId) return;

        \CCalendarSect::Delete($calendarSectionId);
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
