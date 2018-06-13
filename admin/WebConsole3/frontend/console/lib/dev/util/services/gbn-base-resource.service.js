(function(){
	'use strict';
	angular.module("globunet.utils")
	.service('gbnBaseResource',gbnBaseResource);

	gbnBaseResource.$inject = ['$filter','$q'];

	function gbnBaseResource($filter,$q){
		var self = this;
		self.resource;
		self.options = {};

		self.getBaseResource = getBaseResource;

		function getBaseResource(_resource, options){
			self.methods = {};
			self.options = {};
			self.resource = _resource;
			self.options = angular.extend(self.options, options);
			self.methods = options.methods;
			return getResourceObject();
		}

		function getResourceObject(){
			var resourceObj = self.methods||{};
			resourceObj = angular.extend(resourceObj,{
				mockData: [],
				nextId: 1,
				_options: angular.merge({},self.options),
				getFunction: getFunction,
				resource: self.resource,
				query: function(args){
					var self = this;
					var result = null;
					var func = null;
					if(self._options.mock){
						func = queryMock;
						result = func.apply(this, arguments);
					}
					else{
						func = self.getFunction("query");
						result = func.apply(this, arguments).$promise;
					}
					
					return result;
				},
				get: function(args){
					var self = this;
					var result = null;
					var func = null;
					if(self._options.mock){
						func = getMock;
						result = func.apply(this, arguments);
					}
					else{
						func = self.getFunction("get");
						result = func.apply(this, arguments).$promise;
					}
					
					return result;	
				},
				save: function(args){
					var self = this;
					var result = null;
					var func = null;
					if(self._options.mock){
						func = saveMock;
						result = func.apply(this, arguments);
					}
					else{
						func = self.getFunction("save");
						result = func.apply(this, arguments).$promise;
					}
					
					return result;
				},
				update: function(args){
					var self = this;
					var result = null;
					var func = null;
					if(self._options.mock){
						func = updateMock;
						result = func.apply(this, arguments);
					}
					else{
						func = self.getFunction("update");
						result = func.apply(this, arguments).$promise;
					}
					
					return result;
				},
				delete: function(args){
					var self = this;
					var result = null;
					var func = null;
					if(self._options.mock){
						func = _deleteMock;
						result = func.apply(this, arguments);
					}
					else{
						func = self.getFunction("delete");
						result = func.apply(this, arguments).$promise;
					}
					
					return result;
				},
				options: function(args){
					var self = this;
					var func = self.getFunction("options");
					return func.apply(this,arguments).$promise||func.apply(this,arguments);
				},
				loadMockData: loadMockData,
				saveMockData: saveMockData,
				init: function(){
					var self = this;
					if(self._options.mock){
						self.loadMockData();
					}
				}
			});
			resourceObj.init();

			return resourceObj;
		}

		function getFunction(method){
			var self = this;
			var result = self.resource[method];
			// Comprobar si tenemos definido el metodo
			if(typeof self._options.methods !== "undefined" && typeof self._options.methods[method] === "function"){
				result = self._options.methods[method];
			}
			return result;
		}


		// Funciones para trabajar con datos "mock"
		function loadMockData(){
			var self = this;
			var name = self._options.name;
			if(localStorage.getItem(name)){
				self.mockData = JSON.parse(localStorage.getItem(name));
				self.mockData = $filter('orderBy')(self.mockData, 'id');
				if(self.mockData.length == 0){
					self.nextId = 1;
				}
				else{
					self.nextId = parseInt(self.mockData[self.mockData.length - 1].id)+1;
				}
			}
			else{
				self.mockData = [];
				self.nextId = 1;
				self.saveMockData();
			}
		}

		function saveMockData(){
			var self = this;
			var name = self._options.name;
			localStorage.setItem(name, JSON.stringify(self.mockData));
		}

		function queryMock(params){
			var self = this;
			return $q(function(resolve, reject){
				resolve(self.mockData);
			});
		}

		function getMock(params){
			var self = this;
			return $q(function(resolve, reject){
				if(params.id){
					var index = self.mockData.indexOfByKey(params, "id");
					if(index != -1){
						resolve(self.mockData[index]);
					}
					else{
						reject({error: { status: 404, message: "object not found"}});
					}
				}
				else{
					reject({error: { status: 404, message: "param id not found"}});
				}
			});
		}

		function saveMock(object){
			var self = this;
			return $q(function(resolve,reject){
				object.id = self.nextId++;
				self.mockData.push(object);
				self.saveMockData();
				resolve(object);
			});
		}

		function updateMock(objId, object){
			var self = this;
			return $q(function(resolve,reject){
				// objId contiene la clave por la que buscar, viene en la forma {key: value}
				// Ej {id: X}
				var key = Object.keys(objId)[0];
				var index = self.mockData.indexOfByKey(objId, key);
				if(index != -1){
					angular.extend(self.mockData[index], object);
					self.saveMockData();
					resolve(self.mockData[index]);
				}
				else{
					reject({error: { status: 404, message: "object not found"}});
				}
			});
		}

		function _deleteMock(objId){
			var self = this;
			return $q(function(resolve,reject){
				// objId contiene la clave por la que buscar, viene en la forma {key: value}
				// Ej {id: X}
				var key = Object.keys(objId)[0];
				var index = self.mockData.indexOfByKey(objId, key);
				if(index != -1){
					self.mockData.splice(index,1);
					self.saveMockData();
					resolve({status: 200});
				}
				else{
					reject({error: { status: 404, message: "object not found"}});
				}
			});
		}
		


		return self;
	};
})();