(function(){
	'use strict';
	angular.module(appName).service("MenusResource", MenusResource);

	MenusResource.$inject = ["$resource", "$rootScope", '$q', 'gbnBaseResource','API_URL'];

	function MenusResource($resource, $rootScope, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options,
			save: save
		}
		var resource = $resource(API_URL+"/menus/:menuId.json", {menuId: "@menuId"}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});

		function save(object){
			var res = $resource(API_URL+"/menuses.json", {menuId: "@menuId"}, {'update': {method:'PATCH'}});
			return res.save(object);
		}
			

		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
						rows: [
							{
								"title": {
									type: "text",
									css: "col-md-6",
									required: true
								},
								"idurlimg":{
									type:"select",
									required: true,
									css: "col-md-2",
									label: "background_image",
									options: ["1"]
								},								
								"resolution": {
									type: "select",
									required: true,
									css: "col-md-4",
									options: {
										source: "constants.menus.resolutions",
										label: "text",
										output: "id",
										trackby: " track by item.id"
									}
								},

								"description": {
									type: "textarea",
									css: "col-md-12",
									required: true
								},
								"comments": {
									type: "textarea",
									css: "col-md-12"
								}
							},
							{
								"publicColumns":{
									type: "number",
									css: "col-md-2"
								},
								"publicUrl": {
									type: "text",
									css: "col-md-10"
								}
							},
							{
								"privateColumns":{
									type: "number",
									css: "col-md-2"
								},
								"privateUrl": {
									type: "text",
									css: "col-md-10"
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