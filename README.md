В репозитории **код** для **главного модуля** разбит по **веткам** (указаны основные **ветки**, некоторые **ветки** указаны как вложенные, так как несут не только информацию вышестоящей **ветки**, но и еще дополнительный **код**)

* master - код основного модуля, папки `php_interface`
* childmodule - код для дочерних модулей
* eventhandles - код для регистрации обработчиков событий 
* files - обработчик шаблонов путей к файлам/папкам внутри модулей и код по удалению папок,
  * files+local - создает **символьные ссылки** внутри папки **local**, подойдет для **шаблонов компонентов**, **компонентов**, **действий БД**
  * files+www - добавляет в модули константу **WWW_FILES**
* adddata
  * adddata+agents
  * adddata+calendar
  * adddata+iblock
  * adddata+iblock+lists
  * adddata+socnetsubject
  * adddata+userfields
  * adddata+userfields+highloadblock
  * adddata+userfields+iblocksection
  * adddata+userfields+task
  * adddata+usergroups
* urlrules



#### Первые шаги подготовки проекта

1. Создается репозиторий, обычно, он находится по пути `<Группа проектов для конкретного клиента>/BX24`
2. На тестовом портале под пользователем **bitrix** выполняем следующие **команды**
  ```bash
  cd www/local
  git clone https://.... .infoservice
  cd .infoservice/
  git remote add infsmdl https://git.infoservice.ru/bx-project-starting/module-creating.git
  git fetch infsmdl
  ```

#### Создание главного модуля

1. Берём начальные **файлы** для **главного модуля**
  ```bash
  git checkout remotes/infsmdl/master modules php_interface .gitignore
  ```
2. Переименовываем **модуль** `modules/infoservice.testtask` на `modules/infoservice.<код клиента>`
3. В **файле** `modules/infoservice.<код клиента>/include.php` меняем все костанты `INFS_TESTTASK_...` на `INFS_<КОДКЛИЕНТА>_...`
4. В **файле** `modules/infoservice.<код клиента>/lang/ru/install/index.php`
  * поправляем **языковые константы** с `INFOSERVICE_TESTTASK_...` на `INFOSERVICE_<КОДКЛИЕНТА>_...`
  * Изменяем значения **языковых констант**
    * `INFOSERVICE_<КОДКЛИЕНТА>_MODULE_NAME` - название **модуля**, обычно, если это первый **модуль**, а значит, главный, то название носит что-то **Клиент такой-то. Главный модуль**
    * `INFOSERVICE_<КОДКЛИЕНТА>_MODULE_DESCRIPTION`- описание **модуля**, обычное пустое
5. В **файлах**
  * `modules/infoservice.<код клиента>/lib/eventhandles/employment.php`
  * `modules/infoservice.<код клиента>/lib/helpers/options.php`

  Делаем 
  * меняем часть значения именного пространства (это `namespace`) c `Infoservice\TestTask` на `Infoservice\<КодКлиента>`
  * остальную часть каждого из этих **файла** обновляем из аналогичных **файлов** из **последних проектов**
6. В **файле** `modules/infoservice.<код клиента>/install/index.php`
  * заменяем название класса **infoservice_testtask** на **infoservice_<кодклиента>**
  * меняем часть значения именного пространства (это `use`) c `Infoservice\TestTask` на `Infoservice\<КодКлиента>`
7. Вносим дату окочания в **файл** `modules/infoservice.<код клиента>/install/version.php`
8. **Модуль** готов, осталось прокинуть на него путь в папку `www/local/modules`, для этого
  * переходим в папку `www/local`
  * создаем папки, если их нет, `modules` и `php_interface`
    ```bash
    mkdir modules
    mkdir php_interface
    ```
  * записываем в **файле** `www/local/php_interface/init.php` **код**
    ```php
    require_once __DIR__ . '/../.infoservice/php_interface/init.php';
    ```
  * выполняем **bash-код** (его можно запускать в папке `local` и после добавления новых **модулей**)
    ```bash
    for i in .infoservice/modules/*; do  j=${i:21}; if ! [[ -d modules/$j ]]; then echo ln -s `pwd`/$i modules; fi; done
    ```
    проверяем правильный ли вывод на экран инфомации по созданию новых символьных ссылок на новые **модули** и, если все нормально, то заменяем в **bash-коде** `echo ln` на `ln` и выполняем снова
9. Если на странице в браузере `https://.../bitrix/admin/partner_modules.php` появляется **модуль**, то все ок
10. Устанавливаем **модуль**. Если не произошло ошибок, то переходим по адресу `https://.../bitrix/admin/php_command_line.php` и выполняем **php-код**
  ```php
  $className = 'Infoservice\\<КодКлиента>\\Helpers\\Options';
  echo class_exists($className) ? print_r($className::getParams(), true) : 'NO';
  ```

   Должна вывестись краткая информация по **модулю** - версия, дата, какие константы добавляет **модуль**. Если все ок, то **модуль** точно готов.
