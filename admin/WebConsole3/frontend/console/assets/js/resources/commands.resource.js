(function(){
	'use strict';
	angular.module(appName).service("CommandsResource", CommandsResource);

	CommandsResource.$inject = ["$resource", '$rootScope', '$q', 'gbnBaseResource','API_URL'];

	function CommandsResource($resource, $rootScope, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options,
			execute: execute
		}
		var resource = $resource(API_URL+"/commands/:commandId.json", {commandId: "@commandId"}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
			

		function execute(params) {
			return $resource(API_URL+"/commands/executes.json", {}, {'execute':   {method: 'POST', isArray: false}}).execute(params).$promise;
		}


		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
						"title": {
							type: "text",
							required: true

						},
						"script": {
							type: "textarea",
							required: true
						},
						"parameters": {
							type: "checkbox",
							required: true,
							ngChange: "vm.parametersCheck(value)"
						}
					}
				};
				resolve(opts);
			});
		}
	}
})();