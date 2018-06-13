(function(){
	'use strict';
	angular.module(appName).service("RepositoriesResource", RepositoriesResource);

	RepositoriesResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL', 'API_BASE_URL', 'BASE_DIR'];

	function RepositoriesResource($resource, $q, gbnBaseResource, API_URL, API_BASE_URL, BASE_DIR){
		var methods = {
			options: options
		};

		var resource = $resource(API_URL+"/repositories/:repoId.json", {repoId: "@repoId"}, {'update': {method:'PATCH'}});


		var repoResource = gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
		repoResource.getInfo = getInfo;
		return repoResource;

		function getInfo(repository){
			var REPO_API_URL = "https://"+repository.ip+"/"+BASE_DIR+API_BASE_URL;
			var headers = {Authorization: repository.apikey};
			var resource = $resource(REPO_API_URL+"/repository/images.json", {}, {'update': {method:'PATCH', headers: headers}, 'get': {method:'GET', headers: headers}, 'query': {method:'GET', headers: headers, isArray: true}});
			return resource.get().$promise;
		}


		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
						rows: [
							{
								"name": {
									type: "text",
									required: true,
									css: "col-md-5"
								},
								"ip": {
									type: "text",
									required: true,
									css: "col-md-4"
								},
								"port": {
									type: "number",
									required: true,
									css: "col-md-3"
								}
							},
							{
								"password": {
									type: "text",
									required: true,
									label: "api_token",
									css: "col-md-4"
								},
								"configurationpath": {
									type: "text",
									required: true,
									label: "configurationpath",
									css: "col-md-4"
								},
								"adminpath": {
									type: "text",
									required: true,
									label: "adminpath",
									css: "col-md-4"

								},
								"pxepath": {
									type: "text",
									required: true,
									label: "pxepath",
									css: "col-md-4"
								}
							},
							{
								"description": {
									type: "textarea",
									required: false,
									css: "col-md-12"
								}
							}
						]

					}
				};
				resolve(opts);
			});
		}
	}
})();