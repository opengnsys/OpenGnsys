(function(){
	'use strict';
	angular.module(appName).service("OsTypesResource", OsTypesResource);

	OsTypesResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function OsTypesResource($resource, $q, gbnBaseResource, API_URL){
		
		var resource = $resource(API_URL+"/ostypes", {}, {'update': {method:'POST'}});


		return gbnBaseResource.getBaseResource(resource, { mock: false});
	}
})();