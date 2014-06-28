(function($) {

Craft.SproutSeoDefault = Garnish.Base.extend(
{
    // DEFINE VARIABLES
    $selectDropdowns: null,
    $twitterAppendage: null,

    // INIT VARIABLES
    $twitterDropdown: null,
    $oldTwitterSelectOption: null,
    $currentTwitterElement: null,
    $oldElement: null,

    // ONCHANGE VARIABLES
    $appendage: null,
    $newSelectOption: null,
    $newElement: null,

    // ON INIT
    init: function()
    {
        // TWITTER
        // select the twitter dropdown so we can grab the value later
        this.$twitterDropdown = $('#default-twitter select[name="default_fields[twitterCard]"]');
        // grab the value of the current twitter select option
        this.$oldTwitterSelectOption = $(this.$twitterDropdown).val();
        // assign the appendage to twitter element
        this.$twitterAppendage = '#twitter-';
        // concatenate the appendage and the current value
        this.$currentTwitterElement = this.$twitterAppendage + this.$oldTwitterSelectOption;
        // remove the class from the current element
        $(this.$currentTwitterElement).removeClass('hidden');

        // LISTEN UP DRONES!
        this.addListener(this.$twitterDropdown, 'change', 'onTwitterChange');
    },

    // ON TWITTER CHANGE
    onTwitterChange: function(ev)
    {
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
        this.$currentElement = this.$newElement;
            console.log(this.$currentElement);

    }
})


})(jQuery);
