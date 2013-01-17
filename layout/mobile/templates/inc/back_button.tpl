{if isset($service) && isset($host)}
<a href="{$smarty.const.SOVA_BASE_URL}host/index/id/{$host.nagios_name|escape:"url"}" data-iconpos="notext" data-icon="back" data-direction="reverse" data-mini="true">Назад</a>
{elseif isset($host)}
    {if isset($operation) && $operation}
        {assign var=operationHost value=1}
    {elseif isset($host) && $host && (!isset($host['nagios']['IS_STOROZH']) || !$host.nagios.IS_STOROZH) && (!isset($host['IS_STOROZH']) || !$host.IS_STOROZH)}
        {assign var=operationHost value=1}
    {else}
        {assign var=operationHost value=0}
    {/if}
    {if !$operationHost}
<a href="{$smarty.const.SOVA_BASE_URL}overview/" data-iconpos="notext" data-icon="back" data-direction="reverse" data-mini="true">Назад</a>
    {else}
<a href="{$smarty.const.SOVA_BASE_URL}overview/operation/" data-iconpos="notext" data-icon="back" data-direction="reverse" data-mini="true">Назад</a>
    {/if}
{else}
<a href="{$smarty.const.SOVA_BASE_URL}" data-iconpos="notext" data-icon="home" data-direction="reverse" data-mini="true">Назад</a>
{/if}
