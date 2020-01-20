/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

'use strict';

$(document).ready(function() {

    // Default option
    var option = '';

    // Method to clear dropdowns down to a given level
    var clearDropDown = function(arrayObj, startIndex) {
        $.each(arrayObj, function(index, value) {
            if (index >= startIndex) {
                $(value).html(option);
            }
        });
    };

    // Method to disable dropdowns down to a given level
    var disableDropDown = function(arrayObj, startIndex) {
        $.each(arrayObj, function(index, value) {
            if (index >= startIndex) {
                $(value).closest('div.organizationinfo-dropdown').addClass('hidden');
            }
        });
    };

    // Method to enable dropdowns down to a given level
    var enableDropDown = function(element) {
        element.closest('div.organizationinfo-dropdown').removeClass('hidden');
    };

    // Method to generate and append options
    var generateOptions = function(element, children) {
        var options, name = '';

        $.each(children, function(index, value) {
            // insert space before capital letters
            name = index.replace(/([A-Z][^A-Z\b])/g, ' $1').trim();
            options += '<option value="' + index + '">' + name + '</option>';

            // let's foreach the children
            if (value) {
                $.each(value, function(key, level3) {
                    name = "&nbsp;&nbsp;&nbsp;" + key.replace(/([A-Z][^A-Z\b])/g, ' $1').trim();
                    options += '<option value="' + key + '">' + name + '</option>';
                });
            }

        });

        element.append(options);
    };

    // Select each of the dropdowns
    var firstDropDown = $('.mainentity-firstdropdown select');
    var secondDropDown = $('.mainentity-seconddropdown select');

    // Hold selected option
    var firstSelection = '';
    var secondSelection = '';

    // Hold selection
    var selection = '';

    // Selection handler for first level dropdown
    firstDropDown.on('change', function() {

        // Get selected option
        firstSelection = firstDropDown.val();

        // Clear all dropdowns down to the hierarchy
        clearDropDown($(".organization-info :input"), 1);

        // Disable all dropdowns down to the hierarchy
        disableDropDown($(".organization-info :input"), 1);

        // Check current selection
        if (typeof items[firstSelection] === 'undefined' || firstSelection === '' || items[firstSelection].length <= 0) {
            return;
        }

        if (items[firstSelection]) {
            // Enable second level DropDown
            enableDropDown(secondDropDown);

            // Generate and append options
            generateOptions(secondDropDown, items[firstSelection]);
        }

    });

    // Selection handler for second level dropdown
    secondDropDown.on('change', function() {
        var lastValue = secondDropDown.val();
        // Final work goes here
    });

});
