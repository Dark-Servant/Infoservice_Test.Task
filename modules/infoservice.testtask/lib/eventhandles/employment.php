<?
namespace Infoservice\TestTask\EventHandles;

abstract class Employment
{
    private static $bussyStatus = false;
    private static $partnerEventHandleSpaces = null;

    /**
     * Устанавливает занятость для всех обработчиков событий
     *
     * @return boolean
     */
    public static function setBussy(string $methodName = '')
    {
        if (self::$bussyStatus) return false;

        return self::$bussyStatus = true;
    }

    /**
     * Снимает занятость для всех обработчиков событий
     *
     * @return boolean
     */
    public static function setFree()
    {
        $oldFree = self::$bussyStatus;
        self::$bussyStatus = false;

        return !$oldFree;
    }

    /**
     * Получает список всех модулей, созданных тем же партнером, что и текущий модуль. Модули,
     * если они были установлены в системе будут сохранены в статической переменной, затем возвращено
     * ее значение. Результат будет представлен таким образов
     *
     *      "<название модуля>" => "<Пространство имен, начинающееся с названия модуля и до EventHandles>"
     *
     * При повторном вызове в той же сессии будет просто возвращаться уже полученный результат
     * 
     * @return array
     */
    public static function getPartnerEventHandleSpaces()
    {
        if (self::$partnerEventHandleSpaces) return self::$partnerEventHandleSpaces;

        self::$partnerEventHandleSpaces = [];
        $installedModules = [];
        $modules = \CModule::GetList();
        while ($mdl = $modules->Fetch()) {
            $installedModules[] = current($mdl);
        }

        $nameSpacePath = explode('\\', __NAMESPACE__);
        $moduleName = array_splice($nameSpacePath, 0, 2);
        $allModulePath = realpath(__DIR__ . '/' . implode('/', array_fill(0, count($nameSpacePath), '..')) . '/../../');
        $nameSpacePath = implode('\\', array_map(function($pathPart) { return ucwords($pathPart); }, $nameSpacePath));

        foreach (glob($allModulePath . '/' . strtolower($moduleName[0]) . '.*') as $mdlFolder) { 
            $name = basename($mdlFolder);
            if (!in_array($name, $installedModules)) continue;

            self::$partnerEventHandleSpaces[$name] = preg_replace_callback(
                                                         '/(?:^|[^a-z\d])(\w)/',
                                                         function($word) {
                                                             return '\\' . strtoupper($word[1]);
                                                         }, 
                                                         $name
                                                     )
                                                   . '\\' . $nameSpacePath;
        }
        return self::$partnerEventHandleSpaces;
    }

    /**
     * Вызывает обработчики из других классов, которые могут быть в этом же именном пространстве
     * или таком же подпространстве имен другого модуля, который был создан тем же партнером
     * 
     * @param $symCode - символьный код, состоящий из цифр и букв латинского алфавита. Если
     * имеются другие символы, то, когда встречается последовательность таких символов, то в случае
     * ее длины не больше одного она убирается, иначе заменяется на символ \ - так можно указать путь
     * к классу, размещенному внутри подпаки по текущему именному пространству, где и текущий класс
     * Employment. Все символы латинского алфавита и цифр, если идут в начале или сразу после иных
     * символов переводятся в верхний регистр, тем самым превращая значение $symCode в camelCase-формат.
     * Итоговый результат после обработки $symCode должен ввести к конкретному файлу, где описан класс
     * с именем, что получилось после обработки $symCode
     * 
     * @param string $methodName - название метода. Может быть указано с именем класса и пространством имен,
     * будет выделено только имя метода. Будет проверяться на существование в классе, имя которого совпадает
     * с результатом обработки $symCode
     * 
     * @param array $parameters - массив параметров, будут переданны методу по пути
     * <результат из $symCode>::<метод, на который указывает $methodName>
     * 
     * @return mixed
     */
    public static function sendOtherHandle(
            $symCode, string $methodName,
            array $parameters
        )
    {
        if (!is_string($symCode)) return;
        $result = null;
        $className = preg_replace_callback(
                        '/(^|[^a-z\d]+)(\w)/',
                        function($word) {
                            return (strlen($word[1]) > 1 ? '\\' : '') . strtoupper($word[2]);
                        }, 
                        $symCode
                    );
        $methodName = preg_replace('/^[^:]+::/', '', $methodName);
        foreach (self::getPartnerEventHandleSpaces() as $module => $namespace) {
            Loader::includeModule($module);

            $classUnit = $namespace . '\\' . $className;
            if (class_exists($classUnit) && method_exists($classUnit, $methodName)) {
                $result = call_user_func_array($classUnit . '::' . $methodName, $parameters);
                break;
            }
        }

        self::setFree();
        return $result;
    }
}