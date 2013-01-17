{if isset($service['sensor_type']) && $service.sensor_type.name}
    {assign var=serviceName value=$service.position|cat:'/'|cat:$service.communication_device.logical_number|cat:':'|cat:$service.sensor_type.name|cat:' '|cat:$service.alias}
{else}
    {assign var=serviceName value=$service.alias}
{/if}
<script type="text/javascript">
//<![CDATA[
        {if !isset($isAjax) || not $isAjax}
hostId = "{$host.host_id}";
serviceId = "{$service.service_id}";
hostUrl = BASE_URL + "host/index/id/" + "{$host.nagios_name}";

$(document).bind('pageinit', function() {
    /*$serviceTabs = initServiceTabs(serviceId);
    {if !isset($host.nagvis_thumb_url) || not $host.nagvis_thumb_url}$serviceTabs.tabs("disable", "map");{/if}
    {if !isset($host.scheme_image_name) || not $host.scheme_image_name}$serviceTabs.tabs("disable", "schemes");{/if}
    //$serviceTabs.tabs("option", "selected", 4);
    $serviceTabs.tabs("select", 4);*/
})
{/if}
    //]]>
</script>
    <div id="service{$service.service_id}" class="service">
    <form name="service" action="" class="alert" >
        <div class="service-tabs ui-tabs-hide">
            <ul>
                <li><a href="#log">Журнал</a></li>
                <li><a href="#details">Картка ПНО</a></li>
                <li><a href="#contacts">Контакти</a></li>
                <li><a href="#services">Сервіси</a></li>
                <li><a href="#service">{$service.name|escape}</a></li>
                <li><a href="#map">Інтерактивна схема</a></li>
                <li><a href="#schemes">Схеми</a></li>
            </ul>
            <div id="log"></div>
            <div id="host"></div>
            <div id="contacts"></div>
            <div id="services"></div>
            <div id="map"></div>
            <div id="schemes"></div>
            <div class="details" id="service">
                {*if $service.type eq 'service' or not $service.cards*}
                {assign var="serviceType" value=$service.type}
                {assign var="nagios" value=$service.nagios[0]}
                <table>
                    <tr><td><h3>Картка сервісу ({$service.name|escape})</h3></td></tr>
                    <tr><td><label for="service_alias{$service.service_id}">Назва</label></td></tr>
                    <tr><td><input type="text" id="service_alias{$service.service_id}" readonly="readonly" value="{$service.alias|escape}"/></td></tr>

                    <tr><td><label for="last_check{$service.service_id}">Останнє оновлення</label></td></tr>
                    <tr><td><input type="text" id="last_check{$service.service_id}" readonly="readonly" value="{$nagios.last_check|date_format:'%Y-%m-%d %H:%M:%S'}"/></td></tr>
                    <tr>
                        <td><label for="last_state_change{$service.service_id}">Остання зміна статусу</label></td>
                    </tr>
                    <tr>
                        <td><input type="text" id="last_state_change{$service.service_id}" readonly="readonly"
                                    value="{$nagios.last_state_change|date_format:'%Y-%m-%d %H:%M:%S'}"/></td>
                    </tr>
                    <tr>
                        <td><label for="state_duration{$service.service_id}">Тривалість</label>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="text" id="state_duration{$service.service_id}" readonly="readonly"
                                    value="{if $nagios.state_duration}{$nagios.state_duration|human_interval}{else}&nbsp;{/if}"/>
                        </td>
                    </tr>
                    <tr><td><label for="service_com_device{$service.service_id}">Пристрій контролю та сповіщення</label></td></tr>
                    <tr><td><input type="text" id="service_com_device{$service.service_id}" readonly="readonly" value="{$service.communication_device.name|escape}"/></td></tr>
                    <tr><td><label for="service_type{$service.service_id}">Тип сервісу</label></td></tr>
                    <tr><td><input type="text" id="service_type{$service.service_id}" readonly="readonly" value="{if $serviceType eq "sensor"}Датчик{elseif $serviceType eq "service"}Службовий{elseif $serviceType eq "button"}Сухий Контакт{else}--{/if}"/></td></tr>
                    <tr><td><label for="sensor_type{$service.service_id}">Тип датчика</label></td></tr>
                    <tr><td><input type="text" id="sensor_type{$service.service_id}" readonly="readonly" value="{$service.sensor_type.name|escape}"/></td></tr>

                    <tr><td><label for="serial_number{$service.service_id}">Зав.№</label></td></tr>
                    <tr><td><input type="text" id="serial_number{$service.service_id}" readonly="readonly" value="{$service.serial_number|escape}"/></td></tr>

                    <tr><td><label for="logical_number{$service.service_id}">Лог.№</label></td></tr>
                    <tr><td><input type="text" id="logical_number{$service.service_id}" readonly="readonly" value="{$service.logical_number|escape}"/></td></tr>

                    <!-- Зона контролю -->
                    <tr><td><label for="zone_name{$service.service_id}">Зона контролю</label></td></tr>
                    <tr><td><input type="text" id="zone_name{$service.service_id}" readonly="readonly" value="{$service.zone.name|escape}"/></td></tr>

                    <tr>
                        <td><label for="description{$service.service_id}">Примітки</label></td>
                    </tr>
                    <tr>
                        <td><textarea id="description{$service.service_id}" rows="7" cols="20"
                                        readonly="readonly">{$service.description}</textarea></td>
                    </tr>
                </table>
                {if $service.cards}
                {foreach from=$service.cards item=alert_card}
                    <table>
                        <tr><td><h3>Картка {if $service.cards|@count > 1}аварій{else}аварії{/if}</h3></td></tr>
                        <tr><td><label for="card_name{$alert_card.alert_card_id}">Назва</label></td></tr>
                        <tr><td><input type="text" id="card_name{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.name|escape}"/></td></tr>
                        <tr><td><label for="card_emergency_type{$alert_card.alert_card_id}">Вид небезпеки згідно Методики ідентифікації ПНО додаток 3</label></td></tr>
                        <tr><td><input type="text" id="card_emergency_type{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.emergency_type|escape}"/></td></tr>
                        <tr><td><label for="card_emergency_code{$alert_card.alert_card_id}">Код можливих НС згідно Методики ідентифікації ПНО додаток 1</label></td></tr>
                        <tr><td><input type="text" id="card_emergency_code{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.emergency_code|escape}"/></td></tr>
                        <tr><td><label for="card_sensor_number{$alert_card.alert_card_id}">Датчик, що контролює процес</label></td></tr>
                        <tr><td><input type="text" id="card_sensor_number{$alert_card.alert_card_id}" readonly="readonly" value="{$service.alias|escape}"/></td></tr>
                        <tr><td><label for="service_plugin_output{$alert_card.alert_card_id}">Повідомлення датчика</label></td></tr>
                        <tr><td><input type="text" id="service_plugin_output{$alert_card.alert_card_id}" readonly="readonly" value="{$service['nagios'][0]['plugin_output']|escape}"/></td></tr>
                        <tr><td><label for="card_affect_area{$alert_card.alert_card_id}">Зона можливого ураження (радіус, метрів, граничні ознаки ураження</label>)</td></tr>
                        <tr><td><input type="text" id="card_affect_area{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.affect_area|escape}"/></td></tr>
                        <tr><td><label for="card_emergency_level{$alert_card.alert_card_id}">Рівень можливих НС згідно Методики ідентифікації ПНО додаток 4</label></td></tr>
                        <tr><td><input type="text" id="card_emergency_level{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.emergency_level|escape}"/></td></tr>
                        <tr><td><label for="card_emergency_level2{$alert_card.alert_card_id}">Рівень можливих НС згідно НПАОП 0.00-4.33-99</label></td></tr>
                        <tr><td><input type="text" id="card_emergency_level2{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.emergency_level2|escape}"/></td></tr>
                        <tr><td><label for="card_scenarion{$alert_card.alert_card_id}">Сценарій аварії</label></td></tr>
                        <tr><td><div id="card_scenario{$alert_card.alert_card_id}" class="textarea">{$alert_card.emergency_scenario|nl2br}</div></td></tr>
                    </table>
                {/foreach}

                {foreach from=$service.cards item=alert_card}
                    <table>
                        <!-- Зона контролю -->
                        <tr><td><label for="alert_card_zone_name{$alert_card.alert_card_id}">Зона контролю</label></td></tr>
                        <tr><td><input type="text" id="alert_card_zone_name{$alert_card.alert_card_id}" readonly="readonly" value="{$service.zone.name|escape}"/></td></tr>
                        <!-- Картка -->
                        <tr><td><label for="card_detector_id{$alert_card.alert_card_id}">Унікальний код датчика в даній зоні контролю</label></td></tr>
                        <tr><td><input type="text" id="card_detector_id{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.detector_id|escape}"/></td></tr>
                        <tr><td><label for="card_detector_category_id{$alert_card.alert_card_id}">Категорія датчика</label></td></tr>
                        <tr><td><input type="text" id="card_detector_category_id{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.detector_category_id|escape}" title="Категорія датчика, відповідно до з наказом № 288, п. 7.5.1.2 (див. «Датчики контролю»), тобто від 1 до 6"/></td></tr>
                        <tr><td><label for="card_controlled_agent{$alert_card.alert_card_id}">Назва контрольованої речовини</label></td></tr>
                        <tr><td><input type="text" id="card_controlled_agent{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.controlled_agent|escape}"/></td></tr>
                        <tr><td><label for="card_agent_quantity_unit{$alert_card.alert_card_id}">Одиниця вимірювання контрольованої речовини</label></td></tr>
                        <tr><td><input type="text" id="card_agent_quantity_unit{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.agent_quantity_unit|escape}" title="Одиниця вимірювання контрольованої речовини (відповідно до «Класифікатором системі позначень одиниць вімірювання та обліку ДК 011-96» від 01.07.97 р"/></td></tr>
                        <tr><td><label for="card_controlled_agent_quantity{$alert_card.alert_card_id}">Кількість контрольованої речовини</label></td></tr>
                        <tr><td><input type="text" id="card_controlled_agent_quantity{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.controlled_agent_quantity|escape}"/></td></tr>
                    </table>
                {/foreach}
                {/if}
            </div>
        </div>
    </form>
</div>
