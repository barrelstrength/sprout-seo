(function($) {

Craft.SproutSeoTemplate = Garnish.Base.extend(
{
    // DEFINE VARIABLES
    $selectDropdowns: null,
    $appendage: null,

    // INIT VARIABLES
    $oldSelectOption: null,
    $oldElement: null,

    // ONCHANGE VARIABLES
    $newSelectOption: null,
    $newElement: null,

    // ON INIT
    init: function()
    {
        // 1. choose the select to target
        this.$selectDropdowns = $('.sproutseo-box select');
        // 2. grab the old value from the select
        this.$oldSelectOption = $('.sproutseo-box select').val();
        // 3. define the appendage to concatenate
        this.$appendage = '#fields-facebook-';
        // 4. define oldElement to remove the class
        this.$oldElement = this.$appendage + this.$oldSelectOption;
        // 5. remove the class from the oldElement
        $(this.$oldElement).removeClass('hidden');

        // @DONE Add listener for this.$selectTwitterDropdown to fire onChange
        this.addListener(this.$selectDropdowns, 'change', 'onChange');
    },

    // ON CHANGE
    onChange: function(ev)
    {
        // 1. hide the oldElement
        $(this.$oldElement).addClass('hidden');
        // 2. show the new card type
        this.$newSelectOption = $(this.$selectDropdowns).val();
        // 3. append the newSelectOption and appendage
        this.$newElement = this.$appendage + this.$newSelectOption;
        // 4. remove class hidden from targetedElement
        $(this.$newElement).removeClass('hidden');
        // 5. assign the old variable to the new one... REBOOT
        this.$oldElement = this.$newElement;
    }
})


})(jQuery);
