{assign var=nagiosServices value=$host.nagios.services}
{if $nagiosServices}
<ul class="services">
{foreach name=services from=$nagiosServices|@sortby:"db_data.zone.plas_zone_id,db_data.position,db_data.alias" item=service}
    {assign var=dbService value=$service.db_data}
    {if $dbService}
        {if $dbService.sensor_type.name}
            {assign var=serviceName value=$dbService.position|cat:":"|cat:$dbService.sensor_type.name}
        {else}
            {assign var=serviceName value=$dbService.alias}
        {/if}
    {else}
        {if $service.notes_expanded}
            {assign var=serviceName value=$service.notes_expanded}
        {else}
            {assign var=serviceName value=$service.display_name}
        {/if}
    {/if}

    {if $dbService}
        {assign var=serviceType value=$dbService.type}
    {else}
        {assign var=serviceType value=$service.SOVA_SERVICE_TYPE}
    {/if}

    {if ($smarty.foreach.services.iteration-1)%20 == 0 && !$smarty.foreach.services.first}</ul><ul class="services">{/if}
    <li id="service{$service.md5}" class="service bgServiceState{$service.state}">
    {if $service.is_storozh && !$nolinks}<a href="{$smarty.const.SOVA_BASE_URL}service/index/id/{$host.host_id}:{$service.description|escape:"url"}" title="{$serviceName}" class="serviceLink" target="{$service.md5}">{/if}<span>{$serviceName}</span>{if $service.is_storozh && !$nolinks}</a>{/if}
    {*if $service.state_duration}<span class="duration">({$service.state_duration|human_interval})</span>{/if*}
    <div class="serviceImages">
        <img class="imgAcknowleged{if $service.acknowledged != 1} hidden{/if}" src="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/images/ack.gif" alt="" title="Сервис в обработке"/>
        <img class="imgComments{if not $service.comments} hidden{/if}" src="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/images/comment.gif" alt="" title="Комментарии: {$service.comments|@count}"/>
        <img class="imgFlapping{if not $service.is_flapping} hidden{/if}" src="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/images/flapping.gif" alt="" title="Обнаружено мерцание"/>
    </div>
    </li>
{/foreach}
</ul>
{/if}
