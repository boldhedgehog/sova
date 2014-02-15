if ('undefined' == typeof(nagiosWatcher)) {
    nagiosWatcher = {
        data : {
            hostId: null
        },
        services : [],
        watcherController : null,
        url : location.href,
        baseURL: null,
        requestURI: null,
        refreshURI: null,

        sovaInterval : null,
        sovaIntervalFull : null,

        refresh : function() {
            this._refresh(1);
        },
        refreshFull : function() {
            this._refresh(0);
        },
        _refresh : function(useLastCheck) {
            /*if (this.watcherController) {
                this.watcherController.xajaxRefreshStatuses(useLastCheck);
            } else {
                window.clearInterval(this.sovaInterval);
                this.sovaInterval = null;
            }*/
            this.refreshStatuses(useLastCheck);
        },
        getHostLogRows : function(filter, button) {
            jQuery.ajax(
                nagiosWatcher.baseURL + 'host/getLogRows/id/' + nagiosWatcher.data.hostId,
                {
                    type: "POST",
                    dataType: "json",
                    beforeSend: function() {
                        $('#log-please-wait').css('display','block');
                        button.prop('disabled', true);
                    },
                    complete: function() {
                        $('#log-please-wait').css('display','none');
                        button.prop('disabled', false);
                    },
                    success: function(xhr) {
                        $('table.log tbody').html(xhr.response);
                    },
                    cache: false,
                    data: filter
                }
            );
        },
        refreshStatuses : function(useLastCheck) {
            useLastCheck = useLastCheck || false;
            jQuery.ajax(
                nagiosWatcher.refreshURI,
                {
                    type: "POST",
                    dataType: "json",
                    success: function(xhr) {
                        nagiosWatcher.services = xhr.services;
                        if (nagiosWatcher.services) {
                            var service;
                            var services = nagiosWatcher.services;
                            for (service in services) {
                                if (!services.hasOwnProperty(service)) {
                                    break;
                                }

                                service = services[service];
                                $('#service' + service.md5).removeClass().addClass(
                                    'service bgServiceState' + service.state
                                ).find('.imgFlapping').removeClass().addClass((service.is_flapping|0)?'hidden':'');

                                /*console.log(service, 'Setting state to ' + service.state +
                                    ' for ' + service.host_name + ':' + service.description);*/
                            }
                        }
                        nagiosWatcher.onRefresh(xhr);
                    },
                    cache: false,
                    data: { "useLastCheck" : useLastCheck }
                }
            );
        },

        onRefresh : function(xhr) {
            //console.log(xhr);
        },
        // refresh on the host page
        onRefreshHost : function(response) {
            //console.log(response);
            if (typeof(response['nagvisServiceIconOverlay']) !== 'undefined') {
                $('#nagvisServiceIconOverlay').replaceWith(response['nagvisServiceIconOverlay']);
                $('img.host-map').each(function() {
                    scaleNagvisServiceIcons($(this))
                });
                initNagvisMap();
            }
            if (typeof(response['servicesContainer']) !== 'undefined') {
                $('#services-container').find('table').replaceWith(response['servicesContainer']);
                initTableFilter("table.services");
                applyFilters("table.services");
            }
        }
    };
}

nagiosWatcher.assignWatcherController = function(controller) {
    if (null != nagiosWatcher.watcherController) {
        return;
    }
    nagiosWatcher.watcherController = controller;
};

nagiosWatcher.startAlertsWatch = function(interval) {
    if ('number' != typeof(nagiosWatcher.sovaInterval)) {
        nagiosWatcher.sovaInterval = window.setInterval(
            function () {
                nagiosWatcher.refresh.call(nagiosWatcher);
            },
            interval
        );
    }

    if ('number' != typeof(nagiosWatcher.sovaIntervalFull)) {
        nagiosWatcher.sovaIntervalFull = window.setInterval(
            function () {
                nagiosWatcher.refreshFull.call(nagiosWatcher);
            },
            interval * 10 + 2
        );
    }
};

