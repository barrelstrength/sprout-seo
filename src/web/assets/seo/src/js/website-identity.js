class SproutSeoWebsiteIdentity {

  constructor(props) {
    this.items = props.items;
    this.websiteIdentity = props.websiteIdentity;

    this.firstDropdownId = props.firstDropdownId;
    this.secondDropdownId = props.secondDropdownId;
    this.thirdDropdownId = props.thirdDropdownId;

    this.initWebsiteIdentityField();
    this.moreWebsiteIdentityStuff();
  }

  initWebsiteIdentityField() {
    let self = this;

    // Select each of the dropdowns
    let firstDropDown = $(this.firstDropdownId);
    let secondDropDown = $(this.secondDropdownId);
    let thirdDropDown = $(this.thirdDropdownId);

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
      self.clearDropDown($("#organization :input"), 1);

      // Disable all dropdowns down to the hierarchy
      self.disableDropDown($("#organization :input"), 1);

      // Check current selection
      if (firstSelection === '') {
        return;
      }

      if (self.items[firstSelection].hasOwnProperty('children')) {
        // Enable second level DropDown
        self.enableDropDown(secondDropDown);

        // Generate and append options
        self.generateOptions(secondDropDown, self.items[firstSelection]['children']);
      }

    });

    // Selection handler for second level dropdown
    secondDropDown.on('change', function() {

      firstSelection = $('#main-entity-first-dropdown').val();
      secondSelection = secondDropDown.val();

      // Clear all dropdowns down to the hierarchy
      self.clearDropDown($("#organization :input"), 2);

      // Disable all dropdowns down to the hierarchy
      self.disableDropDown($("#organization :input"), 2);

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
            self.enableDropDown(thirdDropDown);

            // Generate and append options
            self.generateOptions(thirdDropDown, children[i]['children']);
          }

          break;
        }
      }

    });
  }

  moreWebsiteIdentityStuff() {
    let websiteIdentity = this.websiteIdentity;

    // check if we need load depending dropdowns
    if (websiteIdentity) {
      if (websiteIdentity.hasOwnProperty('organizationSubTypes') && websiteIdentity.organizationSubTypes[0]) {
        $('#main-entity-first-dropdown').val(websiteIdentity.organizationSubTypes[0]).trigger('change');
      }
      if (websiteIdentity.hasOwnProperty('organizationSubTypes') && websiteIdentity.organizationSubTypes[1]) {
        $('#main-entity-second-dropdown').val(websiteIdentity.organizationSubTypes[1]).trigger('change');
      }
      if (websiteIdentity.hasOwnProperty('organizationSubTypes') && websiteIdentity.organizationSubTypes[2]) {
        $('#main-entity-third-dropdown').val(websiteIdentity.organizationSubTypes[2]).trigger('change');
      }
    }

    $("#identityType").change(function() {

      if (this.value === 'Person') {
        $(".person-info").removeClass('hidden');
        $(".organization-info").addClass('hidden');
      } else {
        $(".person-info").addClass('hidden');
        $(".organization-info").removeClass('hidden');
      }

      if (this.value === 'Organization') {
        $(".organization-info").removeClass('hidden');
        $(".person-info").addClass('hidden');

        if ($("#main-entity-first-dropdown").val() === 'LocalBusiness') {
          $("#localbusiness").removeClass('hidden');
        }
      } else {
        $(".organization-info").addClass('hidden');
        $(".person-info").removeClass('hidden');
      }
    });

    $("#main-entity-first-dropdown").change(function() {
      if (this.value === 'LocalBusiness') {
        $("#localbusiness").removeClass('hidden');
      } else {
        $("#localbusiness").addClass('hidden');
      }
    });
  }

  // Clear dropdowns down to a given level
  clearDropDown(arrayObj, startIndex) {

    // Default option passed to html() on Globals page
    let option = '<option value="" selected="selected"></option>';

    // From Metadata Field settings page:
    // var option = '';

    $.each(arrayObj, function(index, value) {
      if (index >= startIndex) {
        $(value).html(option);
      }
    });
  };

  // Disable dropdowns down to a given level
  disableDropDown(arrayObj, startIndex) {
    $.each(arrayObj, function(index, value) {
      if (index >= startIndex) {
        $(value).closest('div.organizationinfo-dropdown').addClass('hidden');
      }
    });
  };

  // Enable dropdowns down to a given level
  enableDropDown(element) {
    element.closest('div.organizationinfo-dropdown').removeClass('hidden');
  };

  // Generate and append options
  generateOptions(element, children) {
    let options = '';
    let name = '';

    for (let i = 0; i < children.length; i++) {
      // insert space before capital letters
      name = children[i].name.replace(/([A-Z][^A-Z\b])/g, ' $1').trim();

      options += '<option value="' + children[i].name + '">' + name + '</option>';
    }

    element.append(options);
  };
}

window.SproutSeoWebsiteIdentity = SproutSeoWebsiteIdentity;
