<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{if $pageTitle}{$pageTitle|escape} :: {/if}СОВА :: {$smarty.const.SITE_NAME}</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>

    <link rel="stylesheet" href="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/css/smoothness/jquery-ui-1.9.0.custom.css" type="text/css" />

    <link rel="stylesheet" href="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/css/styles.css" type="text/css" />

    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/json/json2.js"></script>

    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery-1.8.2.js"></script>
    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery/jquery.cookie.js"></script>
    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery/jquery.jeditable.js"></script>
    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery/jquery-ui-1.9.0.custom.min.js"></script>
    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery/i18n/jquery-ui-i18n.min.js"></script>

    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/sova.js"></script>

    {$xajax_javascript}

    <script type="text/javascript">
    /* <![CDATA[ */
	$(document).ready(function() {
		nagiosWatcher.assignWatcherController(xajax_{$controllerName});
		nagiosWatcher.startAlertsWatch({$smarty.const.REQUEST_INTERVAL});

        $.datepicker.setDefaults($.datepicker.regional[ "ru" ]);

		initStatusBar();
	});

    $(document).unload(function() {
        nagiosWatcher.stopAlertsWatch();
    });

	function logout() {
	    return confirm('Дійсно вийти?');
	}

    /* ]]> */
    </script>

    <script type="text/javascript">
    //<![CDATA[
    var dhxWins, alertWindow;
    var dhxPath = '{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.LAYOUT_NAME}/';
    BASE_URL = "{$smarty.const.SOVA_BASE_URL}";
    REWRITE_BASE = "{$smarty.const.REWRITE_BASE}";
    var sAlertId = '{if isset($alert)}{$alert.alert_id}{/if}';

    $(document).ready(function (){
        $('#header-puller a').click(function(){
            $('#header-body').toggle();
        });
    });

    //]]>
    </script>

    {*<script type="text/javascript" src="https://www.google.com/jsapi"></script>*}

    {foreach name=head_html from=$head_html item=html}
    {$html}
    {/foreach}

</head>
<body>
<div class="wrapper">
	<div id="header">
        <div id="header-body">
            <div id="header-title">{if isset($operator.name)}{$operator.name}{else}{$user.name}{/if}</div>
            <div id="header-links">
                <a id="settings-link" href="#">Налаштування</a>
                <a href="{$smarty.const.SOVA_BASE_URL}operator/logout/" onclick="return logout();">Вихід</a>
            </div>
        </div>
        <div id="header-puller">
            <span><a href="javascript:void(0)" title="Меню">..</a></span>
        </div>
	</div>
	<div class="clearer">&nbsp;</div>
    <div id="container">
        <div id="leftnav-container">
        {include file="inc/menu.tpl"}
        <div id="credentials">Розроблено <a href="http://i-p.in.ua/" target="_blank"><img src="{$smarty.const.LAYOUT_IMAGES_URL}logo_ip.jpg" alt="http://i-p.in.ua/" width="65" height="26" /></a></div>
        </div>
        <div id="content">{$content}</div>
	</div>
	{include file="inc/statusbar.tpl"}
    {include file="form/settings.tpl"}
</div>

</body>
</html>
