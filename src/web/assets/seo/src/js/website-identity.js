/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

'use strict';

$(document).ready(function() {

    // Default option
    var option = '<option value="" selected="selected"></option>';

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

        for (var i = 0; i < children.length; i++) {
            // insert space before capital letters
            name = children[i].name.replace(/([A-Z][^A-Z\b])/g, ' $1').trim();

            options += '<option value="' + children[i].name + '">' + name + '</option>';
        }
        element.append(options);
    };

    // Select each of the dropdowns
    var firstDropDown = $('#first');
    var secondDropDown = $('#second');
    var thirdDropDown = $('#third');

    // Hold selected option
    var firstSelection = '';
    var secondSelection = '';
    var thirdSelection = '';

    // Hold selection
    var selection = '';

    // Selection handler for first level dropdown
    firstDropDown.on('change', function() {

        // Get selected option
        firstSelection = firstDropDown.val();

        // Clear all dropdowns down to the hierarchy
        clearDropDown($("#organization :input"), 1);

        // Disable all dropdowns down to the hierarchy
        disableDropDown($("#organization :input"), 1);

        // Check current selection
        if (firstSelection === '') {
            return;
        }

        if (items[firstSelection].hasOwnProperty('children')) {
            // Enable second level DropDown
            enableDropDown(secondDropDown);

            // Generate and append options
            generateOptions(secondDropDown, items[firstSelection]['children']);
        }

    });

    // Selection handler for second level dropdown
    secondDropDown.on('change', function() {

        firstSelection = $('#first').val();
        secondSelection = secondDropDown.val();

        // Clear all dropdowns down to the hierarchy
        clearDropDown($("#organization :input"), 2);

        // Disable all dropdowns down to the hierarchy
        disableDropDown($("#organization :input"), 2);

        // Check current selection
        if (secondSelection === '') {
            return;
        }

        var secondChildren = [];
        var children = items[firstSelection]['children'];
        var pos = null;
        for (var i = 0; i < children.length; i++) {
            if (children[i].name === secondSelection) {
                if (children[i].hasOwnProperty('children')) {
                    // Enable third level DropDown
                    enableDropDown(thirdDropDown);

                    // Generate and append options
                    generateOptions(thirdDropDown, children[i]['children']);
                }

                break;
            }
        }

    });

    // Selection handler for third level dropdown
    thirdDropDown.on('change', function() {
        thirdSelection = thirdDropDown.val();
        // Final work goes here

    });

});
