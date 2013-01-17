{if isset($operation) && $operation}
    {assign var=operationHost value=1}
    {elseif isset($host) && $host && (!isset($host['nagios']['IS_STOROZH']) || !$host.nagios.IS_STOROZH) && (!isset($host['IS_STOROZH']) || !$host.IS_STOROZH)}
    {assign var=operationHost value=1}
    {else}
    {assign var=operationHost value=0}
{/if}

<div data-role="collapsible-set" data-theme="a" data-content-theme="d" data-inset="false">
    <div data-role="collapsible" data-mini="true">
        <h2>Список</h2>
        <ul data-role="listview" data-divider-theme="b" data-inset="false" data-filter="true" data-filter-placeholder="Фільтр" data-mini="true">
        {foreach name=hosts from=$overview|@sortby:"-db_data.is_on_service,db_data.alias" item=hostItem}
            {if (isset($hostItem.IS_STOROZH) && $hostItem.IS_STOROZH && !$operationHost) || ((!isset($hostItem.IS_STOROZH) || !$hostItem.IS_STOROZH) && $operationHost)}
                {if isset($hostItem['db_data']) && $hostItem.db_data.is_on_service}
                    {assign var='host_is_on_service' value=1}
                    {else}
                    {assign var='host_is_on_service' value=0}
                {/if}
                {if isset($old_host_is_on_service) && $old_host_is_on_service neq $host_is_on_service}
                    <!--li data-role="list-divider" role="heading">&nbsp;</li-->
                {/if}
                <li data-mini="true" id="hostNav{$hostItem.md5}"
                    class="hostNav {if isset($hostItem['db_data']) && $hostItem.db_data.is_on_service}on-service{else}not-on-service{/if}"
                    {if isset($host) && isset($host.nagios.md5) && $hostItem.md5 eq $host.nagios.md5}data-theme="a"{/if}
                        >
                    <a href="{$smarty.const.SOVA_BASE_URL}host/index/id/{$hostItem.name|escape:"url"}"
                       data-transition="slide">{if isset($hostItem['db_data']) && $hostItem.db_data.host_id}{$hostItem.db_data.alias}{else}{$hostItem.alias}{/if}</a>
                </li>
                {assign var='old_host_is_on_service' value=$host_is_on_service}
            {/if}
        {/foreach}
        </ul>
    </div>
</div>