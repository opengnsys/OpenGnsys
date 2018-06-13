(function(){
	'use strict';
	angular.module(appName).service("GroupsResource", GroupsResource);

	GroupsResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function GroupsResource($resource, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options
		}
		var resource = $resource(API_URL+"/ous/:ouid/groups", {ouid: "@ouid"}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
			

		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
						"name": {
							type: "text",
							required: true
						},
						"comments":{
							type: "textarea",
						}
					}
				};
				resolve(opts);
			});
		}
	}
})();