class SproutSeoMetadataField {
  constructor(props) {

    this.fieldHandle = props.fieldHandle;

    this.initMetadataFieldButtons();
  }

  initMetadataFieldButtons() {
    let self = this;
    let metaDetailsTabsId = 'fields-' + this.fieldHandle + '-meta-details-tabs';
    let metaDetailsTabsContainer = document.getElementById(metaDetailsTabsId);

    metaDetailsTabsContainer.addEventListener('click', function(event) {
      let tab = event.target;
      let tabName = tab.getAttribute('data-type');
      let tabBodyClass = '#fields-' + self.fieldHandle + '-meta-details-body .fields-' + tabName;
      let targetBodyContainer = document.querySelector(tabBodyClass);

      if (tab.classList.contains('active')) {
        targetBodyContainer.style.display = 'none';
        tab.classList.remove('active');
      } else {
        targetBodyContainer.style.display = 'block';
        tab.classList.add('active');
      }
    });
  }
}

window.SproutSeoMetadataField = SproutSeoMetadataField;