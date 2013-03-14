{if $host.zones && isset($host['has_geo']) && $host.has_geo}
<script type="text/javascript">

    var myMap = null;
    function initYMap() {
        var cluster = new ymaps.Clusterer();

        var center = null;

        {foreach from=$host.zones item=item name=current}
            {if $item.entrance_position_longitude && $item.entrance_position_latitude}
                center = [{$item.entrance_position_latitude}, {$item.entrance_position_longitude}];
                cluster.add( new ymaps.GeoObject({
                    geometry: {
                        type: "Point",
                        coordinates: [{$item.entrance_position_latitude}, {$item.entrance_position_longitude}]
                    },
                    properties: {
                        hintContent: "{$item.name}"
                    }

                })
                );
            {/if}
        {/foreach}

        if (center == null) {
            return;
        }

        myMap = new ymaps.Map("YMapsID", {
            center: center,
            // building
            zoom: 16,
            type: 'yandex#publicMapHybrid',
            {if $smarty.const.DEFAULT_LAYOUT_NAME eq "mobile"}
            {else}
            behaviors: ["drag", "dblClickZoom", "scrollZoom"]
            {/if}
        });

        myMap.controls.add('zoomControl');
        myMap.controls.add('typeSelector');
        myMap.controls.add('mapTools');
        myMap.controls.add('scaleLine');

        myMap.geoObjects.add(cluster);

        //initTabs();
    }
</script>

<script src="//api-maps.yandex.ru/2.0-stable/?load=package.standard,package.geoObjects,package.clusters&lang=uk-UA&onload=initYMap" type="text/javascript"></script>
{/if}

<script type="text/javascript">
    /* <![CDATA[ */
    hostId = '{$host.host_id}';
    hostMd5 = '{$host.nagios.md5}';
    logDayOffset = '{$dayOffset|default:-1}';

    $(document).ready(function(){

        nagiosWatcher.data.hostId = hostId;

        nagiosWatcher.onRefresh = nagiosWatcher.onRefreshHost;

        initTabs();

        $hostTabs.bind('tabsshow', function (event, ui) {
            if (typeof(myMap) != 'undefined') {
                myMap.container.fitToViewport();
            }
        });

        initServiceLinks();
        //initTableSearch('table.log', { searchFunction: xajax_{$controllerName}.xajaxGetLogRows });
        initTableSearch('table.log', { searchFunction: nagiosWatcher.getHostLogRows });
        initTableFilter('table.services');
    });

    function initTabs() {
        $hostTabs = initHostTabs(hostId);
        {if !isset($host.nagvis_thumb_url) || not $host.nagvis_thumb_url}$hostTabs.tabs('disable', 'map');{/if}

        {if !isset($host.scheme_image_name) || not $host.scheme_image_name}$hostTabs.tabs('disable', 'schemes');{/if}

        {if !($host.zones && isset($host['has_geo']) && $host.has_geo)}$hostTabs.tabs('disable', 'ymap');{/if}

        {if !isset($host.config_info) || not $host.config_info}$hostTabs.tabs('disable', 'configinfo');{/if}
    }

    //$(window).load(function() {
    $(document).ready(function() {
        initNagvisMap();
    });
    /* ]]> */
