<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/infoservice.testtask/install/index.php';

class infoservice_childmodule extends infoservice_testtask
{
    /**
     * Здесь надо просто объявлять те же константы, что и в родительском модуле, 
     * в котором и должен находиться весь код по установке и удалению.
     *
     * Дочерний модуль не установит для себя то же, что есть в этих же константах
     * у родительского модуля, только то, что указано в константах тут
     */
}