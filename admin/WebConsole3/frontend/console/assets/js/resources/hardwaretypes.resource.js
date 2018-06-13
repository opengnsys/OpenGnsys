(function(){
	'use strict';
	angular.module(appName).service("HardwareTypesResource", HardwareTypesResource);

	HardwareTypesResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function HardwareTypesResource($resource, $q, gbnBaseResource, API_URL){
		
		var resource = $resource(API_URL+"/hardwaretypes.json", {}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, { mock: false});
	}
})();