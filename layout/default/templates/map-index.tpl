<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{if $pageTitle}{$pageTitle|escape} :: {/if}СОВА :: {$smarty.const.SITE_NAME}</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>

    <link rel="stylesheet" href="{$smarty.const.SOVA_BASE_URL}skin/{$smarty.const.DEFAULT_LAYOUT_NAME}/css/map/styles.css"
          type="text/css" />

    <link rel="stylesheet" href="{$smarty.const.SOVA_BASE_URL}js/fancybox2/jquery.fancybox.css?v=2.1.3" type="text/css"
          media="screen"/>

    <script type="text/javascript" src="{$smarty.const.SOVA_BASE_URL}js/jquery-1.8.2.js"></script>

    <!-- Add fancyBox -->
    <script type="text/javascript"
            src="{$smarty.const.SOVA_BASE_URL}js/fancybox2/jquery.fancybox.pack.js?v=2.1.3"></script>


    <script type="text/javascript">
    //<![CDATA[
    BASE_URL = "{$smarty.const.SOVA_BASE_URL}";
    REWRITE_BASE = "{$smarty.const.REWRITE_BASE}";
    //]]>
    </script>

    {foreach name=head_html from=$head_html item=html}
    {$html}
    {/foreach}

</head>
<body>
<div class="wrapper">
    <div id="container">
        <div id="content">{$content}</div>
    </div>
</div>

<script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-37208126-1']);
    _gaq.push(['_trackPageview']);

    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
</script>
</body>
</html>
