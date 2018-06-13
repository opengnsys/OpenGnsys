(function(){
	'use strict';
	angular.module(appName).service("PlatformsResource", PlatformsResource);

	PlatformsResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function PlatformsResource($resource, $q, gbnBaseResource, API_URL){
		
		var resource = $resource(API_URL+"/platforms", {}, {'update': {method:'POST'}});


		return gbnBaseResource.getBaseResource(resource, { mock: false});
	}
})();