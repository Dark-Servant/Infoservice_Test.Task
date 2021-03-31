<?// Этот раздел можно найти по адресу https://<доменное имя>/local/public/ ?>
<?use \Bitrix\Main\Localization\Loc;?>
<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');?>
<?$APPLICATION->SetTitle(Loc::getMessage('TITLE'));?>
<pre><?print_r($_REQUEST)?></pre>
<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');?>