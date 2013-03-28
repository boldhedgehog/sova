{*assign var=services value=$host.services*}
{assign var=nagiosServices value=$host.nagios.services}
<table class="grid services" id="services{$host.nagios.md5}">
    {*if !$isAjax*}
    <thead>
	<tr>
	    <th class="nofilter">&nbsp;</th>
        <th><span>№</span></th>
        <th><span>Тип</span></th>
        {if $servicesHasAliasColumn}<th><span>Назва</span></th>{/if}
	    <th><span>Повідомлення</span></th>
        {*
	    <th class="time nofilter">Останнє оновлення</th>
	    <th class="time nofilter">Остання зміна статусу</th>
	    *}
        <th class="time nofilter" title="Тривалість">Тр-ть</th>
	</tr>
    </thead>
    {*/if*}
    <tbody>
        {foreach name=services from=$nagiosServices|@sortby:"db_data.zone.plas_zone_id,db_data.position,db_data.alias" item=service}
        {if isset($service['db_data'])}
            {assign var=dbService value=$service.db_data}
            {if $dbService.sensor_type.name}
                {assign var=serviceName value=$dbService.position|cat:":"|cat:$dbService.sensor_type.name}
            {else}
                {assign var=serviceName value=$dbService.alias}
            {/if}
        {else}
            {assign var=dbService value=0}
            {if $service.notes_expanded}
                {assign var=serviceName value=$service.notes_expanded}
            {else}
                {assign var=serviceName value=$service.display_name}
            {/if}
        {/if}
        
        {if $dbService}
            {assign var=serviceType value=$dbService.type}
        {elseif isset($service.SOVA_SERVICE_TYPE)}
            {assign var=serviceType value=$service.SOVA_SERVICE_TYPE}
        {else}
    	    {assign var=serviceType value=null}
        {/if}

        {if $dbService.zone && (!isset($_old_zone_id) || $dbService.zone_id != $_old_zone_id)}
            {assign var=zone_row_type value="new"}
        {elseif $dbService.zone.name}
            {assign var=zone_row_type value="continue"}
        {else}
            {assign var=zone_row_type value="empty"}
        {/if}

        {if $zone_row_type == "new" or $zone_row_type == "empty"}
        <tr>
            {if $zone_row_type == "new"}
                <td colspan="6" class="zone-header first" title="{$dbService.zone.name|escape}"><h4><span class="zone-id">{$dbService.zone.name|escape}</span></h4></td>
            {else}
                <td colspan="6" class="zone-header-empty first">Без Зони</td>
            {/if}
        </tr>
        {/if}
        <tr class="state{$service.state}{if $smarty.foreach.services.first} first{elseif $smarty.foreach.services.last} last{/if}">
            <td class="service-type service-type-{if $serviceType}{$serviceType|escape}{else}unknown{/if}">{if $serviceType eq "sensor"}ДТ{elseif $serviceType eq "service"}СЛ{elseif $serviceType eq "button"}СК{else}--{/if}</td>
            <td>{if $dbService.communication_device.logical_number}{$dbService.communication_device.logical_number}/{/if}{$dbService.position}</td>
            <td>
                {if $dbService && (!isset($nolinks)|| !$nolinks)}<a href="{$smarty.const.SOVA_BASE_URL}service/index/id/{$host.host_id}:{$service.description|escape:"url"}" title="{$serviceName|escape}" class="serviceLink{if $dbService.type eq 'service'} service{/if}">{/if}<span id="service[{$host.host_id}:{$service.description|escape:"javascript"}]">{$serviceName}</span>{if $dbService && (!isset($nolinks) || !$nolinks)}</a>{/if}
            </td>
            {if $servicesHasAliasColumn}<td>{if $dbService.alias}{$dbService.alias|escape}{else}&nbsp;{/if}</td>{/if}
            <td>{$service.plugin_output|escape}</td>
            {*
            <td class="time">{$service.last_check|date_format:'%Y-%m-%d %H:%M:%S'}</td>
            <td class="time">{$service.last_state_change|date_format:'%Y-%m-%d %H:%M:%S'}</td>
            *}
            <td class="last">{if isset($service['state_duration'])}<span title="{$service.state_duration|human_interval}">{$service.state_duration|human_interval:true}</span>{else}&nbsp;{/if}</td>
        </tr>
        {assign var=_old_zone_id value=$dbService.zone_id}
        {foreachelse}

        {/foreach}
    </tbody>
</table>