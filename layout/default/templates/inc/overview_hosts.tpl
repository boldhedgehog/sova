{assign var="is_on_service" value=$is_on_service|default:0}
{assign var="operation" value=$operation|default:0}
<ul class="hosts {if $is_on_service}on-service{else}not-on-service{/if}">
{foreach name=hosts from=$overview|@sortby:"-db_data.is_on_service,db_data.alias" item=host}
{if (!isset($host['db_data']) && !$is_on_service) || (isset($host['db_data']) && $host.db_data.is_on_service neq $is_on_service)}
    {continue}
{/if}
{if !$operation && isset($host.IS_STOROZH) && $host.IS_STOROZH || $operation && (!isset($host.IS_STOROZH) || !$host.IS_STOROZH)}
    <li id="host{$host.md5}" class="host {if isset($host['db_data']) && $host.db_data.is_on_service}on-service{else}not-on-service{/if}">
        <div class="hosticonContainer">
{if $host.icon_image_with_status or $host.services_with_state}
            <a href="{$smarty.const.SOVA_BASE_URL}host/index/id/{$host.name|escape:"url"}" class="hostLink">
                {if $host.services_with_state}
                <div class="service-icons">
                    <div class="services">{include file="inc/services_overview.tpl" host=$host}</div>
                </div>
                {/if}
                {if not $host.services|@count > 0}<img id="hostIcon{$host.md5}" width="157" height="91"
                        src="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/images/logos/{$host.icon_image_with_status}" 
                        alt="{$host.alias|escape}" class="hosticon"/>{/if}
            </a>
{/if}
        </div>
        <a href="{$smarty.const.SOVA_BASE_URL}host/index/id/{$host.name|escape:"url"}" class="hostLinkOuter">
            <span>{if isset($host['db_data']) && $host.db_data.host_id}{$host.db_data.alias}{else}{$host.alias}{/if}</span>
        </a>
    </li>
{/if}
{/foreach}
</ul>
