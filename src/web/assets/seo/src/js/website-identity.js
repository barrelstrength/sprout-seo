class SproutSeoWebsiteIdentity {
    constructor(props) {
        this.items = props.items;
        this.websiteIdentity = props.websiteIdentity;

        this.initWebsiteIdentityField();
        this.moreWebsiteIdentityStuff();
        this.initKeywordsField();
    }

    initWebsiteIdentityField() {
        let self = this;

        // Default option
        let option = '<option value="" selected="selected"></option>';

        // Method to clear dropdowns down to a given level
        let clearDropDown = function(arrayObj, startIndex) {
            $.each(arrayObj, function(index, value) {
                if (index >= startIndex) {
                    $(value).html(option);
                }
            });
        };

        // Method to disable dropdowns down to a given level
        let disableDropDown = function(arrayObj, startIndex) {
            $.each(arrayObj, function(index, value) {
                if (index >= startIndex) {
                    $(value).closest('div.organizationinfo-dropdown').addClass('hidden');
                }
            });
        };

        // Method to enable dropdowns down to a given level
        let enableDropDown = function(element) {
            element.closest('div.organizationinfo-dropdown').removeClass('hidden');
        };

        // Method to generate and append options
        let generateOptions = function(element, children) {
            let options, name = '';

            for (let i = 0; i < children.length; i++) {
                // insert space before capital letters
                name = children[i].name.replace(/([A-Z][^A-Z\b])/g, ' $1').trim();

                options += '<option value="' + children[i].name + '">' + name + '</option>';
            }
            element.append(options);
        };

        // Select each of the dropdowns
        let firstDropDown = $('#first');
        let secondDropDown = $('#second');
        let thirdDropDown = $('#third');

        // Hold selected option
        let firstSelection = '';
        let secondSelection = '';
        let thirdSelection = '';

        // Hold selection
        let selection = '';

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

            if (self.items[firstSelection].hasOwnProperty('children')) {
                // Enable second level DropDown
                enableDropDown(secondDropDown);

                // Generate and append options
                generateOptions(secondDropDown, self.items[firstSelection]['children']);
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

            let secondChildren = [];
            let children = self.items[firstSelection]['children'];
            let pos = null;
            for (let i = 0; i < children.length; i++) {
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
    }

    moreWebsiteIdentityStuff() {
        let websiteIdentity = this.websiteIdentity;

        // check if we need load depending dropdowns
        if (websiteIdentity)
        {
            if (websiteIdentity.hasOwnProperty('organizationSubTypes') && websiteIdentity.organizationSubTypes[0])
            {
                $('#first').val(websiteIdentity.organizationSubTypes[0]).change();
            }
            if (websiteIdentity.hasOwnProperty('organizationSubTypes') && websiteIdentity.organizationSubTypes[1])
            {
                $('#second').val(websiteIdentity.organizationSubTypes[1]).change();
            }
            if (websiteIdentity.hasOwnProperty('organizationSubTypes') && websiteIdentity.organizationSubTypes[2])
            {
                $('#third').val(websiteIdentity.organizationSubTypes[2]).change();
            }
        }

        $( "#identityType" ).change(function() {

            if(this.value === 'Person')
            {
                $( ".person-info" ).removeClass('hidden');
                $( ".organization-info" ).addClass('hidden');
            }
            else
            {
                $( ".person-info" ).addClass('hidden');
                $( ".organization-info" ).removeClass('hidden');
            }

            if(this.value === 'Organization')
            {
                $( ".organization-info" ).removeClass('hidden');
                $( ".person-info" ).addClass('hidden');

                if ($( "#first" ).val() == 'LocalBusiness')
                {
                    $( "#localbusiness" ).removeClass('hidden');
                }
            }
            else
            {
                $( ".organization-info" ).addClass('hidden');
                $( ".person-info" ).removeClass('hidden');
            }
        });

        $( "#first" ).change(function() {
            if(this.value === 'LocalBusiness')
            {
                $( "#localbusiness" ).removeClass('hidden');
            }
            else
            {
                $( "#localbusiness" ).addClass('hidden');
            }
        });
    }

    initKeywordsField() {
        $('#keywords-field input').tagEditor({
            animateDelete: 20
        });
    }
}

window.SproutSeoWebsiteIdentity = SproutSeoWebsiteIdentity;

