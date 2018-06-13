(function(){
	'use strict';
	angular.module(appName).service("ServerStatusResource", ServerStatusResource);

	ServerStatusResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function ServerStatusResource($resource, $q, gbnBaseResource, API_URL){
		var resource = $resource(API_URL+"/core/status.json", {}, {'update': {method:'PATCH'}});
		return gbnBaseResource.getBaseResource(resource, {mock: false});

	}
})();