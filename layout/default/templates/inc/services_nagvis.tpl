{if isset($host['nagvis_map_config'])}
<div id="nagvisServiceIconOverlay">
    {assign var="nagvisServices" value=$host.nagvis_map_config}
    {assign var="services" value=$host.nagios.services}
    <script type="text/javascript">
    //<![CDATA[
    nagvisServices = {$nagvisServices|@json_encode};
    //]]>
    </script>
    {foreach from=$nagvisServices item=service key=service_key}
        {if $service['type'] eq 'service'}
            {if !isset($service['md5']) or !isset($services[$service.md5])}
                {continue}
            {/if}
            {include file="inc/service/nagvis/service.tpl" services=$services nagios_service=$nagios_service}
        {elseif $service['type'] eq 'textbox'}
            {include file="inc/service/nagvis/textbox.tpl" service=$service}
        {/if}

    {/foreach}
</div>
{/if}
