<div id="sovaOverview">
{if $overview}
    <script type="text/javascript">
        {literal}
        $(document).bind('pageinit', function(){
            initHostServiceOverview();
        });
        {/literal}
    </script>
    {include file='inc/overview_hosts.tpl' overview=$overview is_on_service=1}
    {include file='inc/overview_hosts.tpl' overview=$overview is_on_service=0}
{/if}
</div>
