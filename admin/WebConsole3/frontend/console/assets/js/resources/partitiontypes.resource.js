(function(){
	'use strict';
	angular.module(appName).service("PartitionTypesResource", PartitionTypesResource);

	PartitionTypesResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function PartitionTypesResource($resource, $q, gbnBaseResource, API_URL){
		
		var resource = $resource(API_URL+"/partitiontypes", {}, {'update': {method:'POST'}});


		return gbnBaseResource.getBaseResource(resource, {mock: false});
	}
})();