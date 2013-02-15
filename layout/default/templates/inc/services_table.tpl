{assign var=nagiosServices value=$host.nagios.services}
<table class="grid services" id="services{$host.nagios.md5}">
    {*if !$isAjax*}
    <thead>
	<tr>
        <th class="nofilter">З/К</th>
	    <th class="nofilter">&nbsp;</th>
        <th><span>№</span></th>
        <th><span>Тип</span></th>
        <th><span>Назва</span></th>
	    <th class="state"><span>Стан</span></th>
	    <th><span>Повідомлення</span></th>
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
            
        <tr onmouseover="highlightZone(this, true)" onmouseout="highlightZone(this, false)"
	    id="service{$service.md5}"
        class="state{$service.state}
        {if $smarty.foreach.services.first} first{elseif $smarty.foreach.services.last} last{/if}
        zone-row-id-{$dbService.zone.zone_id}{if $zone_row_type == "new"}
        zone-row-start{elseif $zone_row_type == "continue"} zone-row-continue{/if}
        {if $dbService.communication_device_id}zone-row-device-{$dbService.communication_device_id|escape}{/if}
        ">
            {if $zone_row_type == "new"}
                <td class="zone-header first zone-id" title="{$dbService.zone.name|escape}">{$dbService.zone.name|escape}</td>
            {elseif $zone_row_type == "continue"}
                <td class="zone-continue first" title="{$dbService.zone.name|escape}">-//-</td>
            {else}
                <td class="zone-header-empty first">&mdash;</td>
            {/if}
            <td class="service-type service-type-{if $serviceType}{$serviceType|escape}{else}unknown{/if}">{if $serviceType eq "sensor"}ДТ{elseif $serviceType eq "service"}СЛ{elseif $serviceType eq "button"}СК{else}--{/if}</td>
            <td>{if $dbService.communication_device.logical_number}{$dbService.communication_device.logical_number}/{/if}{$dbService.position}</td>
            <td>
                {if $dbService && (!isset($nolinks)|| !$nolinks)}<a href="{$smarty.const.SOVA_BASE_URL}service/index/id/{$host.host_id}:{$service.description|escape:"url"}" class="serviceLink{if $dbService.type eq 'service'} service{/if}">{/if}{$serviceName}{if $dbService && (!isset($nolinks) || !$nolinks)}</a>{/if}
            </td>
            <td>{if $dbService.alias}{$dbService.alias|escape}{else}&nbsp;{/if}</td>
            <td class="state bgServiceState{$service.state}">{$service.state_text}
        	    <div class="serviceImages">
                    <img class="imgAcknowleged{if $service.acknowledged != 1} hidden{/if}" src="{$smarty.const.LAYOUT_IMAGES_URL}ack.gif" alt="ОБР" title="Сервіс в обробці"/>
                    <img class="imgComments{if not $service.comments} hidden{/if}" src="{$smarty.const.LAYOUT_IMAGES_URL}comment.gif" alt="КОМ" title="Коментарі: {$service.comments|@count}"/>
                    <img class="imgFlapping{if not $service.is_flapping} hidden{/if}" src="{$smarty.const.LAYOUT_IMAGES_URL}flapping.gif" alt="МЕР" title="Мерехтіння"/>
                </div>
            </td>
            <td>{$service.plugin_output|escape}</td>
            <td class="last">{if isset($service['state_duration'])}<span title="{$service.state_duration|human_interval}">{$service.state_duration|human_interval:true}</span>{else}&nbsp;{/if}</td>
        </tr>
        {assign var=_old_zone_id value=$dbService.zone_id}
        {foreachelse}

        {/foreach}
    </tbody>
</table>