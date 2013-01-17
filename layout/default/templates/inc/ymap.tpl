{assign var=min_lat value="50.259679"}
{assign var=max_lat value="50.259679"}
{assign var=min_lon value="28.648092"}
{assign var=max_lon value="28.648092"}

<script src="//api-maps.yandex.ru/2.0-stable/?load=package.standard,package.geoObjects,package.clusters&lang=uk-UA"
        type="text/javascript"></script>

<script type="text/javascript">

    ymaps.ready(initYMap);

    yMap = null;

    function getHashYMapParams() {
        var hash = document.location.hash.replace('#', '');

        var params = {
            latitude: null,
            longitude: null,
            zoom: null
        };

        hash = hash.split(",", 3);

        /* we have coordinates */
        if (hash.length > 1 && hash[0] && hash[1]) {
            params.latitude = parseFloat(hash[0]);
            params.longitude = parseFloat(hash[1]);
        }

        /* we have zoom */
        if (hash.length > 2 && hash[2]) {
            params.zoom = parseFloat(hash[2]);
        }

        return params;
    }

    function navigateFromHash() {
        var params = getHashYMapParams();

        if (!params || yMap == null) {
            return;
        }

        /* we have coordinates */
        if (params.latitude && params.longitude) {
            yMap.setCenter(
                    [params.latitude, params.longitude],
                    (params.zoom) ? params.zoom : yMap.getZoom(),
                    { checkZoomRange: true, duration: 200 }
            );
        } else {
            /* we have zoom */
            if (params.zoom) {
                yMap.setZoom(params.zoom, { checkZoomRange: true, duration: 200 });
            }
        }
    }

    function initYMap() {
        var globalCluster = new ymaps.Clusterer({
            gridSize: 96
        });
        var i;
        var points = [];
    {strip}
    {foreach name=hosts from=$database_hosts|@sortby:"-is_on_service,alias" item=host}
        {if !isset($host['zones']) || ! $host['zones']}
            {continue}
        {/if}

        {if $host.scheme_image_name}
            {assign var="host_imagefile" value=$smarty.const.SITE_ROOT|cat:"media/scheme/"|cat:$host.scheme_image_name}
            /*{capture assign="host_image_html"}
                <a href="{$smarty.const.SOVA_BASE_URL}media/scheme/{$host.scheme_image_name}"
                rel="gallery{$host.host_id}" title="{$host.alias|escape}"
                target="_blank" class="gallery"><img src="{$smarty.const.SOVA_BASE_URL}{imagemodifier
                img=$host_imagefile chain="host-scheme-ymap" output="url"
                outputformat="jpeg"
                }" alt="{$host.alias|escape}"></a>
                {/capture}*/
        {else}
            {assign var="host_imagefile" value=""}
        {/if}

        {foreach from=$host.zones item=item name=current}
            var zonePoints = [];
            {if $item.entrance_position_longitude && $item.entrance_position_latitude}
                {if $min_lat > $item.entrance_position_latitude}{assign var=min_lat value=$item.entrance_position_latitude}{/if}
                {if $max_lat < $item.entrance_position_latitude}{assign var=max_lat value=$item.entrance_position_latitude}{/if}
                {if $min_lon > $item.entrance_position_longitude}{assign var=min_lon value=$item.entrance_position_longitude}{/if}
                {if $max_lon < $item.entrance_position_longitude}{assign var=max_lon value=$item.entrance_position_longitude}{/if}

                {if $item.scheme_image_name}
                {assign var="imagefile" value=$smarty.const.SITE_ROOT|cat:"media/scheme/"|cat:$item.scheme_image_name}
                /*{capture assign="zone_image_html"}
                <a href="{$smarty.const.SOVA_BASE_URL}media/scheme/{$item.scheme_image_name}"
                rel="gallery{$host.host_id}" title="{$host.alias|cat:": "|cat:$item.name|escape}"
                target="_blank" class="gallery"><img src="{$smarty.const.SOVA_BASE_URL}{imagemodifier
                        img=$imagefile chain="host-scheme-ymap" output="url"
                        outputformat="jpeg"
                        }" alt="{$item.name|escape}"></a>
                {/capture}*/
                {else}
                    {assign var="imagefile" value=""}
                {/if}

                {assign var="lat" value=$item.entrance_position_latitude|escape}
                {assign var="lon" value=$item.entrance_position_longitude|escape}
                zonePoints.push(new ymaps.GeoObject({
                    geometry:{
                        type:"Point",
                        coordinates:[{$lat}, {$lon}]
                    },
                    properties:{
                        hintContent:"{$host.alias|cat:": "|cat:$item.name|escape:javascript}",
                        caption:"{$host.alias|cat:": "|cat:$item.name|escape:javascript}",
                        balloonContentHeader: "<span class=\"balloon-header\">{$host.alias|escape:javascript}</span>",
                        balloonContentBody: ""
                        {if isset($host_image_html) && $host_imagefile !== ""}+ "{$host_image_html|escape:javascript}"{/if}
                        {if isset($zone_image_html) && $imagefile !== ""}+ "{$zone_image_html|escape:javascript}"{/if}
                        + "<p class=\"balloon-zone\">{$item.name|escape:javascript}</p>",
                        balloonContentFooter: "<a href=\"#{$lat},{$lon}\" class=\"balloon-coords\""
                                + " data-lat=\"{$lat|escape}\""
                                + " data-lon=\"{$lon|escape}\">"
                                + " <strong>Ш:</strong> {$lat|escape},"
                                + " <strong>Д:</strong> {$lon|escape}</a>"
                    }
                }));
            {/if}
            if (zonePoints.length) {
                points.push(zonePoints);
            }
        {/foreach}
    {/foreach}
    {/strip}

        if (points.length) {
            for (i = 0; i < points.length; i++) {
                //var cluster = new ymaps.Clusterer();
                //cluster.add(points[i]);
                globalCluster.add(points[i]);
                //points[i] = cluster;
            }
        }

        var center, zoom;

        var params = getHashYMapParams();

        if (params.latitude && params.longitude) {
            center = [params.latitude, params.longitude];
        } else {
            center = [({$max_lat} + {$min_lat})/2, ({$max_lon} + {$min_lon})/2];
        }

        if (params.zoom) {
            zoom = params.zoom;
        } else {
            zoom = 6;
        }

        yMap = new ymaps.Map("YMapsID", {
            center: center,
            zoom: zoom,
            {if $smarty.const.DEFAULT_LAYOUT_NAME eq "mobile"}
            {else}
            behaviors: ["drag", "dblClickZoom", "scrollZoom"]
            {/if}
        });

        yMap.controls.add('zoomControl');
        yMap.controls.add('typeSelector');
        yMap.controls.add('mapTools');
        yMap.controls.add('scaleLine');

        /*for (i = 1; i <= points.length; i++) {
            myMap.geoObjects.add(points[i]);
        }*/

        yMap.geoObjects.add(globalCluster);

        $(".gallery").fancybox({
            prevEffect:'none',
            nextEffect:'none',
            closeBtn:true,
            helpers:{
                title:{ type:'outside' }
            }
        });

        /*yMap.balloon.events.add('open', function(event) {
            $(".balloon-coords").click(function() {
                var lat = $(this).attr('data-lat');
                var lon = $(this).attr('data-lon');
                yMap.setCenter([lat, lon]);
                //event.get('balloon').close();
            });
        });*/
    }
</script>

<div id="ymap">
    <div id="YMapsID" style="height: 800px"></div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#YMapsID").height($(window).innerHeight() - 30);
        $(window).bind("hashchange", navigateFromHash);
    });

    $(window).resize(function() {
        $("#YMapsID").height($(window).innerHeight() - 30);
    });
</script>