nagiosWatcher.stopAlertsWatch = function() {
    window.clearInterval(nagiosWatcher.sovaInterval);
    nagiosWatcher.sovaInterval = null;
    window.clearInterval(nagiosWatcher.sovaIntervalFull);
    nagiosWatcher.sovaIntervalFull = null;
};

nagiosWatcher.refreshServices = function(services) {
    for (var key in services) {
        nagiosWatcher.services[key] = services[key];
    }
};

nagiosWatcher.loadServiceData = function(key) {
    if (!key) return;
    // TODO: use jQuery AJAX
    nagiosWatcher.watcherController.xajaxGetService(key);
};

nagiosWatcher.getServiceData = function(key) {
    if (!key) return {};
    if (typeof(nagiosWatcher.services[key]) != 'undefined') {
        return nagiosWatcher.services[key];
    }

    nagiosWatcher.loadServiceData(key);

    return nagiosWatcher.services[key];
};

nagiosWatcher.setServiceData = function(key, data) {
    if (!key) return;
    nagiosWatcher.services[key] = data;
};
    
nagvisServices = {};

function log(object) {
    if (typeof(console) != 'undefined') {
        console.log(object);
    }
}

function initFloats(context) {
    context = context || 'body';

    $('li.host', $(context)).mousemove(function(event) {
        var notes = $(this).find('div.hostFloat');
        if (!notes) return;

        var leftPosition = event.pageX + 15;
        var topPosition = event.pageY + 15;
        /*
         var target = $(this);
         var rightBorder = target.offset().left + target.width() - 50;
         var bottomBorder = target.offset().top + target.height() - 10;
         if (leftPosition > rightBorder) leftPosition = rightBorder;
         if (topPosition > bottomBorder) topPosition = bottomBorder;
         */

        notes.show();

        notes.offset({
            top : topPosition,
            left : leftPosition
        });
    });

    $('li.host', $(context)).mouseout(function() {
        $(this).find('div.hostFloat').hide();
    });
}

function initHostServiceOverview() {
    $('li.host').each(function() {
        var notes = $(this).find('div.service-icons');
        
        if (!notes.length) return;

        var iconContainer = $(this).find('div.hosticonContainer');

        var offset = iconContainer.offset();
        
        var width = iconContainer.width();
        var height = iconContainer.height();

        /*
        notes.show()
            .offset({
                top : offset.top,
                left : offset.left
            })
            .width(width)
            .height(height)
        */
        var count = notes.find('li').length;

        var cols = Math.floor(Math.sqrt(count));
        var rows = Math.ceil(count / cols);

        if (rows < cols) {
            /*var tmp = cols;
            cols = rows;
            rows = tmp;*/
            width = width / rows - 1;
            height = height / cols - 1;
            
        } else {
            width = width / cols - 1;
            height = height / rows - 1;
        }

        notes.find('li')
            .width(width)
            .height(height)
            /*.mousemove(function (event) {
                var info = $(this).find('span');
                var leftPosition = event.pageX + 15;
                var topPosition = event.pageY + 15;
                
                info.show().offset({
                    top : topPosition,
                    left : leftPosition
                });
                
                if (info.text() == '' || info.text() == '...') {
                    info.text('...');
                    var data = nagiosWatcher.getServiceData($(this).attr('service-key'));
                    //console.log(data);
                    info.text(data.position);
                }
            })
            .mouseout(function () {
                $(this).find('span').hide();
            })*/
            //.find('span').css('position', 'absolute').offset({left:-1000, top: -1000})
        ;

    });
}

