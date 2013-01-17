<script type="text/javascript">
</script>

<form id="formAlert" class="alert" name="alert" method="post" action="">
    <input type="hidden" name="status" value=""/>
    <div id="alertForm{$alert.alert_id}" class="alertForm">
        {assign var=host value=$alert.host}
        <div id="dhtmlxContainer" style="position: relative; width: 100%; height: 100%;"></div>
        <div class="details" id="processArea">
            <h2>Обробка</h2>
            <table id="quickContacts">
{foreach from=$host.contacts item=contact}
                <tr>
                    <td><input type="text" id="quick_contact_name_{$contact.contact_id}" readonly="readonly" value="{$contact.fullname|escape}"/></td>
                    <td><input type="text" id="quick_contact_phone_{$contact.contact_id}" readonly="readonly" value="{$contact.phone|escape}" class="phone"/></td>
                </tr>
{/foreach}
            </table>
            <table>
                <tr><td><label for="operator">Оператор</label></td></tr>
                <tr><td><input type="text" id="operator" readonly="readonly" value="{$alert.operator.name|escape}"/></td></tr>
                <tr><td><label for="timestamp">Дата/час надходження тривоги</label></td></tr>
                <tr><td><input type="text" id="timestamp" readonly="readonly" value="{$alert.timestamp|date_format:'%Y-%m-%d %H:%M:%S'}"/></td></tr>
                <tr><td><label for="message">Повідомлення від датчика ({$alert.service.alias|escape})</label></td></tr>
                <tr><td><input type="text" id="message" readonly="readonly" value="{$alert.message|escape}" class="bgServiceState{$alert.state}"/></td></tr>
                <tr><td><label for="alert_status">Статус тривоги</label></td></tr>
                <tr><td><select id="alert_status" disabled="disabled">
	{if $alert.status eq 0}<option selected="selected">Нова</option>{/if}
	{if $alert.status eq 1}<option selected="selected">Обробляється</option>{/if}
	{if $alert.status eq 2}<option selected="selected">Оброблена</option>{/if}
                        </select></td></tr>
                <tr><td><label for="alert_type">Тип тривоги</label></td></tr>
                <tr><td><select id="alert_type" name="type"{if $readonly} disabled="disabled"{/if}>
                            <option value="0"{if $data.type eq 0} selected="selected"{/if}>Тестова</option>
                            <option value="1"{if $data.type eq 1} selected="selected"{/if}>Учбова</option>
                            <option value="2"{if $data.type eq 2} selected="selected"{/if}>Бойова</option>
                        </select>{*&nbsp;<span class="hint">Боевая тревога отправится на обработку в МЧС</span>*}</td></tr>
                {if !$readonly}
		<tr><td><label for="alert_notes">Примітки оператора</label><span class="required">*</span></td></tr>
                <tr><td><textarea id="alert_notes" name="notes" class="required">{$data.notes}</textarea>
                        <span class="formError"></span></td></tr>
                <tr><td class="spacer">&nbsp;</td></tr>
                <tr><td class="button">
			<input type="submit" value="Підтвердити" class="button" />
			<input type="submit" value="Відмінити" class="button" />
			<input type="submit" value="Відкласти" class="button" />
		</td></tr>
		{/if}
            </table>
        </div>
        <div class="details" id="alertCard">
            <h2>Картка {if $alert.cards|@count > 1}аварій{else}аварії{/if}</h2>
{foreach from=$alert.cards item=alert_card}
            <table>
                <tr><td><label for="card_name{$alert_card.alert_card_id}">Найменування технологічного процесу</label></td></tr>
                <tr><td><input type="text" id="card_name{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.name|escape}"/></td></tr>
                <tr><td><label for="card_emergency_type{$alert_card.alert_card_id}">Вид небезпеки згідно Методики ідентифікації ПНО додаток 3</label></td></tr>
                <tr><td><input type="text" id="card_emergency_type{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.emergency_type|escape}"/></td></tr>
                <tr><td><label for="card_emergency_code{$alert_card.alert_card_id}">Код можливих НС згідно Методики ідентифікації ПНО додаток 1</label></td></tr>
                <tr><td><input type="text" id="card_emergency_code{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.emergency_code|escape}"/></td></tr>
                <tr><td><label for="card_sensor_number{$alert_card.alert_card_id}">Датчик, що контролює процес</label></td></tr>
                <tr><td><input type="text" id="card_sensor_number{$alert_card.alert_card_id}" readonly="readonly" value="{$alert.service.alias|escape}"/></td></tr>
                <tr><td><label for="card_affect_area{$alert_card.alert_card_id}"></label>Зона можливого ураження (радіус, метрів, граничні ознаки ураження)</td></tr>
                <tr><td><input type="text" id="card_affect_area{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.affect_area|escape}"/></td></tr>
                <tr><td><label for="card_emergency_level{$alert_card.alert_card_id}"></label>Рівень можливих НС згідно Методики ідентифікації ПНО додаток 4</td></tr>
                <tr><td><input type="text" id="card_emergency_level{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.emergency_level|escape}"/></td></tr>
                <tr><td><label for="card_emergency_level2{$alert_card.alert_card_id}"></label>Рівень можливих НС згідно НПАОП 0.00-4.33-99</td></tr>
                <tr><td><input type="text" id="card_emergency_level2{$alert_card.alert_card_id}" readonly="readonly" value="{$alert_card.emergency_level2|escape}"/></td></tr>
                <tr><td><label for="card_scenarion{$alert_card.alert_card_id}">Сценарій аварії</label></td></tr>
                <tr><td><div id="card_scenario{$alert_card.alert_card_id}" class="textarea">{$alert_card.emergency_scenario|nl2br}</div></td></tr>
            </table>
{/foreach}
        </div>
        <div class="details" id="details">
            <h2>Картка ПНО</h2>
            <table>
                <tr><td><label for="host_name">Повна назва ПНО</label></td></tr>
                <tr><td><input type="text" id="host_name" readonly="readonly" value="{$host.name|escape}"/></td></tr>
                <tr><td><label for="host_location">Місце знаходження ПНО</label></td></tr>
                <tr><td><input type="text" id="host_location" readonly="readonly" value="{$host.location|escape}"/></td></tr>
                <tr><td><label for="host_edrpou">Ідентифікаційний код  юридичної особи (за ЄДРПОУ)</label></td></tr>
                <tr><td><input type="text" id="host_edrpou" readonly="readonly" value="{$host.edrpou|escape}"/></td></tr>
                <tr><td><label for="host_inn">Ідентифікаційний код фізичної особи  (підприємця)</label></td></tr>
                <tr><td><input type="text" id="host_inn" readonly="readonly" value="{$host.inn|escape}"/></td></tr>
                <tr><td><label for="host_dk009_96">Код виду економічної діяльності за ДК 009-96</label></td></tr>
                <tr><td><input type="text" id="host_dk009_96" readonly="readonly" value="{$host.dk009_96|escape}"/></td></tr>
                <tr><td><label for="host_dk002_2004">Форма господарювання за ДК 002:2004</label></td></tr>
                <tr><td><input type="text" id="host_dk002_2004" readonly="readonly" value="{$host.dk002_2004|escape}"/></td></tr>
                <tr><td><label for="host_central_authority">Центральний орган виконавчої влади</label></td></tr>
                <tr><td><input type="text" id="host_central_authority" readonly="readonly" value="{$host.central_authority|escape}"/></td></tr>
                <tr><td><label for="host_local_authority">Місцевий орган виконавчої влади</label></td></tr>
                <tr><td><input type="text" id="host_local_authority" readonly="readonly" value="{$host.local_authority|escape}"/></td></tr>
                <tr><td><label for="host_description">Примітки</label></td></tr>
                <tr><td><textarea id="host_description" rows="7" cols="20" readonly="readonly">{$host.description}</textarea></td></tr>
            </table>
        </div>
        <div class="details" id="contacts">
            <h2>Контакти</h2>
            <table class="contacts">
                <tr><td><label for="host_contact_legal">Відповідальна (юридична) особа ПНО</label></td></tr>
                <tr><td><input type="text" id="host_contact_legal" readonly="readonly" value="{$host.contact_legal.fullname|escape}"/></td></tr>
                <tr><td><label for="host_contact_legal_address">Місце знаходження юридичної особи (адреса, поштовий індекс)</label></td></tr>
                <tr><td><input type="text" id="host_contact_legal_address" readonly="readonly" value="{$host.contact_legal.address|escape}"/></td></tr>
                <tr><td><label for="host_contact_legal_phone">Телефон</label></td></tr>
                <tr><td><input type="text" id="host_contact_legal_phone" readonly="readonly" value="{$host.contact_legal.phone|escape}"/></td></tr>
                <tr><td><label for="host_contact_legal_email">Електронна адреса</label></td></tr>
                <tr><td><input type="text" id="host_contact_legal_email" readonly="readonly" value="{$host.contact_legal.email|escape}"/></td></tr>
        {foreach from=$host.contacts item=contact}
                <tr><td class="spacer">&nbsp;</td></tr>
                <tr><td><label for="host_contact_name_{$contact.contact_id}">{$contact.function|capitalize|escape} (П.І.Б.)</label></td></tr>
                <tr><td><input type="text" id="host_contact_name_{$contact.contact_id}" readonly="readonly" value="{$contact.fullname|escape}"/></td></tr>
                <tr><td><label for="host_contact_phone_{$contact.contact_id}">Телефон</label></td></tr>
                <tr><td><input type="text" id="host_contact_phone_{$contact.contact_id}" readonly="readonly" value="{$contact.phone|escape}"/></td></tr>
                <tr><td><label for="host_contact_email_{$contact.contact_id}">Електронна адреса</label></td></tr>
                <tr><td><input type="text" id="host_contact_email_{$contact.contact_id}" readonly="readonly" value="{$contact.email|escape}"/></td></tr>
        {/foreach}
            </table>
        </div>
        <div class="details" id="services">
            <h2>Датчики</h2>
	    <h3>На час виникнення тривоги</h3>
	    {include file="inc/services.tpl" services=$alert.services_data|unserialize nolinks=1}
	    <h3>Зараз</h3>
	    {include file="inc/services.tpl" services=$host.nagios.services}
        </div>
        <div class="details" id="map">
            <h2>Схема</h2>
	    {if $host.nagvis_map_config}
	    {include file="inc/services_nagvis.tpl" host=$host}
	    {/if}
    {if $host.nagvis_map_url}<a href="{$host.nagvis_map_url}" target="hostMap{$host.host_id}">{/if}
    {if $host.nagvis_thumb_url}<img class="hostMap" src="{$host.nagvis_thumb_url}" alt="Інтерактивна схема" />{else}{if $host.nagvis_map_url}Інтерактивна схема{/if}{/if}
    {if $host.nagvis_map_url}</a>{/if}
        </div>
    </div>
</form>