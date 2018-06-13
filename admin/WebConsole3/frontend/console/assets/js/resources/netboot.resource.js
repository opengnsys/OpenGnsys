(function(){
	'use strict';
	angular.module(appName).service("NetbootsResource", NetbootsResource);

	NetbootsResource.$inject = ["$resource", '$rootScope', '$q', 'gbnBaseResource','API_URL'];

	function NetbootsResource($resource, $rootScope, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options,
			execute: execute
		}
		var resource = $resource(API_URL+"/netboots/:netbootId.json", {netbootId: "@netbootId"}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
			

		function execute(params) {
			return $resource(API_URL+"/commands/executes.json", {}, {'execute':   {method: 'POST', isArray: true}}).execute(params).$promise;
		}


		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
						"name": {
							type: "text",
							required: true

						},
						"fileName": {
							type: "text",
							required: true

						},
						"template": {
							type: "textarea",
							required: true
						}						
					}
				};
				resolve(opts);
			});
		}
	}
})();