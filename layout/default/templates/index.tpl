<!DOCTYPE html>
<html>
<head>
    <title>{if $pageTitle}{$pageTitle|escape} :: {/if}СОВА :: {$smarty.const.SITE_NAME}</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>

    <link rel="stylesheet" href="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/css/smoothness/jquery-ui-1.9.0.custom.css" type="text/css" />

    <link rel="stylesheet" href="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/css/styles.css" type="text/css" />

    <link rel="stylesheet" href="{$smarty.const.SOVA_BASE_URL}js/fancybox2/jquery.fancybox.css?v=2.1.3" type="text/css" media="screen" />

    <link href="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/css/font-awesome.css" rel="stylesheet">

    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/json/json2.js"></script>

    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery-1.8.2.js"></script>
    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery/jquery.cookie.js"></script>
    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery/jquery.jeditable.js"></script>
    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery/jquery-ui-1.9.0.custom.min.js"></script>
    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery/i18n/jquery-ui-i18n.min.js"></script>

    <!-- Add fancyBox -->
    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/fancybox2/jquery.fancybox.pack.js?v=2.1.3"></script>

    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/sova.js?v=14032015"></script>

    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>

    <script type="text/javascript">
    /* <![CDATA[ */
	$(document).ready(function() {
		nagiosWatcher.startAlertsWatch({$smarty.const.REQUEST_INTERVAL});
        nagiosWatcher.baseURL = "{$smarty.const.SOVA_BASE_URL}";
        nagiosWatcher.requestURI = "{$controller->getRequestUri()}";
        nagiosWatcher.refreshURI = "{$smarty.const.SOVA_BASE_URL}{$controller->getRefreshUri()}";

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

    // pullers
    $(document).ready(function (){
        $('#header-puller').find('a').click(function(){
            $('#header-body').toggle();
        });

        var navOffset = $('.hostNav.active').offset();
        var $pullerLeft = $('#left-puller');
        if (navOffset) {
            $pullerLeft.css('top', navOffset.top);
        }

        $pullerLeft.click(toggleCollapseLeftNav());

        if ($.cookie('nav_collapsed') == 'true' && !$pullerLeft.hasClass('clicked')) {
            $pullerLeft.click();
        }

        $(window).scroll(function() {
            var $leftContainer = $('#leftnav-container');
            if (!$pullerLeft.hasClass('clicked')) {
                if ($(this).scrollTop() > ($leftContainer.offset().top + $leftContainer.height())) {
                    $leftContainer.addClass('collapsed');
                    $('#content').addClass('expanded');
                } else {
                    $leftContainer.removeClass('collapsed', 'fast');
                    $('#content').removeClass('expanded');
                }
            }
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
        <div id="left-puller"{if $overview|@count <= 1} class="clicked"{/if}>
            <span class="fa {if $overview|@count <= 1}fa-angle-double-right{else}fa-angle-double-left{/if}"></span>
        </div>
        <div id="leftnav-container"{if $overview|@count <= 1} class="collapsed"{/if}>
        {include file="inc/menu.tpl"}
        <div id="credentials">Розроблено <a href="http://i-p.in.ua/" target="_blank"><img src="{$smarty.const.LAYOUT_IMAGES_URL}logo_ip.jpg" alt="http://i-p.in.ua/" width="65" height="26" /></a></div>
        </div>
        <div id="content"{if $overview|@count <= 1} class="expanded"{/if}>{$content}</div>
	</div>
	{include file="inc/statusbar.tpl"}
    {include file="form/settings.tpl"}
</div>

</body>
</html>
