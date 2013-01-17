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

{if not $success and not $already_processed}
{$xajax_javascript}

	<script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/soundmanager2/soundmanager2-nodebug-jsmin.js"></script>
    
        <script type="text/javascript">
        //<![CDATA[
        dhxPath = '{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.LAYOUT_NAME}/';
        baseUrl = '{$smarty.const.SOVA_BASE_URL}';
        alertId = '{$alert.alert_id}';
        alertState = '{$alert.state}';
	
        {literal}

        $(document).bind('pageinit', function(){
                //initContacts('body');
                xajax_alertController.xajaxConfirmOpen();
                initSessionNotifications();
                initForm();
                initDhtmlXContainer('processArea');
                initNagvisMap();

                if (typeof(soundManager != 'undefined')) {
            	    soundManager.url = dhxPath + 'swf/';
            	    soundManager.onready(function() {
        		// check if SM2 successfully loaded..
            		if (soundManager.supported()) {
            		    // SM2 has loaded - now you can create and play sounds!
            	    	    var alertSound = soundManager.createSound({
            	    		id: 'alertSound',
            	        	url: dhxPath + '/mp3/alert'+ alertState +'.mp3'
            		    });
            	    	    alertSound.play();
            		} else {
            		    // SM2 is not supported
            		}
            	    });
                }
        })
        {/literal}
        //]]>
        </script>
{/if}

    </head>
    <body style="margin: 0">
{include file="inc/notifications.tpl" notifications=$sessionNotifications}
{if $success}
        <script type="text/javascript">
        //<![CDATA[
        window.top.dhxWins.window('alertWindow{$alert.alert_id}').close();
        //]]>
        </script>
{elseif $already_processed}
        <span class="error">Ця тривога вже була оброблена.</span>
        <br/>
        <input type="submit" value="Закрити" onclick="if(window.top && window.top.dhxWins) window.top.dhxWins.window('alertWindow{$alert.alert_id}').close(); else window.close();"/>
{else}
    {include file="form/alert.tpl" alert=$alert}

{/if}
    </body>
</html>
