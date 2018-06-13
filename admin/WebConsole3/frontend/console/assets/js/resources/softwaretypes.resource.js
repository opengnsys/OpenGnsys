(function(){
	'use strict';
	angular.module(appName).service("SoftwareTypesResource", SoftwareTypesResource);

	SoftwareTypesResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function SoftwareTypesResource($resource, $q, gbnBaseResource, API_URL){
		
		var resource = $resource(API_URL+"/softwaretypes.json", {}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, { mock: false});
	}
})();