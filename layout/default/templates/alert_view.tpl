<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Тривога - Сова - {$smarty.const.SITE_NAME}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.LAYOUT_NAME}/css/styles.css" type="text/css" />
        <link rel="stylesheet" href="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.LAYOUT_NAME}/css/dhtmlxtabbar.css" type="text/css" />

        <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery-1.5.1.min.js"></script>

        <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/dhtmlxcommon.js"></script>
        <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/dhtmlxwindows.js"></script>
        <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/dhtmlxtabbar.js"></script>
        <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/dhtmlxcontainer.js"></script>

        <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/sova.js"></script>

{$xajax_javascript}

	<script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/soundmanager2/soundmanager2-nodebug-jsmin.js"></script>
    
        <script type="text/javascript">
        //<![CDATA[
        dhxPath = '{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.LAYOUT_NAME}/';
        baseUrl = '{$smarty.const.SOVA_BASE_URL}';
        alertId = '{$alert.alert_id}';
        alertState = '{$alert.state}';
	
        {literal}

        $(document).ready(function(){
                initSessionNotifications();
                initDhtmlXContainer('processArea');
        })
        {/literal}
        //]]>
        </script>

    </head>
    <body style="margin: 0">
    {include file="inc/notifications.tpl" notifications=$sessionNotifications}
    {include file="form/alert.tpl" alert=$alert readonly=true}
    </body>
</html>
