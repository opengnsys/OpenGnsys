(function(){
	'use strict';
	angular.module(appName)
	.service("UserResource", UserResource);

	UserResource.$inject = ["$resource", '$q', '$filter', 'gbnBaseResource', 'API_URL', 'API_PUBLIC_URL'];

	function UserResource($resource, $q, $filter, gbnBaseResource, API_URL, API_PUBLIC_URL){
		var self = this;
		self.resource = $resource(API_URL+"/users/:id.json", {id: '@id'}, {'update': { method:'PATCH' }});

		var userResource = gbnBaseResource.getBaseResource(self.resource, {name: appName+"_User", mock: false, methods: {options: options}});
		userResource.me = me;
		
		return userResource;

		function me()
		{
			return $q(function(resolve, reject){
				resolve({});
			})
			//return $resource(API_URL+"/user/me").get().$promise;
		}

		function options(){
			return $q(function(resolve,reject){
				var options = {
					fields: {
						"rows": [
							{
								username: {
									type: "text",
									css: "col-md-4"
								},
								plainPasswordFirst: {
									type: "password",
									css: "col-md-3"
								},
								plainPasswordSecond: {
									type: "password",
									css: "col-md-3"
								},
								role: {
									type: "select",
									options: {
										source: "ROLES",
									},
									css: "col-md-2"
								}
							}
						]
					},
					required: "all"
				
				};
				resolve(options);
			});
		}

		
	}
})();