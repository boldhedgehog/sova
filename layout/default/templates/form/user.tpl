<div id="user-details">
    <fieldset>
        <legend><h2>Користувач</h2></legend>
    <table>
        <tr>
            <td><label for="operator:name">Ім'я</label></td>
        </tr>
        <tr>
            <td><input type="text" id="operator:name" readonly="readonly" value="{$operator.name|escape}"/></td>
        </tr>
        <tr>
            <td><label for="operator:nagios_name">Логін</label></td>
        </tr>
        <tr>
            <td><input type="text" id="operator:nagios_name" readonly="readonly" value="{$operator.nagios_name|escape}"/></td>
        </tr>
        <tr>
            <td><label for="operator:email">E-mail</label></td>
        </tr>
        <tr>
            <td><input type="text" id="operator:email" readonly="readonly" value="{$operator.email|escape}"/></td>
        </tr>
        <tr>
            <td><label for="operator:created">Дата реєстрації</label></td>
        </tr>
        <tr>
            <td><input type="text" id="operator:created" readonly="readonly" value="{$operator.created|escape}"/>
            </td>
        </tr>
        <tr>
            <td><label for="operator:enabled_from">Дата активації</label></td>
        </tr>
        <tr>
            <td><input type="text" id="operator:enabled_from" readonly="readonly" value="{$operator.enabled_from|escape}"/>
            </td>
        </tr>
        <tr>
            <td><label for="operator:enabled_to">Активен по</label></td>
        </tr>
        <tr>
            <td><input type="text" id="operator:enabled_to" readonly="readonly" value="{$operator.enabled_to|escape}"/>
            </td>
        </tr>
    </table>
    </fieldset>
</div>