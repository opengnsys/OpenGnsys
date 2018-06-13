(function(){
	'use strict';
	angular.module(appName).service("HardwareComponentsResource", HardwareComponentsResource);

	HardwareComponentsResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function HardwareComponentsResource($resource, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options
		}
		var resource = $resource(API_URL+"/hardwares/:hardwareId.json", {hardwareId: "@hardwareId"}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
			

		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
						"description":{
							type: "text",
							required: true
						}
					}
				};
				resolve(opts);
			});
		}
	}
})();