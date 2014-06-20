(function($) {

// DEFINE THIS THINGY HERE
Craft.SproutSeoTemplate = Garnish.Base.extend(
{
    // DEFINE VARIABLES
    $selectDropdowns: null,

    // ON INIT
    init: function()
    {

        this.$selectDropdowns = $('#template-twitter .select select')

        // ADD LISTENER FOR DROPDOWN
        this.addListener(this.$selectDropdowns, 'change', 'onChange');
    },

    onChange: function(ev)
    {
        // alert("Your book is overdue.");
        this.twitterCard = $('select[name="template_fields[twitterCard]"]');
        this.cardSummary = $('div[]')
        $('.twitter-summary').removeClass('hidden');
    }
}
)

})(jQuery);