</script>
<!-- {*include file="inc/host_log_chart.tpl"*} -->
<div id="host{$host.host_id}" class="host">
    <div class="tabs">
        <ul>
            <li><a href="#log">Журнал</a></li>
            <li><a href="#details">Картка ПНО</a></li>
            <li><a href="#contacts">Контакти</a></li>
            <li><a href="#services">Сервіси</a></li>
            <li><a href="#map">Схема</a></li>
            <li><a href="#schemes">Галерея</a></li>
            <li><a href="#ymap">Мапа</a></li>
            <li><a href="#configinfo">Завдання</a></li>
        </ul>
        <div class="details" id="details">
            <table>
                <tr>
                    <td><label for="host_alias">Назва</label></td>
                </tr>
                <tr>
                    <td><input type="text" id="host_alias" readonly="readonly" value="{$host.alias|escape}"/></td>
                </tr>
                <tr>
                    <td><label for="host_name">Повна назва</label></td>
                </tr>
                <tr>
                    <td><input type="text" id="host_name" readonly="readonly" value="{$host.name|escape}"/></td>
                </tr>
                <tr>
                    <td><label for="host_location">Місце знаходження</label></td>
                </tr>
                <tr>
                    <td><input type="text" id="host_location" readonly="readonly" value="{$host.location|escape}"/></td>
                </tr>
                <tr>
                    <td><label for="host_object_id">Унікальний код</label></td>
                </tr>
                <tr>
                    <td><input type="text" id="host_object_id" readonly="readonly" value="{$host.object_id|escape}"
                               title="Унікальний код ПНО, зареестрованного в даному ЦСС-ПНО, тобто номер передавального пристрою"/>
                    </td>
                </tr>
                {*
                <tr>
                    <td><label for="host_company">Суб'єкт господарскої діяльності</label></td>
                </tr>
                <tr>
                    <td><input type="text" id="host_company" readonly="readonly"
                               value="{$host.companies[0].name|escape}"/></td>
                </tr>
                *}
            </table>
        {if $host.zones}
            <table>
                <tr>
                    <td><h3>Зони контролю та розміщення</h3></td>
                </tr>
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
        {/if}
        {if $host.notification_devices}
            <table>
                <tr>
                    <td><h3>Зони трансляції</h3></td>
                </tr>
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
        {/if}
        {if $host.communication_devices}
            <table>
                <tr>
                    <td><h3>Пристрої контролю та сповіщення</h3></td>
                </tr>
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
        {/if}
        {if $host.channels}
            <table>
                <tr>
                    <td><h3>Канали зв'язку</h3></td>
                </tr>
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
                                    <td>{if isset($item['cell_number']['number'])}{$item.cell_number.number|escape|default:'&nbsp;'}{else}&nbsp;{/if}</td>
                                    <td>{$item.notes|escape|nl2br|default:'&nbsp;'}</td>
                                </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        {/if}
            <table>
                <tr>
                    <td><label for="host_description">Примітки</label></td>
                </tr>
                <tr>
                    <td><textarea id="host_description" rows="7" cols="20"
                                  readonly="readonly">{$host.description}</textarea></td>
                </tr>
            {if $host['config_info']}
                <tr>
                    <td><label for="config_info">Завдання</label></td>
                </tr>
                <tr>
                    <td><div id="config_info" class="textarea ui-resizable">{$host['config_info']}</div></td>
                </tr>
            {/if}
            {if $host['passport']}
                <tr>
                    <td><label for="passport">Паспорт</label></td>
                </tr>
                <tr>
                    <td><span id="passport"><a href="{$smarty.const.SOVA_BASE_URL}host/passport/id/{$host.nagios_name}" target="_blank">{$host.name}</a></span></td>
                </tr>
            {/if}
            </table>
        </div>
        <div class="details" id="contacts">
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
        <div class="details" id="services">
            <div id="services-container">
            {include file="inc/services_table.tpl" host=$host}
            </div>
            <div id="serviceDetailsContainer"></div>
        </div>
    {if isset($host.nagvis_thumb_url) && $host.nagvis_thumb_url}
        <div class="details" id="map">
            {if $host.nagvis_map_config}
                {include file="inc/services_nagvis.tpl" host=$host}
            {/if}
            {if $host.nagvis_map_url}<a href="{$host.nagvis_map_url}" class="host-map" target="hostMap{$host.host_id}">{/if}
            {if $host.nagvis_thumb_url}<img class="host-map" src="{$host.nagvis_thumb_url}"
                                            alt="Схема об'єкта"/>{else}{if $host.nagvis_map_url}Інтерактивна схема{/if}{/if}
            {if $host.nagvis_map_url}</a>{/if}
        </div>
    {/if}
    {if $host.scheme_image_name}
        <div class="details" id="schemes">
            {include file="inc/host_schemes.tpl" host=$host}
        </div>
    {/if}
        <div class="details" id="log">
            <table id="hostLog" class="grid log">
                <thead>
                <tr>
                    <th class="line-number nofilter">#</th>
                    <th class="time name-time">Час</th>
                    <th class="name-service_notes">Сервіс</th>
                    <th class="state name-state">Стан</th>
                    <th class="name-plugin_output">Повідомлення</th>
                </tr>
                </thead>
                <tbody>
                {include file="inc/log.tpl" logItems=$host.nagiosLog}
                </tbody>
            </table>
        {*<input id="previous-log" type="button" value="більше ..." onclick="xajax_{$controllerName}.xajaxGetLogRows(logDayOffset, 1);" class="next-button" />*}
            <table>
                <tr>
                    <td colspan="5">
                        <div id="chart_div"></div>
                    </td>
                </tr>
            </table>
            <span class="please-wait" id="log-please-wait" style="display:none;">
                <img src="{$smarty.const.LAYOUT_IMAGES_URL}ajax-loader.gif" alt="Зачекайте, будь ласка ..." title="Зачекайте, будь ласка ..." class="v-middle" /> Зачекайте, будь ласка ...
            </span>
        </div>
        <div class="details" id="ymap">
            <div id="YMapsID" style="height: 600px"></div>
        </div>
        <div class="details" id="configinfo">
            <div>
            {$host.config_info}
            </div>
        </div>
    </div>
</div>
