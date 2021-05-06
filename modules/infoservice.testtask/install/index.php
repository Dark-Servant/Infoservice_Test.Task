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

    /**
     * Пути к файлам и папкам, что лежат в папке install модуля,  на которые необходимо создать символьные ссылки
     * относительно папки local. Игнорируются файлы из папки www. Символная ссылка будет созданна на последнюю часть
     * указанного пути, по остальным частям будут созданны папки, если их нет. При удалении модуля сивмольная ссылка
     * удалится, а затем и все папки, в которые она входит, если в них больше ничего нет, и чьи названия указаны тут.
     * Если при установке выяснится, что символьная ссылка на последнюю часть пути уже существует, или на ее месте
     * находится папа, или одна из непоследних частей пути не является папкой, то произойдет ошибка
     * В ссылках можно использовать добавление подпути в виде имени одной из констант модуля, выделенной кваратными
     * скобками, это будет работать при установке файла в систему, может потребоваться, если нужно выделить файлы
     * модуля
     * [infs_..._module_id] - пример, как надо использовать константы (многоточие это какое-то специальное слово модуля),
     * Так же по-умолчанию доступно [module_id], которое заменяется на идентификатор модуля
     */
    const FILE_LINKS = [
        'components/[module_id]/test.task',
        'public',
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
     * Функция-генератор, по списку переданных файлов делает предобработку названия каждого файла
     * и возвращает  обработанное название файла, рарзделенный на части путь к файлу и его длину.
     * Благодаря второму параметру exclude, в котором указываются пути для исключений, можно отбросить
     * все переданные в списке файлы, путь к которым введен в эти пути для исключения
     * 
     * @param array $files - список файлов
     * @param array $exclude - пути для исключения файлов
     * @param array $definedContants - массив с константами, которые надо заменить в именах списка файлов.
     * Сами константы в файлах должны быть указаны как
     * [<имя константы только из букв латинского алфавита, подчеркивания и цифр>]
     * По-умолчанию, обрабатывается и константа [module_id] с заменой на идентификатор модуля
     */
    protected function getFileParts(array $files, array $exclude = [], array $definedContants = [])
    {
        $resultDefinedContants = $this->getPreparedContantsForReplacing();
        $excludeFiles = array_map(
            function($eFile) use($resultDefinedContants) {
                $parts = preg_split('/[\\\\\/]+/', strtr(strtolower(trim($eFile , '\\/')), $resultDefinedContants));
                return ['count' => count($parts), 'parts' => $parts, 'path' => implode('/', $parts)];
            }, $exclude
        );
        $categories = [];
        $fileList = [];
        foreach ($files as $fileCategory => $categoryFiles) {
            $categoryData = is_array($categoryFiles) ? $categoryFiles : [$categoryFiles];
            $fileList = array_merge($fileList, $categoryData);
            if (is_string($fileCategory)) {
                $fileCategoryName = strtolower($fileCategory);
                $categories[$fileCategoryName] = array_merge($categories[$fileCategoryName] ?? [], $categoryData);
            }
        }
        $categoryCodes = array_keys($categories);

        foreach (array_unique($fileList) as $moduleFile) {
            $fileTarget =  strtolower(preg_replace('/[\\\\\/]+/', '/', trim($moduleFile , '\\/')));
            $resultFileTarget = strtr($fileTarget, $resultDefinedContants);
            $fileParts = explode('/', $resultFileTarget);
            $filePartsSize = count($fileParts);
            if (
                count(array_filter(
                    $excludeFiles,
                    function($ePath) use($resultFileTarget, $fileParts, $filePartsSize) {
                        if ($ePath['count'] <= $filePartsSize) {
                            return implode('/', array_slice($fileParts, 0, $ePath['count'])) == $ePath['path'];

                        } else {
                            return $resultFileTarget == implode('/', array_slice($ePath['parts'], 0, $filePartsSize));
                        }
                    }
                ))
            ) continue;
            yield [
                'target' => preg_replace('/\/+/', '/', preg_replace('/\[\w+\]/', '', $fileTarget)),
                'parts' => $fileParts,
                'count' => $filePartsSize,
                'categories' => array_filter(
                                    $categoryCodes,
                                    function($category) use($categories, $moduleFile) {
                                        return in_array($moduleFile, $categories[$category]);
                                    }
                                )
            ];
        }
    }

    /**
     * Создание символьных ссылок в папке local
     * 
     * @return void
     */
    protected function initFileLinks()
    {
        $localLinks = [];
        $fromPath = $this->moduleClassPath . '/';
        foreach ($this->getFileParts($this->getModuleConstantValue('FILE_LINKS'), ['www'], $this->definedContants) as $moduleFile) {
            $targetFromPath = $fromPath . $moduleFile['target'];
            if (!file_exists($targetFromPath)) continue;

            $lastPartNum = $moduleFile['count'] - 1;
            $subResult = '';
            foreach ($moduleFile['parts'] as $pathNum => $subPath) {
                $subResult .= '/' . $subPath;
                $result = $_SERVER['DOCUMENT_ROOT'] . '/local' . $subResult;
                if (!file_exists($result)) {
                    if ($lastPartNum > $pathNum) {
                        mkdir($result);

                    } else {
                        symlink($targetFromPath, $result);
                        $localLinks[$moduleFile['target']] = ['result' => $subResult];
                    }

                } elseif (!is_dir($result) || is_link($result) || ($lastPartNum == $pathNum)) {
                    $this->optionClass::setLocalLinks($localLinks);
                    throw new Exception(Loc::getMessage('ERROR_LINK_CREATING', ['LINK' => $moduleFile['target']]));
                }
            }
        }
        $this->optionClass::setLocalLinks($localLinks);
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
        $this->initFileLinks();
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
     * Удаляет файл, а затем папку, в которой он лежит, если в ней больше ничего нет,
     * после чего по такому же принципу удаляет все родительские папки до папки local
     * 
     * @param string $fileTarget - относительный путь к файлу
     * @param string $where - начальный путь к файлу
     * @return void
     */
    protected static function deleteEmptyPath(string $fileTarget, string $where)
    {
        $result = $where . $fileTarget;
        if (is_link($result) || !is_dir($result)) {
            @unlink($result) || rmdir($result);

        } else {
            rmdir($result);
        }

        $toDelete = true;
        while ($toDelete && ($fileTarget = preg_replace('/\/?[^\/]+$/', '', $fileTarget))) {
            $result = $where . $fileTarget;
            $dUnit = opendir($result);
            while ($fUnit = readdir($dUnit)) {
                if (($fUnit == '.') || ($fUnit == '..')) continue;

                $toDelete = false;
                break;
            }
            closedir($dUnit);
            if ($toDelete) rmdir($result);
        }
    }

    /**
     * Удаляет файлы, которые были созданы модулем как символьная ссылка на такой же файл в модуле.
     * Вызывает callback-функцию, если она была передана, с обработанным названием файла
     * 
     * @param array $files - список файлов из папки модуля с установочным файлом index.php
     * @param string $from - относительный путь к подпапке из папки модуля с установочным файлом index.php, где
     * должны лежать указанные в $files файлы
     * @param string $where - путь относительно корня сайта, где будут проверяться и удаляться файлы
     * @param array $definedContants - массив с константами, которые надо заменить в именах списка файлов.
     * Сами константы в файлах должны быть указаны как
     * [<имя константы только из букв латинского алфавита, подчеркивания и цифр>]
     * По-умолчанию, обрабатывается и константа [module_id] с заменой на идентификатор модуля
     * 
     * @param $callback - необязательный обработчик для каждого файла модуля. Передаются, если будет указан,
     * два параметра - имя файла из модуля и параметры установленного файла в виде массива, где
     *     <result> - имя файла, который был установлен в системе
     *     <old> - имя файла, которые было ранее до установленного, а теперь переименовано
     * @return void
     */
    protected function removeFiles(array $files, string $from, string $where, array $definedContants, $callback = null)
    {
        $fromPath = $this->moduleClassPath  . (trim($from) ? '/' : '') . trim($from) . '/';
        $wherePath = $_SERVER['DOCUMENT_ROOT'] . (trim($where) ? '/' : '') . trim($where) . '/';
        foreach ($files as $moduleFile => $moduleResult) {
            if (file_exists($fromPath . $moduleFile) && is_link($wherePath . $moduleResult['result']))
                self::deleteEmptyPath($moduleResult['result'], $wherePath);

            if (is_callable($callback)) $callback($moduleFile, $moduleResult);
        }
    }

    /**
     * Удаление всех созданных модулем символьных ссылок
     * 
     * @return void
     */
    protected function removeFileLinks()
    {
        $this->removeFiles($this->optionClass::getLocalLinks() ?? [], '', 'local', $this->definedContants ?? []);
    }

    /**
     * Выполняется основные операции по удалению модуля
     * 
     * @return void
     */
    protected function runRemoveMethods()
    {
        $this->removeFileLinks();
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
