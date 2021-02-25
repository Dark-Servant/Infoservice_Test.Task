<?php
use Bitrix\Main\{
    Localization\Loc,
    Loader,
    EventManager,
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

    /**
     * Описание обработчиков событий. Под "ключом" указывается название другого модуля, события которого
     * нужно обрабатывать, в "значении" указывается массив с названиями классов этого модуля, которые
     * будут отвечать за обработку событий. Сам класс для обработки событий находится в папке lib модуля.
     * У названия класса не надо указывать пространство имен, кроме той части, что идет после
     * названий партнера и модуля. Для обработки конкретных событий эти классы должны иметь
     * статические и открытые методы с такими же названиями, что и события
     * 
     * Для создания обработчиков к конкретному highloadblock-у необходимо писать их названия
     * как <символьное имя highloadblock><название события>, например, для события OnAdd
     * у highloadblock с символьным именем Test такой обработчик должен называться TestOnAdd.
     * 
     * Ананлогично, как и для highloadblock-ов, обработчики событий пишутся и для всех ORM-классов
     * Битрикса, т.е. тех классов, чье имя преставляет шаблон <Приставка класса>Table, где
     * <Приставка класса> это обычно название таблицы в БД, и эту <Приставку класса> можно
     * использовать как приставку к названиям обработчиков событий для ORM-класса, например,
     * <Приставка класса>OnAdd
     */
    const EVENTS_HANDLES = [
        /**
         * Примеры регистрации обработчиков событий, соми классы надо искать в
         * папке lib/eventhandles
         */
        'socialnetwork' => ['EventHandles\\SocNetGroupHandle'],
        'main' => ['EventHandles\\UserEventHandle'],

        /**
         * Класс IBlockSectionEventHandle особенный, он берет на себя все события
         * как элементов инфоблока, так и разделов. Для создания класса с обработчиком
         * событий конкретного инфоблока, достаточно просто создать в папке lib/eventhandles
         * файл с классом, чье имя совпадает с символьным кодом инфоблока, чьи события
         * хочется обработать, добавить в этот класс один из public-методов, которые
         * обрабатывают события, из класса IBlockSectionEventHandle или IBlockElementEventHandle,
         * и решать задачу
         */
        'iblock' => ['EventHandles\\IBlockSectionEventHandle'],
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
     * Регистрация обработчиков событий
     * 
     * @return void
     */
    protected function initEventHandles()
    {
        $eventManager = EventManager::getInstance();
        $eventsHandles = [];
        foreach ($this->getModuleConstantValue('EVENTS_HANDLES') as $moduleName => $classNames) {
            foreach ($classNames as $className) {
                $classNameValue = $this->nameSpaceValue . '\\' . $className;
                if (!class_exists($classNameValue)) continue;

                $registerModuleName = $moduleName == 'highloadblock' ? '' : $moduleName;
                $reflectionClass = new ReflectionClass($classNameValue);
                foreach ($reflectionClass->getMethods() as $method) {
                    if (!$method->isPublic() || !$method->isStatic()) continue;

                    $eventName = $method->getName();
                    $eventsHandles[$moduleName][$eventName][] = $className;
                    $eventManager->registerEventHandler(
                        $registerModuleName, $eventName, $this->MODULE_ID, $classNameValue, $eventName
                    );
                }
            }
        }
        $this->optionClass::setEventsHandles($eventsHandles);
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
        $this->initEventHandles();
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
     * Удаление всех зарегистрированных модулем обработчиков событий
     * 
     * @return void
     */
    protected function removeEventHandles()
    {
        $eventManager = EventManager::getInstance();
        foreach ($this->optionClass::getEventsHandles() as $moduleName => $eventList) {
            foreach (array_keys($eventList) as $eventName) {
                foreach (
                    $eventManager->findEventHandlers(
                        strtoupper($moduleName),
                        strtoupper($eventName),
                        ['TO_MODULE_ID' => $this->MODULE_ID]
                    ) as $handle) {

                        $eventManager->unRegisterEventHandler(
                            $moduleName, $eventName, $this->MODULE_ID, $handle['TO_CLASS'], $handle['TO_METHOD']
                        );
                }
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
        $this->removeEventHandles();
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
