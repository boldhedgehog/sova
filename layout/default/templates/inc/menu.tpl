{if isset($operation) && $operation}
    {assign var=operationHost value=1}
{elseif isset($host) && $host && (!isset($host['nagios']['IS_STOROZH']) || !$host.nagios.IS_STOROZH) && (!isset($host['IS_STOROZH']) || !$host.IS_STOROZH)}
    {assign var=operationHost value=1}
{else}
    {assign var=operationHost value=0}
{/if}
{*$host|@var_dump*}

{literal}
<script type="text/javascript">
/* <![CDATA[ */
    $(document).ready(function() {
	//$('div#leftnav .tabs').tabs();
	$('input#host-lookup').keyup(function () {
	    $('div#leftnav ul.hostsNav').listfilter($(this));
	});
    });
/* ]]> */
</script>
{/literal}
<div id="leftnav">
    <div class="tabs">
	<ul class="tabs">
	    <li class="{if !$operationHost}active{/if}"><a href="{$smarty.const.SOVA_BASE_URL}overview/">ПНО</a></li>
	    <li class="{if $operationHost}active{/if}"><a href="{$smarty.const.SOVA_BASE_URL}overview/operation/">Спеціальні</a></li>
	</ul>
	<div id="host-lookup-container">
	    <input type="text" id="host-lookup"/>
	</div>
	<ul class="hostsNav">
	    {foreach name=hosts from=$overview|@sortby:"-db_data.is_on_service,db_data.alias" item=hostItem}
		{if (isset($hostItem.IS_STOROZH) && $hostItem.IS_STOROZH && !$operationHost) || ((!isset($hostItem.IS_STOROZH) || !$hostItem.IS_STOROZH) && $operationHost)}
                    {if isset($hostItem['db_data']) && $hostItem.db_data.is_on_service}
                        {assign var='host_is_on_service' value=1}
                    {else}
                        {assign var='host_is_on_service' value=0}
                    {/if}
                    {if isset($old_host_is_on_service) && $old_host_is_on_service neq $host_is_on_service}
                        <li class="separator"><span>&nbsp;</span></li>
                    {/if}
	    <li id="hostNav{$hostItem.md5}" class="hostNav{if isset($host) && isset($host.nagios.md5) && $hostItem.md5 eq $host.nagios.md5} active{/if}  {if isset($hostItem['db_data']) && $hostItem.db_data.is_on_service}on-service{else}not-on-service{/if}">
		<a href="{$smarty.const.SOVA_BASE_URL}host/index/id/{$hostItem.name|escape:"url"}" class="hostLinkOuter">{if isset($hostItem['db_data']) && $hostItem.db_data.host_id}{$hostItem.db_data.alias}{else}{$hostItem.alias}{/if}</a>
	    </li>
                    {assign var='old_host_is_on_service' value=$host_is_on_service}
		{/if}
	    {/foreach}
	</ul>
    </div>
</div>
