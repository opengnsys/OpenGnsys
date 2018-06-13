(function(){
	'use strict';
	angular.module(appName).service("OgEngineResource", OgEngineResource);

	OgEngineResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function OgEngineResource($resource, $q, gbnBaseResource, API_URL){
		var resource = $resource(API_URL+"/core/engine.json", {}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {mock: false});
			
	}
})();