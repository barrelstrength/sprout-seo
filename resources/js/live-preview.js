var SproutSEOLivePreview = (function () {
  var _config = {
    seoPreviewButtonSelector: '#fields-sproutseo-seopreview',
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
    var data = $('form#container').serialize();

    Craft.postActionRequest('sproutSeo/livePreview/getPrioritizedMetadata', data, function(response) {
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