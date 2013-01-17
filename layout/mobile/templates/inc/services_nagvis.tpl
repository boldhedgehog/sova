{assign var="nagvisServices" value=$host.nagvis_map_config}
{assign var="services" value=$host.nagios.services}
{if $nagvisServices}
    <script type="text/javascript">
    //<![CDATA[
    nagvisServices = {$nagvisServices|@json_encode};
    //]]>
    </script>
    {foreach from=$nagvisServices item=service key=service_key}
    <!-- {*$service|@var_dump*} -->
        {assign var="nagios_service" value=$services[$service.md5]}
        {if $nagios_service}
            {assign var=dbService value=$nagios_service.db_data}
            {if $dbService}
                {if $dbService.sensor_type.name}
                    {assign var=serviceName value=$dbService.position|cat:":"|cat:$dbService.sensor_type.name}
                {else}
                    {assign var=serviceName value=$dbService.alias}
                {/if}
            {else}
                {if $service.notes_expanded}
                {assign var=serviceName value=$nagios_service.notes_expanded}
                {else}
                {assign var=serviceName value=$nagios_service.display_name}
                {/if}
            {/if}
            <div id="nagvisServiceIcon{$service_key}" class="nagvisServiceIcon nagvisServiceIcon{$nagios_service.state}" style="top:{$service.y}px;left:{$service.x}px">
                {if $dbService && (!isset($nolinks) || !$nolinks)}
                <a href="{$smarty.const.SOVA_BASE_URL}service/index/id/{$host.host_id}:{$nagios_service.description|escape:"url"}" class="serviceLink">
                {/if}
                <img src="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/images/icons/service{$nagios_service.state}.png" alt="{$nagios_service.notes_expanded}" class="serviceIcon"/>
                {if $dbService && (!isset($nolinks) || !$nolinks)}</a>{/if}
                <div class="serviceFloat">
                <!-- {*$nagios_service|@var_dump*} -->
                <table cellspacing="0" cellpadding="1" border="1">
                    <tr><th colspan="2">{$serviceName}</th></tr>
                    <tr><td class="caption caption{$nagios_service.state}">Стан</td><td class="bgServiceState{$nagios_service.state}">{$nagios_service.state_text}</td></tr>
                    <tr><td class="caption">Останнє оновлення</td><td>{$nagios_service.last_check|date_format:'%Y-%m-%d %H:%M:%S'}</td></tr>
                    <tr><td class="caption">Остання зміна статусу</td><td>{$nagios_service.last_state_change|date_format:'%Y-%m-%d %H:%M:%S'}</td></tr>
                        <tr><td class="caption">Тривалість</td><td>{if $nagios_service.state_duration}{$nagios_service.state_duration|human_interval}{/if}</td></tr>
                    <tr><td class="caption">Повідомлення</td><td>{$nagios_service.plugin_output}</td></tr>
                </table>
                </div>
                <div class="position">{$dbService.position}</div>
            </div>
        {/if}
    {/foreach}
{/if}
