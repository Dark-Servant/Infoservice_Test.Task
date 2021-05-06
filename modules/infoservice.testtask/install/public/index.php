<?// Этот раздел можно найти по адресу https://<доменное имя>/local/public/ ?>
<?use \Bitrix\Main\Localization\Loc;?>
<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');?>
<?$APPLICATION->SetTitle(Loc::getMessage('TITLE'));?>
<?$APPLICATION->IncludeComponent('infoservice.testtask:test.task', '', []);?>
<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');?>