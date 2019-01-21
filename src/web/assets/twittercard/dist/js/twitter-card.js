/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

(function($) {

    Craft.SproutSeoTwitterCard = Garnish.Base.extend(
        {
            $appendage: null,

            $select: null,
            $selectValue: null,
            $currentDiv: null,

            $newSelect: null,
            $newSelectValue: null,
            $newDiv: null,

            // ON INIT
            init: function() {
                // select the dropdown to target
                this.$select = $('#fields-twitter-card select');
                // console.log(this.$select);

                // grab the value from the dropdown
                this.$selectValue = $(this.$select).val();
                // console.log(this.$selectValue);

                // define the appendage to concatenate
                this.$appendage = '#fields-';
                // console.log(this.$appendage);

                // concatenate the current div for later use
                this.$currentDiv = this.$appendage + this.$selectValue;
                // console.log(this.$currentDiv);

                // remove the hidden class from current div
                $(this.$currentDiv).removeClass('hidden');
                // console.log('remove class hidden from this.$currentDiv');

                // LISTEN UP DRONES!!!
                this.addListener(this.$select, 'change', 'onChange');
            },

            // ON CHANGE
            onChange: function(ev) {
                // hide the current div
                $(this.$currentDiv).addClass('hidden');
                // console.log('add class hidden to this.$currentDiv');

                // grab the value from the dropdown
                this.$newSelectValue = $(this.$select).val();
                // console.log(this.$selectValue);

                // concatenate the new div for later use
                this.$newDiv = this.$appendage + this.$newSelectValue;
                // console.log(this.$newDiv);

                // remove the hidden class from new div
                $(this.$newDiv).removeClass('hidden');

                // REBOOT!!!
                this.$currentDiv = this.$newDiv;
                // console.log(this.$newDiv);

            }
        })

})(jQuery);
