{assign var=settings value=$operator.settings}
<div id="settings-dialog" title="Налаштування &lt;{if isset($operator.name)}{$operator.name}{else}{$user.name}{/if}&gt;">
    {include file="form/user.tpl"}
    <div>
        <form id="settings-form">
            {include file="form/formkey.tpl"}
            <fieldset>
                <legend><h2>ПНО</h2></legend>
                <fieldset>
                    <legend><h3>Обслуговування</h3></legend>
                    <ul>
                    {foreach name=hosts from=$database_hosts|@sortby:"-is_on_service,type,alias" item=hostItem}
                        {if $hostItem.type != 'pno'}{continue}{/if}
                        {if $hostItem.is_on_service}
                            {assign var='host_is_on_service' value=1}
                        {else}
                            {assign var='host_is_on_service' value=0}
                        {/if}
                    {if isset($old_host_is_on_service) && $old_host_is_on_service neq $host_is_on_service}
                    </ul>
                </fieldset>
                <fieldset>
                    <legend><h3>Інше</h3></legend>
                    <ul>
                    {/if}
                        <li class="{if $hostItem.is_on_service}on-service{else}not-on-service{/if}">
                            <input type="checkbox"
                                   id="display-host-{$hostItem.nagios_name}"
                                   name="display_host[{$hostItem.nagios_name}]"
                                   {if isset($settings['display_host'][$hostItem.nagios_name])}checked="checked"{/if}
                                   />
                            <label for="display-host-{$hostItem.nagios_name}">{$hostItem.alias}</label>
                        </li>
                        {assign var='old_host_is_on_service' value=$host_is_on_service}
                    {/foreach}
                    </ul>
                </fieldset>
            </fieldset>
        </form>
    </div>
</div>
<script type="text/javascript">
    //<![CDATA[

    $(function() {
        $( "#settings-dialog" ).dialog({
            autoOpen: false,
            width: 800,
            height: 600,
            modal: true,
            buttons: {
                "Зберегти": function(event) {
                    var button = $(event.currentTarget);
                    button.button("option", "disabled", true);
                    $.ajax({
                        type: 'POST',
                        url: BASE_URL + 'settings/save/',
                        data: $('#settings-form').serialize(),
                        dataType: 'json',
                        cache: false
                    }).done(function(response) {
                        if ('object' == typeof(response)) {
                            if ('object' == typeof(response.error) && 'string' == typeof(response.error.message)) {
                                alert(response.error.message);
                            }
                            if ('undefined' != typeof(response.reload) && response.reload) {
                                window.location.reload();
                            }
                        }
                    }).always(function() {
                        button.button("option", "disabled", false);
                    });
                },
                "Закрити": function() {
                    $( this ).dialog( "close" );
                }
            }
        });

        $( "#settings-link" ).click(function() {
            $( "#settings-dialog" ).dialog( "open" );
            return false;
        });

    });
    //]]>
</script>