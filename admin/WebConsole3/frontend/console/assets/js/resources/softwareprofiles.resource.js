(function(){
	'use strict';
	angular.module(appName).service("SoftwareProfilesResource", SoftwareProfilesResource);

	SoftwareProfilesResource.$inject = ["$resource", '$rootScope', '$q', 'gbnBaseResource','API_URL'];

	function SoftwareProfilesResource($resource, $rootScope, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options
		}
		var resource = $resource(API_URL+"/softwareprofiles/:profileId.json", {profileId: "@profileId"}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
			

		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
						"description": {
							type: "text",
							required: true
						},
						"comments":{
							type: "textarea",
							required: false
						}
					}
				};
				resolve(opts);
			});
		}
	}
})();