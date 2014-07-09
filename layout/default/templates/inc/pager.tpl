{assign var=pages value=$totalRows/$pageSize}
{$pages|@var_dump}
{$currentPage|@var_dump}
{strip}
<ol class="pager">
    {section name=pager start=1 loop=$pages}
        {if $smarty.section.pager.index eq $currentPage}
            <li class="current">{$currentPage}</li>
            {else}
            <li><a>{$smarty.section.pager.index}</a></li>
        {/if}

    {/section}
</ol>
{/strip}