{assign var=settings value=$operator.settings}
<div id="settings-dialog" data-role="popup" data-theme="a">
    <div data-role="header" data-theme="a" class="ui-corner-top">
        <h1>Налаштування</h1>
    </div>
    <div data-role="content" data-theme="d" class="ui-corner-bottom ui-content">
    <form id="settings-form">
    {include file="form/formkey.tpl"}
        <fieldset data-role="controlgroup" data-mini="true">
            <h2>ПНО</h2>

            <fieldset data-role="controlgroup" data-mini="true">
            <div data-role="collapsible-set" data-theme="a" data-content-theme="d" data-inset="false">
            <div data-role="collapsible" data-collapsed="false" data-mini="true">
                <h3>Обслуговування</h3>
            <div data-role="fieldcontain">
            {foreach name=hosts from=$database_hosts|@sortby:"-is_on_service,type,alias" item=hostItem}
                {if $hostItem.type != 'pno'}{continue}{/if}
                {if $hostItem.is_on_service}
                    {assign var='host_is_on_service' value=1}
                    {else}
                    {assign var='host_is_on_service' value=0}
                {/if}
                {if isset($old_host_is_on_service) && $old_host_is_on_service neq $host_is_on_service}
                </div>
                </div>
                </div>

                <div data-role="collapsible-set" data-theme="a" data-content-theme="d" data-inset="false">
                <div data-role="collapsible" data-mini="true">
                    <h3>Інше</h3>
                <div data-role="fieldcontain">
                {/if}
                <input type="checkbox"
                       id="display-host-{$hostItem.nagios_name}"
                       name="display_host[{$hostItem.nagios_name}]"
                       {if isset($settings['display_host'][$hostItem.nagios_name])}checked="checked"{/if}
                        />
                <label for="display-host-{$hostItem.nagios_name}">{$hostItem.alias}</label>
                {assign var='old_host_is_on_service' value=$host_is_on_service}
            {/foreach}
            </div>
            </div>
            </div>
            </fieldset>

        </fieldset>
        <fieldset class="ui-grid-a">
            <div class="ui-block-a"><button type="button" id="settings-dialog-close" data-theme="c" data-rel="back" data-mini="true">Закрити</button></div>
            <div class="ui-block-b"><button type="submit" data-theme="b" data-mini="true">Зберегти</button></div>
        </fieldset>
        <!--div data-role="controlgroup">
            <a href="#" data-mini="true" data-role="button" data-inline="true" data-rel="back" data-theme="c">Закрити</a>
            <a href="#" data-mini="true" data-role="button" data-inline="true" data-transition="flow" data-theme="b">Зберегти</a>
        </div-->
    </form>
    </div>
</div>
<script>
    //<![CDATA[
    $(document).bind("pageinit", function() {
        $("#settings-dialog-close").click(function () {
            $("#settings-dialog").popup("close");
        });

        $("#settings-form").submit(function () {
            //var button = $(event.currentTarget);
            //button.button("option", "disabled", true);
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
                //button.button("option", "disabled", false);
            });

            $("#settings-dialog").popup("close");

            return false;
        });
    });

    //]]>
</script>