function resizeNagvisMapImage(image) {
    image = image || $('img.host-map');
    image.each(function() {
        var diff = 1;

        var maxWidth = $(this).parent().outerWidth();

        if ($(this).attr('expanded')) {
            $(this).width($(this).attr('originalWidth'))
                .height($(this).attr('originalHeight'))
                .attr('originalWidth', '')
                .attr('originalHeight', '')
                .attr('expanded', '')
        } else if ($(this).width() != maxWidth) {
            var originalWidth = $(this).width();
            $(this).attr('originalWidth', originalWidth);
            var originalHeight = $(this).height();
            $(this).attr('originalHeight', originalHeight);

            $(this).width(maxWidth - 7);

            diff = parseInt(originalWidth) / parseInt($(this).width());

            $(this).height(originalHeight / diff).attr('expanded', 1);
        }

        $(this).attr('diff', diff);

        scaleNagvisServiceIcons($(this));

    });
}

function scaleNagvisServiceIcons(image) {
    var offset = image.offset();
    var diff = image.attr('diff');

    for (var md5 in nagvisServices) {
        $('div#nagvisServiceIcon' + md5).offset({top : parseInt((nagvisServices[md5]['y'] / diff) + offset.top)
            , left : parseInt((nagvisServices[md5]['x'] / diff) + offset.left)});
    }
}

function initNagvisMap() {
    $('a.host-map')
        .css('cursor', 'move')
        .click(function() {
            var expanded;

            if ($(this).find('img.host-map').attr('expanded')) {
                expanded = ''
            } else {
                expanded = 1;
            }

            if (expanded) {
                $(this).parent()
                    .css('top', 0)
                    .css('left', 0)
                    .css('bottom', 0)
                    .css('right', 0)
                    .css('position', 'fixed')
                    .css('margin', 0)
                    .css('overflow-y', 'scroll')
                    .css('background-color', '#aaa')
                ;
            } else {
                $(this).parent()
                    .attr('style', '')
                ;
            }
            
            resizeNagvisMapImage($(this).find('img.host-map'));

            return false;
        }
    );

    $('div.nagvisServiceIcon').mousemove(function(event) {
        var notes = $(this).find('div.serviceFloat');

        //if (0 == notes.length) notes = $(this).parent().next('div.serviceFloat');
        if (0 == notes.length) return;

        var leftPosition = event.pageX + 10;
        var topPosition = event.pageY + 10;

        /* calculate new top-left coordinates */
        var diff = $('img.host-map').offset().top + $('img.host-map').outerHeight() - notes.outerHeight() - topPosition - 10;
        if (diff <= 0) topPosition += diff;
        diff = $('img.host-map').offset().left + $('img.host-map').outerWidth() - notes.outerWidth() - leftPosition - 10;
        if (diff <= 0) leftPosition += diff;

        /* if left overlaps cursor position - move the popup to the left of the cursor */
        if (leftPosition <= event.pageX) {
            leftPosition = event.pageX - notes.outerWidth() - 10;
        }

        notes.show();

        notes.offset({
            top : topPosition,
            left : leftPosition
        });

    })
        .mouseout(function() {
            $(this).find('div.serviceFloat').hide();
            //$(this).parent().next('div.serviceFloat').hide();
        });
}

function initHostLinks(context) {
    return;
    /*context = context || 'body';
     $('a.hostLink', $(context)).click(function() {
     var options = {id : $(this).context.target, url : $(this).context.href, title : $(this).find('img').attr('alt')};
     return openHostWindow(options);
     });*/
}

function initServiceLinks() {
    /*$('a.serviceLink').click(function() {
     xajax_hostController.xajaxGetService($(this).find('span').attr('id').replace(/^service\[/, '').replace(/\]/, ''));
     //$hostTabs.tabs('add', $(this).attr('href').replace(/\/index\//, '/ajax/'), $(this).text());
     return false;
     });*/
}

function initHostTabs(hostId) {
    return  $('#host' + hostId + ' div.tabs').tabs({cookie: {name: 'activeTab' + hostId, expires: 30, path: REWRITE_BASE}});
}

