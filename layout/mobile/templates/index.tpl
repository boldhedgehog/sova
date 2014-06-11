<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{if $pageTitle}{$pageTitle|escape} :: {/if}СОВА :: {$smarty.const.SITE_NAME}</title>
    <link rel="stylesheet" href="https://ajax.aspnetcdn.com/ajax/jquery.mobile/1.2.0/jquery.mobile-1.2.0.min.css" />
    <link rel="stylesheet" href="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/css/styles.css" />
    <script src="{$smarty.const.SOVA_BASE_URL}js/jquery/jquery.cookie.js"></script>
    <script src="https://ajax.aspnetcdn.com/ajax/jquery.mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>

    <script src="{$smarty.const.SOVA_BASE_URL}js/sova.js"></script>
    <script src="{$smarty.const.SOVA_BASE_URL}js/mobile.js"></script>

    <script type="text/javascript">
    /* <![CDATA[ */
    $(document).bind('pageinit', function() {
		nagiosWatcher.assignWatcherController(xajax_{$controllerName});
		//nagiosWatcher.startAlertsWatch({$smarty.const.REQUEST_INTERVAL});

		initStatusBar();

        $('#logout-link').click(function() {
            return confirm('Дійсно вийти?');
        });
	});

    $(document).unload(function() {
        //nagiosWatcher.stopAlertsWatch();
    });

    /* ]]> */
    </script>

    <script type="text/javascript">
    //<![CDATA[
    BASE_URL = "{$smarty.const.SOVA_BASE_URL}";
    REWRITE_BASE = "{$smarty.const.REWRITE_BASE}";
    var sAlertId = '{if isset($alert)}{$alert.alert_id}{/if}';

    //]]>
    </script>
</head>
<div data-role="page" data-add-back-btn="true" class="type-interior">
    <div data-theme="a" data-role="header" data-position="fixed">
        {include file="inc/back_button.tpl"}
        <h1>
        {if $pageTitle}{$pageTitle|escape}{/if}
        </h1>
        <a href="#settings-dialog" data-rel="popup" data-iconpos="notext" data-position-to="window" data-role="button" data-icon="gear" data-mini="true">Налаштування</a>
        {if isset($operation) && $operation}
            {assign var=operationHost value=1}
            {elseif isset($host) && $host && (!isset($host['nagios']['IS_STOROZH']) || !$host.nagios.IS_STOROZH) && (!isset($host['IS_STOROZH']) || !$host.IS_STOROZH)}
            {assign var=operationHost value=1}
            {else}
            {assign var=operationHost value=0}
        {/if}
        <div data-role="navbar" data-inset="false">
            <ul>
                <li><a href="{$smarty.const.SOVA_BASE_URL}overview/" class="{if !$operationHost}ui-btn-active{/if}">ПНО</a></li>
                <li><a href="{$smarty.const.SOVA_BASE_URL}overview/operation/" class="{if $operationHost}ui-btn-active{/if}">Спеціальні</a></li>
            </ul>
        </div>
    </div>
    <div data-role="content" class="content-main">
        <div class="content-secondary">{include file="inc/menu.tpl"}</div>
        <div class="content-primary">{$content}</div>
    </div>
    <div data-theme="a" data-role="footer">
        <a href="{$smarty.const.SOVA_BASE_URL}operator/logout/" id="logout-link" data-role="button" data-icon="delete" data-mini="true" class="ui-btn-right">Вихід</a>
        <h3><div id="credentials">&copy; Розроблено <a href="http://i-p.in.ua/" target="_blank">http://i-p.in.ua/</a></div></h3>
    </div>
    {include file="form/settings.tpl"}
</div>
<script>
    //App custom javascript
</script>
</body>
</html>
