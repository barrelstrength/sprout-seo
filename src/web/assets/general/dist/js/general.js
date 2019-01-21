/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

'use strict';

$(document).ready(function() {
    var multilingualToggle = $('#settings-enableMultilingualSitemaps-field');

    multilingualToggle.on('change', function() {
        var value = $("input[name='settings[enableMultilingualSitemaps]']").val();
        var $siteWrapper = $("#settings-siteWrapper");
        var $groupWrapper = $("#settings-groupWrapper");

        if (value == 1) {
            $groupWrapper.removeClass("hidden");
            $siteWrapper.addClass("hidden");
        } else {
            $groupWrapper.addClass("hidden");
            $siteWrapper.removeClass("hidden");
        }

    });

});