11. Код главного **модуля** можно коммитить. Для этого выполняются следующие команды в **bash-консоле**
  ```bash
  git config --local user.name <свои имя и фамилия>
  git config --local user.email <свой email, обычно, это что-то оканчивающееся на @infoservice.ru>
  git checkout -b <КОД ЗАДАЧИ>
  git add .
  git commit -m "<КОД ЗАДАЧИ> Начальные файлы главного модуля infoservice.<код клиента>"
  ```

#### Создание дочернего модуля

1. Если ранее уже был добавлен **функционал** поддержки **дочерних модулей**, то переходим к **шагу 4**. Если нет, то сравниваем ветки **remotes/infsmdl/master** и **remotes/infsmdl/childmodule**, сохраняем в отдельном **файле**, и из него забираем данные
  ```bash
  git diff remotes/infsmdl/master remotes/infsmdl/childmodule > childmodule.txt
  ```
2. Берем только те правки, что относятся к **главному модулю**, по **дочернему** потом скопируем иначе. Например, в **файле** есть **код**
  ```gitdiff
     /**
+     * Устанавливает модуль, но сначала проверяет не является ли он
+     * дочерним, а, если это так, то при условии, что родительские модули
+     * не установлены, сначала устанавливает их
+     * 
+     * @return void
+     */
+    protected function initFullInstallation()
+    {
+        set_time_limit(0);
+        $parentClassName = get_parent_class(get_called_class());
+        if (($parentClassName != 'CModule') && !(new $parentClassName())->IsInstalled())
+            (new $parentClassName())->DoInstall(false);
+
+        RegisterModule($this->MODULE_ID);
+    }
+
+    /**
      * Проверяет у модуля наличие класса Employment в своем подпространстве имен EventHandles,
      * а так же наличие у него метода, название которого передано в параметре $methodName.
      * В случае успеха вызывает метод у своего Employment
@@ -181,7 +198,7 @@ class infoservice_testtask extends CModule
     public function DoInstall(bool $stopAfterInstall = true) 
     {
         global $APPLICATION;
-        RegisterModule($this->MODULE_ID);
+        $this->initFullInstallation();
  ```
  
  Значит, копируем все что начинается с `+` и удаляем все, что начинается с `-` в аналогичный **файл**. Можно спокойно копировать и вставлять вместе с `+`, затем легко в **редакторе кода** через **функцию замены** убрать символ `+` по **регулярному выражению**
  ```regex
  ^\+
  ```
3. Делаем коммит
  ```bash
  git add .
  git commit -m "<КОД ЗАДАЧИ> В главный модуль infoservice.<код клиента> добавлен функционал поддержки дочерних модулей"
  ```
4. Создаем **код дочернего модуля**. Для начала выведем список новых **файлов** через сравнение веток
  ```bash
  git diff remotes/infsmdl/master remotes/infsmdl/childmodule --name-status
  ```
5. Копируем все выведенные **файлы**, обычно, это кратко делается так
  ```bash
  git checkout remotes/infsmdl/childmodule modules/infoservice.childmodule
  ```
6. Переименовываем **модуль** `modules/infoservice.childmodule` на `modules/infoservice.<код с намеком на категорию задач>`, где `<код с намеком на категорию задач>` - какое-то указание, что делает **модуль**, например, в нем хранятся **действий БД**, тогда ему подойдет название `bpactivities`
7. В **файле** `modules/infoservice.<код дочернего модуля>/include.php` меняем все костанты `INFS_CHILDMODULE_...` на `INFS_<КОДДОЧЕРНЕГОМОДУЛЯ>_...`
8. В **файле** `modules/infoservice.<код дочернего модуля>/lang/ru/install/index.php`
  * поправляем **языковые константы** с `INFOSERVICE_CHILDMODULE_...` на `INFOSERVICE_<КОДДОЧЕРНЕГОМОДУЛЯ>_...`
  * Изменяем значения **языковых констант**
    * `INFOSERVICE_<КОДДОЧЕРНЕГОМОДУЛЯ>_MODULE_NAME` - название модуля, обычно, это краткое описание категории задач
    * `INFOSERVICE_<КОДДОЧЕРНЕГОМОДУЛЯ>_MODULE_DESCRIPTION`- описание **модуля**, обычное пустое
9. В **файле** `modules/infoservice.<код дочернего модуля>/lib/helpers/options.php` меняем часть значения именных пространств
  * для `namespace` c `Infoservice\ChildModule` на `Infoservice\<КодДочернегоМодуля>`
  * для `use` c `Infoservice\TestTask` на `Infoservice\<КодКлиента>`
10. В **файле** `modules/infoservice.<код дочернего модуля>/install/index.php` заменяем
  * название класса **infoservice_testtask** на **infoservice_<кодклиента>**
  * название класса **infoservice_childmodule** на **infoservice_<коддочернегомодуля>**
  * подключение **файла** `/local/modules/infoservice.testtask/install/index.php;` на `/local/modules/infoservice.<код клиента>/install/index.php`. *Для таких случаев можно было использовать **автолоадинг классов***
