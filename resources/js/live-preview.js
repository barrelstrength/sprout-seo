var SproutSEOLivePreview = (function () {
  var scenario = $('#sproutseo-preview').length ? '' : 'fields-' ;
  var _config = {
    seoPreviewButtonSelector: '#'+scenario+'sproutseo-seopreview',
    targets : {
      searchEngine: {
        titleSelector: ".google-result-heading",
        descriptionSelector: ".google-result-description"
      },
      openGraph: {
        titleSelector: ".og-heading",
        descriptionSelector: ".og-description",
        imageSelector: ".opengraph-preview .preview-img img"
      },
      twitterCard: {
        titleSelector: ".SummaryCard-content h2",
        descriptionSelector: ".SummaryCard-content p",
        imageSelector: ".SummaryCard-image img"
      }
    }
  };

  var getInputScenario =function (customKey, type = 'input'){
    if ($(type+"[name='fields["+_config.nameContext+"][metadata]"+customKey+"']").length)
    {
      return $(type+"[name='fields["+_config.nameContext+"][metadata]"+customKey+"']");
    }
    else
    {
      return $(type+"[name='sproutseo[metadata]"+customKey+"']");
    }
  };
  var init = function (options) {
    if (typeof(options) === 'object') {
      $.extend(_config, options);
    }

    _setUpEvents();
    console.log('_config: ', _config);
  };

  var _setUpEvents = function() {
    $(_config.seoPreviewButtonSelector).on('click', function(){
      _updateMetadata();
    });
  };

  var _updateMetadata = function() {
    var metadata = {};

    // get title value
    if ('selector' in _config.sources.title) {
      metadata.title = $(_config.sources.title.selector).val();
    }
    if ('template' in _config.sources.title) {
      metadata.title = {};
      metadata.title.fields = {};
      metadata.title.template = _config.sources.title.template;

      $.each(_config.sources.title.fields, function(index, value) {
        metadata.title.fields[value] = $('#'+scenario+ value).val();
      });
    }

    // get description value
    if ('selector' in _config.sources.description) {
      metadata.description = $(_config.sources.description.selector).val();
    }
    if ('template' in _config.sources.description) {
      metadata.description = {};
      metadata.description.fields = {};
      metadata.description.template = _config.sources.description.template;

      $.each(_config.sources.description.fields, function(index, value) {
        metadata.description.fields[value] = $('#'+scenario+ value).val();
      });
    }

    // get image value
    if ('selector' in _config.sources.image) {
      metadata.image = $(_config.sources.image.selector + ' input[type=hidden]').val();
    }
    // Get the twitter and facebook values

    var $facebookBtn = $('#'+scenario+'btn-OpenGraph');
    var $twitterBtn = $('#'+scenario+'btn-TwitterCard');

    if ($facebookBtn.length)
    {
      if ($facebookBtn.hasClass('active'))
      {
        var ogTitle = getInputScenario('[ogTitle]').val();
        var ogDescription = getInputScenario('[ogDescription]', 'textarea').val();
        var ogType = getInputScenario('[ogType]', 'select').val();
        var ogImage = getInputScenario('[ogImage][]').val();

        if (ogTitle)
        {
          metadata.ogTitle = ogTitle;
        }
        if (ogDescription)
        {
          metadata.ogDescription = ogDescription;
        }
        if (ogImage)
        {
          metadata.ogImage = ogImage;
        }
        if (ogType)
        {
          metadata.ogType = ogType;
        }
      }
    }

    if ($twitterBtn.length)
    {
      if ($facebookBtn.hasClass('active'))
      {
        var twitterTitle = getInputScenario('[twitterTitle]').val();
        var twitterDescription = getInputScenario('[twitterDescription]', 'textarea').val();
        var twitterCard = getInputScenario('[twitterCard]', 'select').val();
        var twitterImage = getInputScenario('[twitterImage][]').val();

        if (twitterTitle)
        {
          metadata.twitterTitle = twitterTitle;
        }
        if (twitterDescription)
        {
          metadata.twitterDescription = twitterDescription;
        }
        if (twitterImage)
        {
          metadata.twitterImage = twitterImage;
        }
        if (twitterCard)
        {
          metadata.twitterCard = twitterCard;
        }
      }
    }
    // Let's send the scenario

    var variableNames = _config.variableIdNames;
    var data = null;

    for (var i = variableNames.length - 1; i >= 0; i--)
    {
      if ($('input[name='+variableNames[i]+']').length)
      {
        // prepare data
        var data = {
          variableNameId: variableNames[i],
          variableIdValue: $('input[name='+variableNames[i]+']').val(),
          metadata: metadata
        };
        break;
      }
    }
    console.log("before send");
    console.log(data);
    // it's a SproutSEO section
    if (data == null)
    {
      if ($('input[name="sproutseo[metadata][urlEnabledSectionId]"]').length)
      {
        console.log("BINGO SECTION: "+$('input[name="sproutseo[metadata][urlEnabledSectionId]"]').val());
      }
    }

    Craft.postActionRequest('sproutSeo/livePreview/getPrioritizedMetadata', data, function(response) {
      console.log('response: ', response);

       if (response.success)
       {
         var optimized = response.optimized;
         _updateSearchEngineMetaData(optimized.meta.search);
         _updateOpenGraphMetaData(optimized.meta.openGraph);
         _updateTwitterCardMetaData(optimized.meta.twitterCard);
       }
       else
       {
        console.log('errors: ', response.errors);
       }
    });
  };

  var _updateSearchEngineMetaData = function(data) {
    _updateContent(_config.targets.searchEngine.titleSelector, data.title);
    _updateContent(_config.targets.searchEngine.descriptionSelector, data.description);
  };

  var _updateOpenGraphMetaData = function(data) {
    _updateContent(_config.targets.openGraph.titleSelector, data['og:title']);
    _updateContent(_config.targets.openGraph.descriptionSelector, data['og:description']);
    _updateContent(_config.targets.openGraph.imageSelector, data['og:image'], 'src')
  };

  var _updateTwitterCardMetaData = function(data) {
    _updateContent(_config.targets.twitterCard.titleSelector, data['twitter:title']);
    _updateContent(_config.targets.twitterCard.descriptionSelector, data['twitter:description']);
    _updateContent(_config.targets.twitterCard.imageSelector, data['twitter:image'], 'src')
  };

  var _updateContent = function (targetSelector, content, attribute) {

    if (typeof attribute !== 'undefined') {
      $(targetSelector).attr(attribute, content);
    }
    else {
      $(targetSelector).html(content);
    }
  };

  return {
    init: init,
  };

})();