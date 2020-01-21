/* global Craft */

class SproutSeoMetadataField {
  constructor(props) {
    this.fieldHandle = props.fieldHandle;
    this.seoBadgeInfo = props.seoBadgeInfo;
    this.maxDescriptionLength = props.maxDescriptionLength;

    this.initMetadataFieldButtons();
    this.addSeoBadgesToUi();
  }

  initMetadataFieldButtons() {
    let self = this;

    // Checking active class
    let fields = ['Search', 'OpenGraph', 'Geo', 'Robots', 'TwitterCard'];

    for (let fieldName of fields) {
      let customKey = "enableMetaDetails" + fieldName;
      let $customizationSettings = self.getCustomizationSettings(customKey);

      // console.log($customizationSettings.val());
      if ($customizationSettings.val() === '1') {
        $('#btn-' + fieldName).addClass('active');
        $('#fields-btn-' + fieldName).addClass('active');
      }
    }

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

  addSeoBadgesToUi() {
    let self = this;

    for (let key in this.seoBadgeInfo) {
      let type = this.seoBadgeInfo[key]['type'];
      let fieldHandle = this.seoBadgeInfo[key]['handle'];
      let badgeClass = this.seoBadgeInfo[key]['badgeClass'];

      let seoButton = $('div.'+badgeClass).html();

      let metaLabelId = '#fields-' + fieldHandle + '-label';
      let metaInputId = '#fields-' + fieldHandle + '-field input';

      let metaInput = $(metaInputId);

      if (fieldHandle === 'title') {
        metaLabelId = '#title-label';
        metaInput = $('#title');
      }

      self.appendSeoBadge(metaLabelId, seoButton);
      Craft.initUiElements($(metaLabelId));

      if (type === 'optimizedTitleField') {
        metaInput.attr('maxlength', 60);
        new Garnish.NiceText(metaInput, {showCharsLeft: true});
      }

      if (type === 'optimizedDescriptionField') {
        let metaTextareaId = '#fields-' + fieldHandle + '-field textarea';
        let metaTextarea = $(metaTextareaId);
        metaTextarea.attr('maxlength', self.maxDescriptionLength);

        // triggers Double instantiating console error
        new Garnish.NiceText(metaTextarea, { showCharsLeft: true });
      }
    }
  }

  getCustomizationSettings(customKey) {
    return $("input[name='fields[" + this.fieldHandle + "][metadata][" + customKey + "]']");
  }

  appendSeoBadge(targetLabelId, seoButton) {
    if ($(targetLabelId).find('.sproutseo-info').length === 0) {
      $(targetLabelId).append(seoButton).removeClass('hidden');
    }
  }
}

class SproutSeoKeywordsField {
  constructor(props) {
    this.keywordsFieldId = props.keywordsFieldId;

    this.initKeywordsField();
  }

  initKeywordsField() {
    $(this.keywordsFieldId + ' input').tagEditor({
      animateDelete: 20
    });
  }
}

window.SproutSeoMetadataField = SproutSeoMetadataField;
window.SproutSeoKeywordsField = SproutSeoKeywordsField;