/**
 * Mobile JS.
 *
 * User: boldhedgehog
 * Date: 07.11.12
 * Time: 23:00
 */

function initHostAccordion(hostId) {
    var hash = window.location.hash;

    if (!hash) {
        hash = "#" + $.cookie('activeTab' + hostId);
    }

    $(hash).trigger("expand");

    //return  $('#host' + hostId + ' div.tabs').tabs({cookie: {name: 'activeTab' + hostId, expires: 30, path: REWRITE_BASE}});
}

function initNagvisMap() {

}