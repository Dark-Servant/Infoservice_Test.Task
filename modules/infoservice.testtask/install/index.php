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

    // Правила обработки адресов
    const GROUP_ADDR_RULE = [
        /**
         * ЧПУ для новых пунктов меню группы,
         *  "ключ" - регулярное выражение,
         *  "значение" - массив с параметрами
         *      FILE - путь к файлу
         *      PARAMS - параметры запроса
         * Для "ключей" "FILE" и "PARAMS" в "значениях" можно вставлять значения констант модуля, указывая их имена, выделенные
         * кваратными скобками
         * [infs_..._module_id] - пример, как надо использовать константы (многоточие это какое-то специальное слово модуля),
         * Так же по-умолчанию доступно [module_id], которое заменяется на идентификатор модуля
         * 
         * Сами настройки правил в самом Битриксе надо искать по адресу /bitrix/admin/urlrewrite_list.php
         */
        '#^/test/first/(\d+)\/second\/(\d+)/?(?:\?(\S*))?$#' => [
            'FILE' => '/local/modules/[module_id]/install/test/index.php',
            'PARAMS' => 'first=$1&second=$2&$3'
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
     * Возвращает список констант модуля в виде массива, где
     *  "ключ" - название константы в нижнем регистре, спереди и в конце названия указаны символы, переданные
     *           в параметре $quotes, т.е., если в $quotes указан один символ, то он будет с обоих сторон названия
     *           "ключа", если символов в $quotes больше, то спереди будет первая половина, в конце вторая половина
     *           этих символов. Например, при значении $quotes равном "{{}}" название некой константы будет представлено
     *           в "ключе" как "{{некая_константа}}", для значения "1234" будет результат "12некая_константа34" и т.д.
     *  "значение" - значение конкретной константы
     * 
     * Метод полезен там, где требуется заменить в каком-то значение название какой-то конкретной константы на ее значение.
     * По-умолчанию, к списку указанных в модуле констант добавляется и параметр с "ключом", равным "module_id", и "значением",
     * равным символьному ID модуля
     * 
     * @param string $quotes - символы для выделения названия каждой константы модуля, по-умолчанию принимает значение "[]",
     * оно же берется, если передано пустое значение
     * 
     * @return array
     */
    protected function getPreparedContantsForReplacing(string $quotes = '[]')
    {
        if (empty($quotes)) $quotes = '[]';

        $startSym = $endSym = $quotes;
        if ($quoteCenter = strlen($quotes) >> 1) {
            $startSym = substr($quotes, 0, $quoteCenter);
            $endSym = substr($quotes, $quoteCenter);
        }
        $resultDefinedContants = [];
        foreach ($this->definedContants as $code => $value) {
            if (!preg_match('/^\w+$/', $code)) continue;

            $resultDefinedContants[$startSym . strtolower($code) . $endSym] = $value;
        }
        return [$startSym . 'module_id' . $endSym => basename(dirname($this->moduleClassPath))] + $resultDefinedContants;
    }

    /**
     * Добавление правил обработки адресов
     *
     * @return  void
     */
    protected function addAddrRules() 
    {
        $preparedContants = $this->getPreparedContantsForReplacing();
        $addrRules = [];
        foreach ($this->getModuleConstantValue('GROUP_ADDR_RULE') as $rule => $data) {
            $filter = ['SITE_ID' => self::getDefaultSiteID(), 'CONDITION' => $rule];

            array_walk($data, function(&$value) use($preparedContants) {
                $value = strtr($value, $preparedContants);
            });
            $addrRules[$rule]['data'] = $data;
            $fields = ['ID' => '', 'PATH' => $data['FILE'], 'RULE' => $data['PARAMS'], 'SORT' => 100];
            $oldRule = current(CUrlRewriter::GetList($filter, ['SORT' => 'ASC']));

            if (empty($oldRule)) {
                CUrlRewriter::Add($filter + $fields);

            } else {
                $addrRules[$rule]['old'] = [
                    'FILE' => $oldRule['PATH'],
                    'PARAMS' => $oldRule['RULE'],
                ];
                CUrlRewriter::Update($filter, $fields);
            }
        }
        $this->optionClass::setAddrRules($addrRules);
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
        $this->addAddrRules();
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
     * Удаление правил обработки адресов
     * 
     * @return void
     */
    protected function removeAddrRules() 
    {
        foreach ($this->optionClass::getAddrRules() as $rule => $data) {
            if (empty($data['old'])) {
                CUrlRewriter::Delete(['SITE_ID' => self::getDefaultSiteID(), 'CONDITION' => $rule]);

            } else {
                CUrlRewriter::Update([
                        'SITE_ID' => self::getDefaultSiteID(),
                        'CONDITION' => $rule
                    ], [
                        'ID' => '',
                        'PATH' => $data['old']['FILE'],
                        'RULE' => $data['old']['PARAMS'],
                        'SORT' => 100
                    ]
                );
            }
        }
    }

    /**
     * Выполняется основные операции по удалению модуля
     * 
     * @return void
     */
    protected function runRemoveMethods()
    {
        $this->removeAddrRules();
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
