/**
 * Helper class to toggle groups of sub-fields on Open Graph and Twitter Card Meta Details tabs
 * Initialize the class and identify the target select field to watch. When the select field is
 * updated, this class will look for a matching body div and toggle it's visibility.
 */
class MetaDetailsToggle {

  constructor(props) {
    let self = this;

    this.fieldHandle = props.fieldHandle;
    this.selectFieldId = props.selectFieldId;
    let openGraphTypeInputClass = '#fields-' + this.fieldHandle + '-meta-details-body ' + this.selectFieldId;

    let openGraphTypeDropdown = document.querySelector(openGraphTypeInputClass);

    if (openGraphTypeDropdown !== null) {
      let selectedDropdownOption = openGraphTypeDropdown.options[openGraphTypeDropdown.selectedIndex].value;

      this.currentContainerId = this.getTargetContainerId(selectedDropdownOption);
      this.currentContainer = document.getElementById(this.currentContainerId);

      if (this.currentContainer) {
        this.currentContainer.classList.remove('hidden');
      }

      openGraphTypeDropdown.addEventListener('change', function(event) {
        self.toggleOpenGraphFieldContainer(event, self);
      });
    }
  }

  toggleOpenGraphFieldContainer(event, self) {

    let currentTarget = event.target;
    let newContainerName = currentTarget.options[currentTarget.selectedIndex].value;
    let newContainerId = self.getTargetContainerId(newContainerName);
    let newContainer = document.getElementById(newContainerId);

    // Only update classes if containers exist
    if (newContainer) {
      newContainer.classList.remove('hidden');
    }

    if (self.currentContainer) {
      self.currentContainer.classList.add('hidden');
    }

    // Set new values
    self.currentContainerId = newContainerId;
    self.currentContainer = newContainer;
  }

  getTargetContainerId(selectedOption) {
    return '#fields-' + selectedOption;
  }
}

window.MetaDetailsToggle = MetaDetailsToggle;