function initServiceTabs() {
    return $('#service' + serviceId + ' div.service-tabs').tabs(
        {
            select : function (event, ui) {
                if (ui.index == 4) {
                    return true;
                }
                if (ui.index > 4) {
                    ui.index--;
                }
                $.cookie('activeTab' + hostId, ui.index, {expires: 30, path: REWRITE_BASE});
                location.href = hostUrl;// + $(ui.tab).attr('href');
                return false;
            }
        }
    );
}

function initNavigationLinks(context) {
    context = context || 'div#leftnav';
    $('a:not([href])', $(context)).each(function() {
        $(this).addClass('disabled');
    });
    $('a:[href=' + location.href + ']', $(context)).addClass('active');
}

function initDHTMLX() {
    dhxWins = new dhtmlXWindows();
    dhxWins.setSkin('dhx_web');
    dhxWins.enableAutoViewport(true);
    dhxWins.setImagePath(dhxPath + 'imgs/');
    dhxWins.setEffect('move', true);
    dhxWins.setEffect('resize', true);

    dhxWins.attachEvent('onMoveFinish', function(win) {
            var position = win.getPosition();
            $.cookie(win.getId() + '_pos', position[0] + ':' + position[1], {"expires" : 30});
        }
    );

    dhxWins.attachEvent('onResizeFinish', function(win) {
            var dimension = win.getDimension();
            $.cookie(win.getId() + '_dim', dimension[0] + ':' + dimension[1], {"expires" : 30});
        }
    );

    translateWindows(dhtmlXWindows);

    dhtmlXWindows.prototype.count = function() {
        this._windowCount = 0;

        this.forEachWindow(function() {
            dhxWins._windowCount++;
        });
        return this._windowCount;
    };

    if (sAlertId != '') openAlertWindow(sAlertId);
}

function translateWindows(dhxWins) {
    dhxWins.prototype.i18n = {
        dhxcontaler: "dhtmlxcontainer.js відсутній на сторінці",
        noenginealert: "Бібліотека dhtmlxWindows не завантажена.",
        stick: "Приклеїти",
        unstick: "Відклеїти",
        help: "Довідка",
        parkdown: "Відновити",
        parkup: "Згорнути",
        maximize: "Розгорнути",
        restore: "Відновити",
        close: "Закрити",
        dock: "Паркувати"
    };
}