11. Вносим дату окочания в **файл** `modules/infoservice.<код дочернего модуля>/install/version.php`
12. Модуль готов, осталось выполнить **bash-код** для создания символьных ссылок в `www/local/modules`. Сам **код** и инструкция находятся в **пункте 9** из раздела **Создание главного модуля**
13. Если на странице в браузере `https://.../bitrix/admin/partner_modules.php` появляется **модуль**, то все ок. Можно установить его, при этом установится и **главный модуль**, если не был установлен, а, удалив **главный модуль**, то должен удалиться и **дочерний модуль**. У **дочернего модуля** могут свои **дочерние модули**, а у тех свои и т.д.
14. Код **дочернего модуля** можно коммитить. Для этого выполняются следующие команды
  ```bash
  git add .
  git commit -m "<КОД ЗАДАЧИ> Начальные файлы модуля infoservice.<код дочернего модуля>"
  ```

#### Добавление какого-то функционала из любой ветки, например, создания инфоблоков, свойств инфоблоков, пользовательских свойств и т.д.

1. Смотрим, какая ветка несет в своем названии указание на создание данных
  ```bash
  git branch -a
  ```

  скорее всего, на это намекается ветка **remotes/infsmdl/adddata**, но там без конкретных данных, только общие для этого методы, для **инфоблоков** лучше подойдет ветка **remotes/infsmdl/adddata+iblock**, а еще лучше ветка **remotes/infsmdl/adddata+iblock+lists**, так как там описано еще и создание **полей** для **списков**

2. Сравниваем ветки **remotes/infsmdl/master** и **remotes/infsmdl/adddata+iblock+lists**, сохраняем в отдельном **файле**, и из него забираем данные
  ```bash
  git diff remotes/infsmdl/master remotes/infsmdl/adddata+iblock+lists > adddata+iblock+lists.txt
  ```
3. Из файла `adddata+iblock+lists.txt` копируем все что начинается с `+` и удаляем все, что начинается с `-`, **НО ТОЛЬКО** в тех **файлах**, аналог которых найдется и в текущей версии **главного модуля**. Можно спокойно копировать и вставлять вместе с `+`, затем легко в **редакторе кода** через **функцию замены** убрать символ `+` по **регулярному выражению**
  ```regex
  ^\+
  ```
  **Код** для **создания** лучше вставлять перед методом `runInstallMethods`, а **код** для **удаления** перед методом `runRemoveMethods`

4. Копируем новые **файлы**, для начала выведем список новых **файлов** через сравнение веток
  ```bash
  git diff remotes/infsmdl/master remotes/infsmdl/adddata+iblock+lists --name-status
  ```

  и забираем все **файлы**, напротив которых есть ключ **A**
  ```bash
  git checkout remotes/infsmdl/adddata+iblock+lists <файл-1> <файл-2> <файл-3> ...  <файл-N>
  ```

  по функционалу добавления данных с **инфоблоками** из ветки **remotes/infsmdl/adddata+iblock+lists** нужно выполнить
  ```bash
  git checkout remotes/infsmdl/adddata+iblock+lists modules/infoservice.testtask/lib/helpers/modules/lists/listfieldlist.php
  ```

  наконец-то, преносим новые **файлы** в **главный модуль** в аналогичные места

5. Делаем замены
  * всех `use` c `Infoservice\TestTask` на `Infoservice\<КодКлиента>`
  * Удаляем тестовые примеры, например, из ветки **remotes/infsmdl/adddata+iblock+lists**, описанные в файлах
    * `modules/infoservice.testtask/include.php`
    * `modules/infoservice.testtask/install/index.php`, все описания внутри категорий (**IBlocks**, **IBlockProperties** и т.д.) из консанты модуля **OPTIONS**
    * `modules/infoservice.testtask/lang/ru/install/index.php`

6. Обновляем скопированные **методы** в **файле** `modules/infoservice.<код клиента>/install/index.php`, копируя **код** из **последних проектов**, где мог применятся тот же **функционал**. Например,
    * для ветки **remotes/infsmdl/adddata** у методов `initOptions` и `removeOptions` есть более лучше версии, которые лучше работают с контантой `SAVE_OPTIONS_WHEN_DELETED`
    * для ветки **remotes/infsmdl/files** у метода `deleteEmptyPath` есть более лучше версия

7. Проверяем **установку модуля**, желательно, посмотреть что выведет **код** из **пункта 10** из раздела **Создание главного модуля**

8. Можно коммитить добавленный функционал. Для этого выполняются следующие команды
  ```bash
  git add .
  git commit -m "<КОД ЗАДАЧИ> В главынй модуль infoservice.<код клиента> добавлен функционал добаления типов инфоблоков, инфоблоков, их свойств и указание главных параметров и добавленных свойств как полей списков"
  ```