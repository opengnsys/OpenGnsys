(function(){
	'use strict';
	angular.module(appName).service("NetbootsResource", NetbootsResource);

	NetbootsResource.$inject = ["$resource", '$rootScope', '$q', 'gbnBaseResource','API_URL'];

	function NetbootsResource($resource, $rootScope, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options,
			updateFiles: updateFiles
		}
		var resource = $resource(API_URL+"/netboots/:netbootId.json", {netbootId: "@netbootId"}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
			

		function updateFiles(params) {
			return $resource(API_URL+"/netboots/clients.json").save(params).$promise;
		}


		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
						rows: [
							{
								"name": {
									css: "col-md-6",
									type: "text",
									required: true

								},
								"filename": {
									css: "col-md-6",
									type: "text",
									required: true

								},
							},
							{
								"template": {
									css: "col-md-12 netboot-template",
									type: "textarea",
									required: true
							}
						}]				
					}
				};
				resolve(opts);
			});
		}
	}
})();