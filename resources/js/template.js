(function($) {

Craft.SproutSeoTemplate = Garnish.Base.extend(
{
    // DEFINE VARIABLES
    $selectDropdowns: null,
    $currentTwitterCardType: null,
    $savedTwitterCardType: null,

    $targetClass: null,
    $appendage: null,

    $targetedElement: null,
    $newTargetedElement: null,

    // TEST
    $test: null,

    // ON INIT
    init: function()
    {

        this.$selectDropdowns = $('#template-twitter select[name="template_fields[twitterCard]"]');

        this.$savedTwitterCardType = ;

        // @DONE Select the current value of the Twitter Card on load
        this.$currentTwitterCardType = this.$selectDropdowns.val();
        // console.log(this.$currentTwitterCardType);

        // @DONE concatenate the card type and div append
        this.$appendage = '#twitter-';
        this.$targetedElement = this.$appendage + this.$currentTwitterCardType;
            // console.log(this.$targetedElement);

        // @TODO add if else for null targetedElement (no selection)
        // @DONE Remove the hidden class from the current twitterCard div
        $(this.$targetedElement).removeClass('hidden');
            // alert(newClass);
            // console.log(this.$currentTwitterCardType);

        // Add listener for dropdown
        this.addListener(this.$selectDropdowns, 'change', 'onChange');

    },

    // ON CHANGE
    onChange: function(ev)
    {

        // TASKS:

        // @DONE Add hidden class to $targetedElement
        $(this.$targetedElement).addClass('hidden');

        // @DONE Select the newtargetedElement
        this.$newTargetedElement = this.$selectDropdowns.val();
            // this.$test = this.$appendage + this.$newTargetedElement;
            // console.log(this.$test);

        $(this.$newTargetedElement).removeClass('hidden');

        // 2. Remove class from newTargetedElement
        // $(this.$newtarget

        // @DONE Grab new value of the Twitter Card select
        // Whatever the users selects
        // this.twitterCard = this.$selectDropdowns.val();
        // // console.log(this.twitterCard);
        //
        // this.changedSelect = this.twitterCard.val();
        //
        // // Remove the hidden class from the div based on selection
        // $(this.changedSelect).removeClass('hidden');
        // alert(this.changedSelect);
        //
        // this.$currentTwitterCardType = this.twitterCard;

    }
}
)

})(jQuery);
