!function(e){var n={};function t(i){if(n[i])return n[i].exports;var o=n[i]={i:i,l:!1,exports:{}};return e[i].call(o.exports,o,o.exports,t),o.l=!0,o.exports}t.m=e,t.c=n,t.d=function(e,n,i){t.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:i})},t.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},t.t=function(e,n){if(1&n&&(e=t(e)),8&n)return e;if(4&n&&"object"==typeof e&&e&&e.__esModule)return e;var i=Object.create(null);if(t.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var o in e)t.d(i,o,function(n){return e[n]}.bind(null,o));return i},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},t.p="/",t(t.s=0)}([function(e,n,t){t(1),t(2),t(3),t(4),e.exports=t(5)},function(e,n){function t(e,n){for(var t=0;t<n.length;t++){var i=n[t];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var i=function(){function e(n){!function(e,n){if(!(e instanceof n))throw new TypeError("Cannot call a class as a function")}(this,e),this.fieldHandle=n.fieldHandle,this.initMetadataFieldButtons()}var n,i,o;return n=e,(i=[{key:"initMetadataFieldButtons",value:function(){var e=this,n="fields-"+this.fieldHandle+"-meta-details-tabs";document.getElementById(n).addEventListener("click",(function(n){var t=n.target,i=t.getAttribute("data-type"),o="#fields-"+e.fieldHandle+"-meta-details-body .fields-"+i,a=document.querySelector(o);t.classList.contains("active")?(a.style.display="none",t.classList.remove("active")):(a.style.display="block",t.classList.add("active"))}))}}])&&t(n.prototype,i),o&&t(n,o),e}();window.SproutSeoMetadataField=i},function(e,n){function t(e,n){for(var t=0;t<n.length;t++){var i=n[t];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var i=function(){function e(n){!function(e,n){if(!(e instanceof n))throw new TypeError("Cannot call a class as a function")}(this,e);var t=this;this.fieldHandle=n.fieldHandle,this.selectFieldId=n.selectFieldId;var i="#fields-"+this.fieldHandle+"-meta-details-body "+this.selectFieldId,o=document.querySelector(i),a=o.options[o.selectedIndex].value;this.currentContainerId=this.getTargetContainerId(a),this.currentContainer=document.getElementById(this.currentContainerId),this.currentContainer&&this.currentContainer.classList.remove("hidden"),o.addEventListener("change",(function(e){t.toggleOpenGraphFieldContainer(e,t)}))}var n,i,o;return n=e,(i=[{key:"toggleOpenGraphFieldContainer",value:function(e,n){var t=e.target,i=t.options[t.selectedIndex].value,o=n.getTargetContainerId(i),a=document.getElementById(o);a&&a.classList.remove("hidden"),n.currentContainer&&n.currentContainer.classList.add("hidden"),n.currentContainerId=o,n.currentContainer=a}},{key:"getTargetContainerId",value:function(e){return"#fields-"+e}}])&&t(n.prototype,i),o&&t(n,o),e}();window.MetaDetailsToggle=i},function(e,n){function t(e,n){for(var t=0;t<n.length;t++){var i=n[t];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var i=function(){function e(n){!function(e,n){if(!(e instanceof n))throw new TypeError("Cannot call a class as a function")}(this,e),this.items=n.items,this.mainEntityValues=n.mainEntityValues,this.initLegacyCode(),this.initOtherLegacyCode()}var n,i,o;return n=e,(i=[{key:"initLegacyCode",value:function(){var e=this,n=$(".mainentity-firstdropdown select"),t=$(".mainentity-seconddropdown select"),i="";n.on("change",(function(){var o,a;i=n.val(),o=$(".organization-info :input"),a=1,$.each(o,(function(e,n){e>=a&&$(n).html("")})),function(e,n){$.each(e,(function(e,t){e>=n&&$(t).closest("div.organizationinfo-dropdown").addClass("hidden")}))}($(".organization-info :input"),1),void 0===e.items[i]||""===i||e.items[i].length<=0||e.items[i]&&(t.closest("div.organizationinfo-dropdown").removeClass("hidden"),function(e,n){var t,i="";$.each(n,(function(e,n){i=e.replace(/([A-Z][^A-Z\b])/g," $1").trim(),t+='<option value="'+e+'">'+i+"</option>",n&&$.each(n,(function(e,n){i="&nbsp;&nbsp;&nbsp;"+e.replace(/([A-Z][^A-Z\b])/g," $1").trim(),t+='<option value="'+e+'">'+i+"</option>"}))})),e.append(t)}(t,e.items[i]))})),t.on("change",(function(){t.val()}))}},{key:"initOtherLegacyCode",value:function(){var e=this.mainEntityValues;$(".mainentity-firstdropdown select").change((function(){"barrelstrength-sproutseo-schema-personschema"===this.value?$(".mainentity-seconddropdown select").addClass("hidden"):$(".mainentity-seconddropdown select").removeClass("hidden")})),e&&(e.hasOwnProperty("schemaTypeId")&&e.schemaTypeId&&$(".mainentity-firstdropdown select").val(e.schemaTypeId).change(),e.hasOwnProperty("schemaOverrideTypeId")&&e.schemaOverrideTypeId&&$(".mainentity-seconddropdown select").val(e.schemaOverrideTypeId).change())}}])&&t(n.prototype,i),o&&t(n,o),e}();window.SproutSeoWebsiteIdentitySettings=i},function(e,n){function t(e,n){for(var t=0;t<n.length;t++){var i=n[t];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var i=function(){function e(n){!function(e,n){if(!(e instanceof n))throw new TypeError("Cannot call a class as a function")}(this,e),this.items=n.items,this.websiteIdentity=n.websiteIdentity,this.firstDropdownId=n.firstDropdownId,this.secondDropdownId=n.secondDropdownId,this.thirdDropdownId=n.thirdDropdownId,this.initWebsiteIdentityField(),this.moreWebsiteIdentityStuff(),this.initKeywordsField()}var n,i,o;return n=e,(i=[{key:"initWebsiteIdentityField",value:function(){var e=this,n=$(this.firstDropdownId),t=$(this.secondDropdownId),i=$(this.thirdDropdownId),o="",a="";n.on("change",(function(){o=n.val(),e.clearDropDown($("#organization :input"),1),e.disableDropDown($("#organization :input"),1),""!==o&&e.items[o].hasOwnProperty("children")&&(e.enableDropDown(t),e.generateOptions(t,e.items[o].children))})),t.on("change",(function(){if(o=$("#first").val(),a=t.val(),e.clearDropDown($("#organization :input"),2),e.disableDropDown($("#organization :input"),2),""!==a)for(var n=e.items[o].children,r=0;r<n.length;r++)if(n[r].name===a){n[r].hasOwnProperty("children")&&(e.enableDropDown(i),e.generateOptions(i,n[r].children));break}})),i.on("change",(function(){i.val()}))}},{key:"moreWebsiteIdentityStuff",value:function(){var e=this.websiteIdentity;e&&(e.hasOwnProperty("organizationSubTypes")&&e.organizationSubTypes[0]&&$("#first").val(e.organizationSubTypes[0]).change(),e.hasOwnProperty("organizationSubTypes")&&e.organizationSubTypes[1]&&$("#second").val(e.organizationSubTypes[1]).change(),e.hasOwnProperty("organizationSubTypes")&&e.organizationSubTypes[2]&&$("#third").val(e.organizationSubTypes[2]).change()),$("#identityType").change((function(){"Person"===this.value?($(".person-info").removeClass("hidden"),$(".organization-info").addClass("hidden")):($(".person-info").addClass("hidden"),$(".organization-info").removeClass("hidden")),"Organization"===this.value?($(".organization-info").removeClass("hidden"),$(".person-info").addClass("hidden"),"LocalBusiness"==$("#first").val()&&$("#localbusiness").removeClass("hidden")):($(".organization-info").addClass("hidden"),$(".person-info").removeClass("hidden"))})),$("#first").change((function(){"LocalBusiness"===this.value?$("#localbusiness").removeClass("hidden"):$("#localbusiness").addClass("hidden")}))}},{key:"initKeywordsField",value:function(){$("#keywords-field input").tagEditor({animateDelete:20})}},{key:"clearDropDown",value:function(e,n){$.each(e,(function(e,t){e>=n&&$(t).html('<option value="" selected="selected"></option>')}))}},{key:"disableDropDown",value:function(e,n){$.each(e,(function(e,t){e>=n&&$(t).closest("div.organizationinfo-dropdown").addClass("hidden")}))}},{key:"enableDropDown",value:function(e){e.closest("div.organizationinfo-dropdown").removeClass("hidden")}},{key:"generateOptions",value:function(e,n){for(var t="",i="",o=0;o<n.length;o++)i=n[o].name.replace(/([A-Z][^A-Z\b])/g," $1").trim(),t+='<option value="'+n[o].name+'">'+i+"</option>";e.append(t)}}])&&t(n.prototype,i),o&&t(n,o),e}();window.SproutSeoWebsiteIdentity=i},function(e,n){}]);