function initDhtmlXContainer(activeTab, cookieName) {
    //$('div#dhtmlxContainer').height($(window).height());

    cookieName = cookieName || '';

    var tabFromUrl = document.location.href.replace(/^[^#]*#?/, '');
    if (tabFromUrl != '') {
        activeTab = tabFromUrl;
    } else {
        activeTab = $.cookie('activeTab' + cookieName) || activeTab || 'details';
    }

    //log(activeTab);log(tabFromUrl);

    tabbar = new dhtmlXTabBar('dhtmlxContainer');
    //tabbar.setSkin('dhx_web');
    tabbar.setImagePath(dhxPath + "imgs/dhtmlx_tabbar/");
    //tabbar.enableAutoReSize(true);
    tabbar.enableAutoSize(true);

    /*$('div.details').each(function(){
     var id = $(this).context.id;
     tabbar.addTab(id, $(this).find('h2').text());
     tabbar.setContent(id, id);
     $(this).find('h2').addClass('hidden');
     //$('body').prepend('<a name="' + id + '" />');
     });
     tabbar.setTabActive(activeTab);*/

    tabbar.attachEvent('onSelect', function(id, lastId) {
        var href = document.location.href.replace(/#.*$/, '');
        //document.location.href = href + '#' + id;
        $.cookie('activeTab' + cookieName, id, {"path" : href, "expires" : 30});
        return true;
    });
}

function initForm(context) {
    context = context || 'body';
    try {
        $('form', $(context)).submit(function() {
            var allOk = true;
            $(this).find('input.required, textarea.required').each(function() {
                if ('' == jQuery.trim($(this).val())) {
                    $(this).next('span.formError').html('<br/>Це поле обов\'язкове для заповнення').show().fadeOut(5000);
                    allOk = false;
                }
            });

            return allOk;
        });
    } catch(e) {
        console.log(e);
    }

}

function openAlertWindow(id, width, height) {
    return true;
    try {
        if (dhxWins.window('alertWindow' + id)) {
            dhxWins.window('alertWindow' + id).bringToTop();
            return false;
        }

        width = width || 1000;
        height = height || 600;

        var offset = getCenterCoordinates(width, height);

        var alertWindow = dhxWins.createWindow('alertWindow' + id, offset.left, offset.top, width, height);
        alertWindow.keepInViewport(false);
        alertWindow.setModal(true);

        alertWindow.centerOnScreen();

        alertWindow.button('close').hide();

        alertWindow.setText('Обробка тривоги');
        alertWindow.attachURL(BaseUrl + 'alert/process/id/' + id, false);

        return true;
    } catch (e) {
        return false;
    }
}

function initSessionNotifications() {
    var context = context || 'body';
    $('ul.notifications', $(context)).each(function() {
        $(this).fadeOut(10000);
    });
}

function addRefreshButton(win) {
    win.addUserButton('refresh', 5, 'Оновити', 'refresh');
    win.button('refresh').attachEvent('onClick', function() {
        try {
            this.parentNode.parentNode.parentNode.vs.def._frame.contentWindow.location.reload();
        } catch(e) {
            alert(e);
        }
    });
}

function openHostWindow(options) {
    try {
        var id = options.id;

        if (dhxWins.window(id)) {
            win = dhxWins.window(id);
            win.bringToTop();
            if (win.isParked()) win.park();
            return false;
        }

        var width = options.width || 940;
        var height = options.height || 600;
        var title = options.title || '';
        var offset = getCenterCoordinates(width, height);

        var savedPosition = $.cookie(id + '_pos');
        if (savedPosition) {
            savedPosition = savedPosition.split(':');
            offset.left = parseInt(savedPosition[0]);
            offset.top = parseInt(savedPosition[1]);
        }
        var savedDimension = $.cookie(id + '_dim');
        if (savedDimension) {
            savedDimension = savedDimension.split(':');
            width = savedDimension[0];
            height = savedDimension[1];
        }

        var hostWindow = dhxWins.createWindow(id, offset.left, offset.top, width, height);

        hostWindow.keepInViewport(false);
        hostWindow.setModal(false);

        if (!savedPosition) hostWindow.centerOnScreen();

        addRefreshButton(hostWindow);

        hostWindow.setText(title);

        var url = options.url || (BaseUrl + 'host/index/id/' + id);

        hostWindow.attachURL(url, false);

        return false;
    } catch (e) {
        log(e);
        return true;
    }
}

function openServiceWindow(options) {
    try {
        var id = options.id;

        if (dhxWins.window('serviceWindow' + id)) {
            win = dhxWins.window('serviceWindow' + id);
            win.bringToTop();
            if (win.isParked()) win.park();
            return false;
        }

        var width = options.width || 800;
        var height = options.height || 600;
        var title = options.title || '';
        var offset = getCenterCoordinates(width, height, dhxWins.count() * 10);

        var serviceWindow = dhxWins.createWindow('serviceWindow' + id, offset.left, offset.top, width, height);
        serviceWindow.keepInViewport(false);
        serviceWindow.setModal(false);
        serviceWindow.centerOnScreen();

        var position = serviceWindow.getPosition();
        serviceWindow.setPosition(position[0] + dhxWins.count() * 10, position[1] + dhxWins.count() * 10);

        serviceWindow.setText(title);

        addRefreshButton(serviceWindow);

        var url = options.url || (BaseUrl + 'service/index/id/' + id);

        serviceWindow.attachURL(url, false);

        return false;
    } catch (e) {
        log(e);
        return true;
    }
}

function getCenterCoordinates(width, height, offset) {
    var top = 0, left = 0;
    offset = offset || 0;
    if (screen.width) {
        left = (screen.width - width) / 2;
        if (left < 0) {
            left = 0;
        }
        top = (screen.height - height) / 2;
        if (top < 0) {
            top = 0;
        }
    } else {
        left = 0;
        top = 0;
    }

    return {left: left + offset, top: top + offset};
}

(function ($) {
    $.fn.columnfilter = function (filter, columnIndex) {
        //var columnIndex = null;
        if (typeof(filter) == 'function') {
            columnIndex = filter.attr('column-index');
            filter = filter.val();
        } else if (typeof(filter) == 'object') {
            columnIndex = $(filter).attr('column-index');
            filter = $(filter).val();
        }

        var s = filter.toString().toLowerCase().split(" ");
        this.each(function () {
            $(this).find('tr:has(td)').each(function () {
                var row = $(this).show();
                var rowText = ((columnIndex == null) ? row : row.find('td:eq(' + columnIndex + ')')).text().toLowerCase();
                $.each(s, function () {
                    if (rowText.indexOf(this) === -1) row.hide();
                });
            });
        });

    };
})(jQuery);

(function ($) {
    $.fn.listfilter = function (filter) {
        //var columnIndex = null;
        if (typeof(filter) == 'function') {
            filter = filter.val();
        } else if (typeof(filter) == 'object') {
            filter = $(filter).val();
        }

        var s = filter.toString().toLowerCase().split(" ");
        this.each(function () {
            $(this).find('li').each(function () {
                var row = $(this).hide();
                var rowText = row.text().toLowerCase();
                $.each(s, function () {
                    if (rowText.indexOf(this) !== -1 || row.hasClass('active')) row.show();
                });
            });
        });

    };
})(jQuery);

function initTableFilter(tableId) {
    var table = $(tableId).data('filter',{});
    
    var cookieFilterName = 'filter' + table.attr('id');
    var cookieFilter = $.cookie(cookieFilterName);

    if (cookieFilter) {
        cookieFilter = JSON.parse(cookieFilter);
        table.data('filter', cookieFilter);
    } else {
        cookieFilter = {};
    }

    table.setFilter = function(field, value) {
        var filter = $(this).data('filter');
        filter[field] = value;
        $(this).data('filter', filter);
        $.cookie(cookieFilterName, JSON.stringify(filter));
    }

    table.getFilter = function(field) {
        var filter = $(this).data('filter');
        return filter[field];
    }

    $(tableId + ' thead th').each(function(index) {
        if ($(this).is('.nofilter')) {
            return true;
        }
        var headers = $(tableId + ' thead');
        var controlId = 'filter' + $(tableId).attr('id') + index;
        
        if ($(this).is('.state')) {
            $(this).append('<select class="filter" id="' + controlId + '"><option/><option value="OK">OK</option><option value="WARNING">WARNING</option><option value="CRITICAL">CRITICAL</option><option value="UNKNOWN">UNKNOWN</option></select>')
                .find('select')
                .css('display', 'none')
                .attr('column-index', index)
                .data('table', table)
                .change(function() {
                    $(this).data('table').setFilter('f' + index, $(this).val());
                    headers.find('.filter[column-index!=' + $(this).attr('column-index') + ']')
                    .val('')
                    .css('display', 'none')
                    .parent()
                    .find('span')
                    .css('display', 'inline');

                    $(tableId).columnfilter($(this));
                    if ($(this).val() == '') {
                        $(this).css('display', 'none')
                            .parent()
                            .find('span')
                            .css('display', 'inline');
                    }
                });
        } else {
            $(this).append('<input class="filter" id="' + controlId + '"/>')
                .find('input')
                .css('display', 'none')
                .attr('column-index', index)
                .data('oldValue', '')
                .data('table', table)
                .keyup(function(event) {
                    if ($(this).data('oldValue') == $(this).val() && (event.keyCode != 27)) {
                        return;
                    }

                    if (event.keyCode == 27) {
                        $(this).val('')
                            .data('oldValue','')
                            .css('display', 'none')
                            .parent()
                            .find('span')
                            .css('display', 'inline');
                    } else {
                        headers.find('.filter[column-index!=' + index + ']')
                            .val('')
                            .css('display', 'none')
                            .parent()
                            .find('span')
                            .css('display', 'inline');
                    }

                    $(this).data('table')
                        .data('filter', {})
                        .setFilter('f' + index, $(this).val());

                    clearTimeout($(this).data('timeout'));
                    $(this).data(
                        'timeout',
                        setTimeout(
                            '$("' + tableId + '").columnfilter("' 
                                + $(this).val() + '", ' + index + ');',
                            500
                        )
                    );
                    //$("table.log").columnfilter($(this).val(), index);

                    $(this).data('oldValue', $(this).val());
                });
        }

        $(this).find('span').click(function() {
            var el = document.getElementById(controlId);
            if (el) {
                var width = $(this).parent().css('width');
                $(this).hide();
                el.style.display = 'block';
                el.style.width = (parseInt(width) - 0) + 'px';
                el.focus();
                $(this).parent().css('width', width);
            }
        });

    });
    
    $(tableId + ' thead th input, ' + tableId + ' thead th select').each(function() {
        var value = table.getFilter('f' + $(this).attr('column-index'));
        if (value && $(this).is('.time') ) {
            $(this).datepicker('setDate', new Date(parseInt(value) * 1000));
        } else {
            $(this).val(value);
        }
    });
}

function initTableSearch(tableId, settings) {
    var table = $(tableId).data('filter',{});
    table.data('settings', settings);

    table.setFilter = function(field, value) {
        var filter = $(this).data('filter');
        filter[field] = value;
        $(this).data('filter', filter);
    }

    table.getFilter = function(field) {
        var filter = $(this).data('filter');
        return filter[field];
    }

    var cookieFilter = $.cookie('logfilter' + table.attr('id'));

    if (cookieFilter) {
        cookieFilter = JSON.parse(cookieFilter);
        table.data('filter', cookieFilter);
    } else {
        cookieFilter = {};
    }

    var filterRow = $(tableId + ' thead').append('<tr class="filter"></tr>').find('tr').last();
    var regexp = /name-([a-z_]*)/;
    $(tableId + ' thead th').each(function(index) {
        var filterCell = filterRow.append('<th class="filter"></th>').find('th').last();

        if ($(this).is('.nofilter')) {
            return true;
        }
        var classes = $(this).attr('class').split(' ');
        var name = '';var i;
        for (i=0;i<classes.length;i++) {
            name = classes[i].match(regexp);
            if (name) {
                name = name[1];
                break;
            }
        }
        if ($(this).is('.state')) {
            filterCell.append('<select multiple="multiple" class="filter" id="filter' + $(tableId).attr('id') + name + index + '"><option value="0">OK</option><option value="1">WARNING</option><option value="2">CRITICAL</option><option value="3">UNKNOWN</option></select>')
                .find('select')
                .attr('column-index', index)
                .attr('name', name)
                .data('table', table)
                .change(function() {
                    $(this).data('table').setFilter(name, $(this).val());
                });
        } else {
            var input = filterCell.append('<input class="filter" id="filter' + $(tableId).attr('id') + name + index + '"/>')
                .find('input')
                .attr('column-index', index)
                .data('oldValue', '')
                .attr('name', name)
                .data('table', table)
                .keyup(function() {
                    var value = $(this).is('.time') ? parseInt($(this).datepicker('getDate').getTime() / 1000) : $(this).val();
                    $(this).data('table').setFilter($(this).attr('name'), value);
                })
                .keypress(function (event) {
            	    if ( event.which == 13 ) {
            		$(this).data('table').find('button.search').click();
            	    }
                });

            if ($(this).is('.time')) {
                filterCell.addClass('filter-time');
                var options = {
                        showOtherMonths: true,
                        selectOtherMonths: true,
                        changeMonth: true,
                        changeYear: true,
                        onSelect: function(dateText, inst) {
                            $(inst.input).keyup();
                        }
                    };
                input.addClass('time').datepicker(options);
                name += '_end';
                filterCell.append('<span>&nbsp;:&nbsp;</span><input class="filter time" id="filter' + $(tableId).attr('id') + name + index + '"/>')
                    .find('input').last()
                    .attr('column-index', index)
                    .data('oldValue', '')
                    .attr('name', name)
                    .data('table', table)
                    .keyup(function() {
                        $(this).data('table').setFilter($(this).attr('name'), parseInt($(this).datepicker('getDate').getTime() / 1000 + 86400));
                    })
                    .datepicker(options);
            }
        }

    });

    $(tableId + ' thead th input, ' + tableId + ' thead th select').each(function() {
        var value = table.getFilter($(this).attr('name'));
        if (value && $(this).is('.time') ) {
            $(this).datepicker('setDate', new Date(parseInt(value) * 1000));
        } else {
            $(this).val(value);
        }
    });

    $(tableId + ' thead th.filter').last().append('<button class="search">Пошук</button>').find('button')
        .data('table', table)
        .click(function() {
            var table = $(this).data('table');
            var filter = table.data('filter');
            $.cookie('logfilter' + table.attr('id'), JSON.stringify(filter));
            table.data('settings').searchFunction(filter, $(this));
        });
}

function applyFilters(tableId) {
    $(tableId + ' input.filter[value!=\'\']').keyup();
    $(tableId + ' select.filter').each(function() {
        if ($(this).val() != '') {
            $(this).change();
            return false;
        }
    })
}

function highlightZone(object, highlight) {
    var className = object.className.split(' ');
    var id = '';
    var communicationDeviceId = '';

    var i;

    for (i=0; i<className.length; i++) {
        if (className[i].indexOf('zone-row-id-') != -1) {
            id = className[i].replace('zone-row-id-', '');
        }
        if (className[i].indexOf('zone-row-device-') != -1) {
            communicationDeviceId = className[i].replace('zone-row-device-', '');
        }
        if (id && communicationDeviceId) {
            break;
        }
    }

    if (id) {
        if (highlight) {
            $('.zone-row-id-' + id).addClass('zone-highlight');
        } else {
            $('.zone-row-id-' + id).removeClass('zone-highlight');
        }
    }

    if (communicationDeviceId) {
        if (highlight) {
            $('.zone-row-device-' + communicationDeviceId).addClass('zone-highlight-device');
        } else {
            $('.zone-row-device-' + communicationDeviceId).removeClass('zone-highlight-device');
        }
    }

}

function initStatusBar() {
    $('#status-header').click(function() {
        $('#status-body').slideToggle('fast');
    });

    //barInterval = setInterval(function(){var el = document.getElementById('status-bar');el.style.bottom = '0px';}, 500);
}

function toggleCollapseLeftNav() {
    return function () {
        var $pullerLeft = $('#left-puller');

        $pullerLeft.toggleClass('clicked');

        // scroll to puller
        if (!$pullerLeft.hasClass('clicked') && $(window).scrollTop() > $pullerLeft.offset().top) {
            $('html, body').animate({
                scrollTop: $(this).offset().top
            }, 200);
        }

        $.cookie('nav_collapsed', $pullerLeft.hasClass('clicked'), {expires: 30, path: REWRITE_BASE});

        $('#leftnav-container').toggleClass('collapsed');
        $('#content').toggleClass('expanded');

        $pullerLeft.find('span').toggleClass('fa-angle-double-left').toggleClass('fa-angle-double-right');
    }
}
