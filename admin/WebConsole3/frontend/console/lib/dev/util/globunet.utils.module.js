(function(){
	'use strict';
	angular.module("globunet.utils",['pascalprecht.translate'])
	.config(translateConfig)
	.run(init);
	init.$inject = ["JsExtensions"];
	function init(JsExtensions){
	}

	translateConfig.$inject = ['$translateProvider'];
	// translate config
	function translateConfig($translateProvider) {

	    // prefix and suffix information  is required to specify a pattern
	    // You can simply use the static-files loader with this pattern:
	    $translateProvider.useStaticFilesLoader({
	        prefix: 'assets/i18n/',
	        suffix: '.json'
	    });

	    // Since you've now registered more then one translation table, angular-translate has to know which one to use.
	    // This is where preferredLanguage(langKey) comes in.
	    $translateProvider.preferredLanguage('en');

	    // Store the language in the local storage
	    $translateProvider.useLocalStorage();

	    // Enable sanitize
	    $translateProvider.useSanitizeValueStrategy('sanitize');
	}


})();