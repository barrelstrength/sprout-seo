(function($) {

Craft.SproutSeoTemplate = Garnish.Base.extend(
{
    // DEFINE VARIABLES
    $selectTwitterDropdown: null,
    $oldSelectOption: null,
    $newSelectOption: null,

    $targetedElement: null,

    // ON INIT
    // @TODO clean this up asap...
    init: function()
    {

        // @DONE chose the element to target
        this.$selectDropdowns = $('form select');
            console.log(this.$selectDropdowns);

        // @DONE Add listener for this.$selectTwitterDropdown to fire onChange
        this.addListener(this.$selectDropdowns, 'change', 'onChange');
    },

    // ON CHANGE
    onChange: function(ev)
    {

        // Perform actions only if bacebook is the open tab
        if ( $('#template-twitter').hasClass('hidden') && !$('#template-facebook').hasClass('hidden') )
        {
            alert('hello facebook');
            this.$selectDropdown = $('#template-facebook select[name="template_fields[oGType]"]');
            this.$appendage = '#facebook-';
            this.$oldSelectOption = this.$selectDropdown.val();
            this.$targetedElement = this.$appendage + this.$oldSelectOption;
            $(this.$targetedElement).removeClass('hidden');
        }
        else if ( $('#template-facebook').hasClass('hidden') && !$('#template-twitter').hasClass('hidden') )
        {
            alert('hello twitter');
            this.$selectDropdown = $('#template-twitter select[name="template_fields[twitterCard]"]');
            this.$appendage = '#twitter-';
            this.$oldSelectOption = this.$selectDropdown.val();
            this.$targetedElement = this.$appendage + this.$oldSelectOption;
            $(this.$targetedElement).removeClass('hidden');
        }
        else {
            // void
        }

        // @DONE Add hidden class to $targetedElement
        $(this.$targetedElement).addClass('hidden');

        // @DONE select the new value to the card type
        this.$newSelectOption = this.$selectDropdown.val();

        // @DONE target the new element
        this.$targetedElement = this.$appendage + this.$newSelectOption;

        // @DONE Remove the hidden class from the div
        $(this.$targetedElement).removeClass('hidden');

    }
})


})(jQuery);
