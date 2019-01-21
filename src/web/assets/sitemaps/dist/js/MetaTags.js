/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

(function($) {

    Craft.SproutSeoMetaTags = Garnish.Base.extend(
        {
            // DEFINE VARIABLES
            $selectDropdowns: null,
            $twitterAppendage: null,
            $facebookAppendage: null,

            // INIT VARIABLES
            $twitterDropdown: null,
            $savedTwitterSelectOption: null,
            $currentTwitterElement: null,

            $facebookDropdown: null,
            $savedFacebookSelectOption: null,
            $currentFacebookElement: null,

            $currentElement: null,

            // ONCHANGE VARIABLES
            $appendage: null,
            $newSelectOption: null,
            $newElement: null,

            // ON INIT
            init: function() {
                // @todo - Refactor
                // abstract this!

                // TWITTER
                // select the twitter dropdown so we can grab the value later
                this.$twitterDropdown = $('#metatags-twitter select[name="sproutseo[metadata][twitterCard]"]');
                // grab the value of the current twitter select option
                this.$savedTwitterSelectOption = $(this.$twitterDropdown).val();
                // assign the appendage to twitter element
                this.$twitterAppendage = '#twitter-';
                // concatenate the appendage and the current value
                this.$currentTwitterElement = this.$twitterAppendage + this.$savedTwitterSelectOption;
                // remove the class from the current element
                $(this.$currentTwitterElement).removeClass('hidden');

                // FACEBOOK
                // select the twitter dropdown so we can grab the value later
                this.$facebookDropdown = $('#metatags-facebook select[name="sproutseo[metadata][ogType]"]');
                // grab the value of the current twitter select option
                this.$savedFacebookSelectOption = $(this.$facebookDropdown).val();
                // assign the appendage to twitter element
                this.$facebookAppendage = '#facebook-';
                // concatenate the appendage and the current value
                this.$currentFacebookElement = this.$facebookAppendage + this.$savedFacebookSelectOption;
                // remove the class from the current element
                $(this.$currentFacebookElement).removeClass('hidden');

                // LISTEN UP DRONES!
                this.addListener(this.$twitterDropdown, 'change', 'onTwitterChange');
                this.addListener(this.$facebookDropdown, 'change', 'onFacebookChange');
            },

            // ON TWITTER CHANGE
            onTwitterChange: function(ev) {
                // reassign variables for this listener
                this.$appendage = this.$twitterAppendage;
                this.$currentElement = this.$currentTwitterElement;
                this.$selectDropdown = this.$twitterDropdown;

                // hide the current element on change
                $(this.$currentElement).addClass('hidden');
                // grab the value of the new element
                this.$newSelectOption = $(this.$selectDropdown).val();
                // assign the appendage to twitter element
                this.$newElement = this.$appendage + this.$newSelectOption;
                // remove the class from the new element
                $(this.$newElement).removeClass('hidden');
                // assign the old element to the new one... REBOOT
                this.$currentTwitterElement = this.$newElement;
            },

            // ON FACEBOOK CHANGE
            onFacebookChange: function(ev) {
                // reassign variables for this listener
                this.$appendage = this.$facebookAppendage;
                this.$currentElement = this.$currentFacebookElement;
                this.$selectDropdown = this.$facebookDropdown;

                // hide the current element on change
                $(this.$currentElement).addClass('hidden');
                // grab the value of the new element
                this.$newSelectOption = $(this.$selectDropdown).val();
                // assign the appendage to twitter element
                this.$newElement = this.$appendage + this.$newSelectOption;
                // remove the class from the new element
                $(this.$newElement).removeClass('hidden');
                // assign the old element to the new one... REBOOT
                this.$currentFacebookElement = this.$newElement;
            }
        })

})(jQuery);
