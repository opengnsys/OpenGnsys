(function(){
	'use strict';
	angular.module(appName).service("TracesResource", TracesResource);

	TracesResource.$inject = ["$resource", '$rootScope', '$q', 'gbnBaseResource','API_URL'];

	function TracesResource($resource, $rootScope, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options
		}
		var resource = $resource(API_URL+"/traces/:traceId.json", {traceId: "@traceId"}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
		
		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
					}
				};
				resolve(opts);
			});
		}
	}
})();