(function(){
	'use strict';
	angular.module(appName).service("SoftwareComponentsResource", SoftwareComponentsResource);

	SoftwareComponentsResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function SoftwareComponentsResource($resource, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options
		}
		var resource = $resource(API_URL+"/softwares/:softwareId.json", {softwareId: "@softwareId"}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
			

		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
						"description":{
							type: "text",
							css: "col-md-6",
							required: true
						},
						"type": {
							type: "select",
							label: "type",
							required: false,
							css: "col-md-6",
							options: {
								source: "vm.softwareTypes",
								label: "name",
								output: "id",
								trackby: " track by item.id"
							}
						}
					}
				};
				resolve(opts);
			});
		}
	}
})();