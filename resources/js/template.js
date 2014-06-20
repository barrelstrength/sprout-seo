(function($) {

Craft.SproutSeoTemplate = Garnish.Base.extend(
{
    // DEFINE VARIABLES
    $selectTwitterDropdown: null,
    $oldTwitterCardType: null,
    $newTwitterCardType: null,

    $appendage: null,

    $targetedElement: null,

    // ON INIT
    // @TODO clean this up asap...
    init: function()
    {

        // @DONE chose the element to target
        this.$selectTwitterDropdown = $('#template-twitter select[name="template_fields[twitterCard]"]');;

        // @DONE Select the current value of the Twitter Card on load
        this.$oldTwitterCardType = this.$selectTwitterDropdown.val();

        // @DONE concatenate the card type and div appendage
        this.$appendage = '#twitter-';
        this.$targetedElement = this.$appendage + this.$oldTwitterCardType;

        // @DONE Remove the hidden class from the oldTwitterCardType div
        $(this.$targetedElement).removeClass('hidden');

        // @DONE Add listener for this.$selectTwitterDropdown to fire onChange
        this.addListener(this.$selectTwitterDropdown, 'change', 'onChange');
    },

    // ON CHANGE
    onChange: function(ev)
    {
        // @DONE Add hidden class to $targetedElement
        $(this.$targetedElement).addClass('hidden');

        // @DONE select the new value to the card type
        this.$newTwitterCardType = this.$selectTwitterDropdown.val();

        // @DONE target the new element
        this.$targetedElement = this.$appendage + this.$newTwitterCardType;

        // @DONE Remove the hidden class from the newTwitterCardType div
        $(this.$targetedElement).removeClass('hidden');
    }
}
)

})(jQuery);
