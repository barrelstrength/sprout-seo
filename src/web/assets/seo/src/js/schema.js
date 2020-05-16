class SproutSeoWebsiteIdentitySettings {

  constructor(props) {
    this.items = props.items;
    this.mainEntityValues = props.mainEntityValues;

    this.initLegacyCode();
    this.initOtherLegacyCode();
  }

  initLegacyCode() {
    let self = this;

    // Default option
    let option = '';

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
      let options = '';
      let name = '';

      $.each(children, function(index, value) {
        // insert space before capital letters
        name = index.replace(/([A-Z][^A-Z\b])/g, ' $1').trim();
        options += '<option value="' + index + '">' + name + '</option>';

        // let's foreach the children
        if (value) {
          $.each(value, function(key, level3) {
            name = "&nbsp;&nbsp;&nbsp;" + key.replace(/([A-Z][^A-Z\b])/g, ' $1').trim();
            options += '<option value="' + key + '">' + name + '</option>';
          });
        }

      });

      element.append(options);
    };

    // Select each of the dropdowns
    let firstDropDown = $('.mainentity-firstdropdown select');
    let secondDropDown = $('.mainentity-seconddropdown select');

    // Hold selected option
    let firstSelection = '';
    let secondSelection = '';

    // Hold selection
    let selection = '';

    // Selection handler for first level dropdown
    firstDropDown.on('change', function() {

      // Get selected option
      firstSelection = firstDropDown.val();

      // Clear all dropdowns down to the hierarchy
      clearDropDown($(".organization-info :input"), 1);

      // Disable all dropdowns down to the hierarchy
      disableDropDown($(".organization-info :input"), 1);

      // Check current selection
      if (typeof self.items[firstSelection] === 'undefined' || firstSelection === '' || self.items[firstSelection].length <= 0) {
        return;
      }

      if (self.items[firstSelection]) {
        // Enable second level DropDown
        enableDropDown(secondDropDown);

        // Generate and append options
        generateOptions(secondDropDown, self.items[firstSelection]);
      }

    });

    // Selection handler for second level dropdown
    secondDropDown.on('change', function() {
      let lastValue = secondDropDown.val();
      // Final work goes here
    });
  }

  initOtherLegacyCode() {
    let self = this;
    let mainEntityValues = self.mainEntityValues;

    //Main entity dropdowns
    $('.mainentity-firstdropdown select').change(function() {
      if (this.value === 'barrelstrength-sproutseo-schema-personschema') {
        $('.mainentity-seconddropdown select').addClass('hidden');
      } else {
        $('.mainentity-seconddropdown select').removeClass('hidden');
      }
    });

    // check if we need load depending dropdowns
    if (mainEntityValues) {
      if (mainEntityValues.hasOwnProperty('schemaTypeId') && mainEntityValues.schemaTypeId) {
        $('.mainentity-firstdropdown select').val(mainEntityValues.schemaTypeId).change();
      }
      if (mainEntityValues.hasOwnProperty('schemaOverrideTypeId') && mainEntityValues.schemaOverrideTypeId) {
        $('.mainentity-seconddropdown select').val(mainEntityValues.schemaOverrideTypeId).change();
      }
    }

  }
}

window.SproutSeoWebsiteIdentitySettings = SproutSeoWebsiteIdentitySettings;
