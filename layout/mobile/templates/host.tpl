<script type="text/javascript">
    /* <![CDATA[ */
    hostId = '{$host.host_id}';
    hostMd5 = '{$host.nagios.md5}';
    logDayOffset = '{$dayOffset|default:-1}';

    $(document).bind('pageinit', function() {
        initHostAccordion(hostId);
        /*$hostTabs = initHostTabs(hostId);
        {if !isset($host.nagvis_thumb_url) || not $host.nagvis_thumb_url}$hostTabs.tabs('disable', 'map');{/if}

        {if !isset($host.scheme_image_name) || not $host.scheme_image_name}$hostTabs.tabs('disable', 'schemes');{/if}

        initServiceLinks();
    });

    //$(window).load(function() {
    $(document).bind('pageinit', function() {
        initNagvisMap();
    });
    /* ]]> */
</script>

<!-- {*$host|@var_dump*} -->
<div id="host{$host.host_id}" class="host">
    <div data-role="collapsible-set" data-collapsed-icon="arrow-r" data-expanded-icon="arrow-d" data-inset="false">
        <div data-role="collapsible" id="log">
            <h2>Журнал</h2>
            <table id="hostLog" class="grid log">
                <thead>
                <tr>
                    <th class="line-number nofilter">#</th>
                    <th class="time name-time">Час</th>
                    <th class="name-service_notes">Сервіс</th>
                    <th class="name-plugin_output">Повідомлення</th>
                </tr>
                </thead>
                <tbody>
                {include file="inc/log.tpl" logItems=$host.nagiosLog}
                </tbody>
            </table>
        </div>
        <div data-role="collapsible" id="details">
            <h2>Картка ПНО</h2>
                <label for="host_alias">Назва</label>
                <input type="text" id="host_alias" readonly="readonly" value="{$host.alias|escape}" data-mini="true"/>
                <label for="host_name">Повна назва</label>
                <input type="text" id="host_name" readonly="readonly" value="{$host.name|escape}" data-mini="true"/>
                <label for="host_location">Місце знаходження</label>
                <input type="text" id="host_location" readonly="readonly" value="{$host.location|escape}" data-mini="true"/>
                <label for="host_object_id">Унікальний код</label>
                <input type="text" id="host_object_id" readonly="readonly" value="{$host.object_id|escape}" data-mini="true"
                           title="Унікальний код ПНО, зареестрованного в даному ЦСС-ПНО, тобто номер передавального пристрою"/>
                <label for="host_description">Примітки</label>
                <textarea id="host_description" rows="7" cols="20"
                          readonly="readonly" data-mini="true">{$host.description}</textarea>
                <div data-role="collapsible-set" data-inset="true">
            {if $host.zones}
            <div data-role="collapsible" data-mini="true">
                <h3>Зони контролю та розміщення</h3>
                <table>
                    <tr>
                        <td>
                            <table class="grid">
                                <thead>
                                <tr>
                                    <th rowspan="2">Код</th>
                                    <th rowspan="2">Найменування</th>
                                    <th rowspan="2">Тип зони</th>
                                    {*
                                    <th colspan="2">Найближчий в'їзд</th>

                                </tr>
                                <tr>
                                    <th>Широта</th>
                                    <th>Довгота</th>
                                    *}
                                </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$host.zones item=item name=current}
                                    <tr class="{if $smarty.foreach.current.iteration % 2}odd{else}even{/if}{if $smarty.foreach.current.last} last{/if}{if $smarty.foreach.current.first} first{/if}">
                                        <td>{$item.plas_zone_id|escape|default:'&nbsp;'}</td>
                                        <td>{$item.name|escape|default:'&nbsp;'}</td>
                                        <td>{if $item.type eq "control"}Зона контролю
                                            {elseif $item.type eq "location"}Зона розміщення
                                            {else}&nbsp;
                                            {/if}</td>
                                        {*
                                        <td>{$item.entrance_position_longitude|escape|default:'&nbsp;'}</td>
                                        <td>{$item.entrance_position_latitude|escape|default:'&nbsp;'}</td>
                                        *}
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            {/if}
            {if $host.notification_devices}
            <div data-role="collapsible" data-mini="true">
                <h3>Зони трансляції</h3>
                <table>
                    <tr>
                        <td>
                            <table class="grid">
                                <thead>
                                <tr>
                                    <th>Код</th>
                                    <th>Пристрій контролю та оповіщення</th>
                                    <th>Зона трансляції</th>
                                </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$host.notification_devices item=item name=current}
                                    <tr class="{if $smarty.foreach.current.iteration % 2}odd{else}even{/if}{if $smarty.foreach.current.last} last{/if}{if $smarty.foreach.current.first} first{/if}">
                                        <td>{$item.zone_id|escape|default:'&nbsp;'}</td>
                                        <td>{$item.communication_device.name}</td>
                                        <td>{$item.name|escape|default:'&nbsp;'}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            {/if}
            {if $host.communication_devices}
            <div data-role="collapsible" data-mini="true">
                <h3>Пристрої контролю та сповіщення</h3>
                <table>
                    <tr>
                        <td>
                            <table class="grid">
                                <thead>
                                <tr>
                                    <th>Найменування</th>
                                    <th>Лог.№</th>
                                    <th>Зав.№</th>
                                    <th>Розміщення</th>
                                </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$host.communication_devices item=item name=current}
                                    <tr class="{if $smarty.foreach.current.iteration % 2}odd{else}even{/if}{if $smarty.foreach.current.last} last{/if}{if $smarty.foreach.current.first} first{/if}">
                                        <td>{$item.name|escape|default:'&nbsp;'}</td>
                                        <td>{$item.logical_number|escape|default:'&nbsp;'}</td>
                                        <td>{$item.serial_number|escape|default:'&nbsp;'}</td>
                                        <td>{$item.zone.name|escape|default:'&nbsp;'}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            {/if}
            {if $host.channels}
            <div data-role="collapsible" data-mini="true">
                <h3>Канали зв'язку</h3>
                <table>
                    <tr>
                        <td>
                            <table class="grid">
                                <thead>
                                <tr>
                                    <th>Найменування</th>
                                    <th>Вид</th>
                                    <th>IP адреса</th>
                                    <th>Номер сотового</th>
                                    <th>Примітки</th>
                                </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$host.channels item=item name=current}
                                    <tr class="{if $smarty.foreach.current.iteration % 2}odd{else}even{/if}{if $smarty.foreach.current.last} last{/if}{if $smarty.foreach.current.first} first{/if}">
                                        <td>{$item.name|escape|default:'&nbsp;'}</td>
                                        <td>{if $item.type eq 'cell'}Сотовий
                                            {elseif $item.type eq 'xdsl'}xDSL
                                            {elseif $item.type eq 'ethernet'}Ethernet
                                            {elseif $item.type eq 'other'}Інше
                                            {else}&nbsp;
                                        {/if}</td>
                                        <td>{$item.ip|escape|default:'&nbsp;'}</td>
                                        <td>{$item.cell_number|escape|default:'&nbsp;'}</td>
                                        <td>{$item.notes|escape|nl2br|default:'&nbsp;'}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            {/if}
                </div>
        {if $host['passport']}
            <label for="passport">Паспорт</label>
            <span id="passport"><a href="{$smarty.const.SOVA_BASE_URL}host/passport/id/{$host.nagios_name}" target="_blank">{$host.name}</a></span>
        {/if}
        </div>
        <div data-role="collapsible" id="contacts">
            <h2>Контакти</h2>
            <h3>Відповідальні юридичні особи</h3>
            <table class="grid contacts">
                <thead>
                <tr>
                    <th>Найменування</th>
                    <th>Ідентифікаційний код юридичної особи (за ЄДРПОУ)</th>
                    <th>Ідентифікаційний код фізичної особи (підприємця)</th>
                    <th>Місце знаходження</th>
                    <th>Телефон</th>
                    <th>Електронна адреса</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$host.companies item=company name="current"}
                <tr class="{if $smarty.foreach.current.iteration % 2}odd{else}even{/if}{if $smarty.foreach.current.last} last{/if}{if $smarty.foreach.current.first} first{/if}">
                    <td>{$company.name|escape}</td>
                    <td>{$company.edrpou|escape}</td>
                    <td>{$company.inn|escape}</td>
                    <td>{$company.address|escape}</td>
                    <td>{$company.phone|escape}</td>
                    <td>{$company.email|escape}</td>
                </tr>
                {/foreach}
                </tbody>
            </table>
            <h3>Відповідальні фізичні особи</h3>
            <table class="grid contacts">
                <thead>
                <tr>
                    <th>П.І.Б.</th>
                    <th>Посада</th>
                    <th>Телефон</th>
                    <th>Електронна адреса</th>
                </tr>
                </thead>
            {foreach from=$host.contacts item=contact name=current}
                <tr class="{if $smarty.foreach.current.iteration % 2}odd{else}even{/if}{if $smarty.foreach.current.last} last{/if}{if $smarty.foreach.current.first} first{/if}">
                    <td>{$contact.fullname|escape}</td>
                    <td>{$contact.function|escape}</td>
                    <td>{$contact.phone|escape}</td>
                    <td>{$contact.email|escape}</td>
                </tr>
            {/foreach}
            </table>
        </div>
        <div data-role="collapsible" id="services">
            <h3>Сервіси</h3>
            <div id="services-container">
            {include file="inc/services_table.tpl" host=$host}
            </div>
            <div id="serviceDetailsContainer"></div>
        </div>
    {if isset($host.nagvis_thumb_url) && $host.nagvis_thumb_url}
        <div data-role="collapsible" id="map">
            <h3>Схема</h3>
            {if $host.nagvis_map_config}
                <div id="nagvisServiceIconOverlay">
                {include file="inc/services_nagvis.tpl" host=$host}
                </div>
            {/if}
            {if $host.nagvis_map_url}<a href="{$host.nagvis_map_url}" class="host-map" target="hostMap{$host.host_id}">{/if}
            {if $host.nagvis_thumb_url}
                {assign var="imagefile" value={$smarty.const.NAGVIS_MAP_IMAGES_PATH}|cat:$host.nagvis_data.nagvis_image_name}
                <img src="{$smarty.const.SOVA_BASE_URL}{imagemodifier
                img=$imagefile chain="scheme_mobile" output="url"
                outputformat="png"
                }" alt="Схема об'єкта">
                {else}{if $host.nagvis_map_url}Інтерактивна схема{/if}{/if}
            {if $host.nagvis_map_url}</a>{/if}
        </div>
    {/if}
    {if $host.scheme_image_name}
        <div data-role="collapsible" id="schemes">
            <h2>Схема</h2>
            {include file="inc/host_schemes.tpl" host=$host}
        </div>
    {/if}
        {if isset($host.config_info) and $host.config_info}
        <div data-role="collapsible" id="configinfo">
            <h2>Завдання</h2>
            {$host.config_info}
        </div>
        {/if}
        {if isset($host.registry) and $host.registry}
        <div data-role="collapsible" id="registry">
            <h2>Реєстр</h2>
            {include file="inc/host_registry.tpl" items=$host.registry}
        </div>
        {/if}
    </div>
</div>
