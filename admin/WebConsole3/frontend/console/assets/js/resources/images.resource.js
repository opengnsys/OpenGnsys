(function(){
	'use strict';
	angular.module(appName).service("ImagesResource", ImagesResource);

	ImagesResource.$inject = ["$resource", '$q', '$rootScope', 'gbnBaseResource','API_URL'];

	function ImagesResource($resource, $q, $rootScope, gbnBaseResource, API_URL){
		var methods = {
			options: options
		};

		var resource = $resource(API_URL+"/images/:imageId.json", {imageId: "@imageId"}, {'update': {method:'PATCH'}});


		var repoResource = gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
		
		return repoResource;

		function options(){
			return $q(function(resolve, reject){

				var opts = {
					fields: {
						rows:[
							{
								"canonicalName": {
									type: "text",
									label: "canonicalName",
									required: true,
									css: "col-md-6",
									pattern: "[a-zA-Z0-9_]+"
								},
								"repository": {
									type: "select",
									label: "repository",
									required: true,
									css: "col-md-6",
									options: {
										source: "vm.repositories",
										label: "name",
										output: "id",
										trackby: " track by item.id"
									},
								}
							},
							{
								"description": {
									type: "textarea",
									required: true,
									css: "col-md-12"
								},
								"comments": {
									type: "textarea",
									required: false,
									css: "col-md-12"
								}
							}
						]
					}
				};
				resolve(opts);
			});
		}
	}
})();