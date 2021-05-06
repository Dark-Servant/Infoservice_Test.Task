<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('TITLE'));
?>
<?
$APPLICATION->IncludeComponent(
    'bitrix:calendar.grid',  '', 
    [
        'ALLOW_RES_MEETING' => 'Y',
        'ALLOW_SUPERPOSE' => 'Y',
        'CALENDAR_TYPE' => INFS_CALENDAR_TYPE_TESTING,
        'COMPONENT_TEMPLATE' => '.default'
    ]
);?>
<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');?>