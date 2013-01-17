{assign var=nagiosServices value=$host.services_with_state}
{*$nagiosServices|@var_dump*}
{if $nagiosServices}
<ul>
{foreach name=services from=$nagiosServices item=service}
    <li id="service{$service.md5}" class="service bgServiceState{if isset($service[1])}{$service[1]}{else}{$service.state}{/if}" service-key="{if isset($host['db_data']) && $host.db_data.host_id}{$host.db_data.host_id}{else}{$host.name}{/if}:{$service.service_description}">
    <span title="{if isset($service[0])}{$service[0]}{else}{$service.service_description}{/if}"></span>
    {*if $service.state_duration}<span class="duration">({$service.state_duration|human_interval})</span>{/if*}
    {*
    <div class="serviceImages">
        <img class="imgAcknowleged{if $service.acknowledged != 1} hidden{/if}" src="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/images/ack.gif" alt="" title="Сервис в обработке"/>
        <img class="imgComments{if not $service.comments} hidden{/if}" src="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/images/comment.gif" alt="" title="Комментарии: {$service.comments|@count}"/>
        <img class="imgFlapping{if not $service.is_flapping} hidden{/if}" src="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/images/flapping.gif" alt="" title="Обнаружено мерцание"/>
    </div>
    *}
    </li>
{/foreach}
</ul>
{/if}