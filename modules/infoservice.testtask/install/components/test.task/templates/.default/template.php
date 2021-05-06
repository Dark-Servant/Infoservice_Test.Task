<?
use \Bitrix\Main\Localization\Loc;

CUtil::InitJSCore(['jquery']);
$APPLICATION->SetAdditionalCSS('/bitrix/js/ui/buttons/src/css/ui.buttons.css');?>

<div class="test-button ui-btn-main ui-btn-split ui-btn-primary"><?=Loc::getMessage('TEST_BUTTON')?></div>