(function(){
	'use strict';
	angular.module(appName).service("OGCommonService", OGCommonService);

	OGCommonService.$inject = ["$filter", '$q', "$rootScope", "$translate", "GroupsResource", "OgEngineResource"];

	function OGCommonService($filter, $q, $rootScope, $translate, GroupsResource, OgEngineResource){
		var self = this;

		self.loadEngineConfig = loadEngineConfig;
		self.loadUserConfig = loadUserConfig;
		self.changeLanguage = changeLanguage;
		self.ogInstructions = "";
		self.createGroups = createGroups;
		self.getPartitionTable = getPartitionTable;
		self.getDisksConfigFromPartitions = getDisksConfigFromPartitions;
		self.getSelectionSize  = getSelectionSize;
		self.getUnits = getUnits;


		return self;


		function loadEngineConfig() {
			// Cargar en el rootScope los arrays de objetos comunes al sistema
			return $q(function(resolve, reject){
				OgEngineResource.get().then(
					function(response){
					  $rootScope.constants = angular.extend($rootScope.constants, response);
					  // inicializar timers generales para refresco de información
					  $rootScope.timers = {
					    serverStatusInterval: {
					      tick: 5000,
					      object: null
					    },
					    clientsStatusInterval: {
					      tick: 5000,
					      object: null
					    },
					    executionsInterval: {
					      tick: 5000,
					      object: null
					    },

					  }
					  resolve();
					},
					function(error){
					  reject(error);
					}
				);
			});
		}

		function loadUserConfig() {
			$rootScope.user = JSON.parse(localStorage.getItem("user"));
			// si no existen las preferencias de usuario se crean
			if(!$rootScope.user.preferences){
				$rootScope.user.preferences = $rootScope.constants.user.preferences;
			}
			if($rootScope.user.preferences.language){
				$translate.use($rootScope.user.preferences.language);
			}
			$rootScope.app.theme = $rootScope.user.preferences.theme;
		}


		function changeLanguage(langKey){
			$translate.use(langKey);
		}

		function createGroups(array, property){
			var groups = [];
			var newArray = [];

			// Extraer los grupos de los perfiles hardware
			for(var index = 0; index < array.length; index++){
				var obj = array[index];
				var group = obj.group;
				if(typeof group !== "undefined"){
					group = addGroup(groups, group);
					// Si no se encontró el grupo, buscamos entre los de rootScope
					if(group == null){
						var g = $filter("filter")($rootScope.groups, {id: obj.group.parent.id});
						g = g[0];
						if(!g.groups)
							g.groups = [];
						g.groups.push(obj.group);
						groups.push(g);
						group = obj.group;
					}
					delete obj.group;

					if(!group[property]){
						group[property] = [];
					}
					group[property].push(obj);
				}
				else{
					newArray.push(obj);
				}
			}
			groups = {
				groups: groups,
			};
			groups[property] = newArray;
			return groups;
		}

		function addGroup(groups, group){
			var found = null;
			if(!group.parent){
				var tmp = $filter("filter")(groups, {id: group.id});
				if(tmp.length == 0){
					groups.push(group);
				}
				else{
					group = tmp[0];
				}
				found = group;
			}
			else{
				var index = 0;
				// buscar el grupo donde insertarlo
				while(found == null && index < groups.length){ 
					if(groups[index].id == group.parent.id){
						if(!groups[index].groups){
							groups[index].groups = [];
							groups[index].groups.push(group);
						}
						else{
							// Comprobar si ya contiene el grupo, sino, se añade
							var tmp = $filter("filter")(groups[index].groups, {id: group.id});
							if(tmp.length == 0){
								groups[index].groups.push(group);
							}
							else{
								group = tmp[0];
							}
						}
						found = group;
					}
					else if(groups[index].groups){
						found = addGroup(groups[index].groups, group);
					}
					index++;
				}
			}
			return found;
		}


		function getSelectionSize(){
			return Object.keys($rootScope.selectedClients).length;
		}

		/**
		 * Dada la particion 0 de la configuracion de un cliente, devuelve el objeto partitionTable asociado
		 */
		function getPartitionTable(partition){
			return $rootScope.constants.partitiontable[parseInt(partition.partitionCode) - 1];
		}

		function getDisksConfigFromPartitions(partitions){
			var disks = [];
			var partitionTable;
			// La partición 0 es la configuración del disco
			for(var p = 0; p < partitions.length; p++){
				var partition = partitions[p];
				if(!disks[partition.numDisk-1]){
					disks.push({});
				}

				// La partición 0 es la configuración del disco
				if(partition.numPartition == 0){
					partitionTable = getPartitionTable(partition);
					disks[partition.numDisk-1] = {
						size: partition.size,
						disk: partition.numDisk,
						parttable: partitionTable.type,
						partitions: []
					};
				}
				else{
					// Comprobar el tipo de partición dependiendo del código
					 var elements = $filter("filter")(partitionTable.partitions, {id: partition.partitionCode}, true);
					 partition.parttype = (elements.length > 0)?elements[0].type:"";
					 // Si es cache, actualizar su contenido
					 if(partition.partitionCode == 'ca'){
					 	// actualizar el contenido de la cache
					 	if(typeof partition.cacheContent === "string"){
					 		var cacheContent = [];
					 		cacheContent = partition.cacheContent.trim().split(",");
					 		var cacheContentObj = {
								files: []
							};
							for(var index = 0; index < cacheContent.length; index++){
								if(index == 0){
									cacheContentObj.freeSpace = cacheContent[index];
								}
								else{
									if(cacheContent[index] != ""){
										var parts = cacheContent[index].trim().split(" ");

										var fileSize = parts[0].trim() + "KB";
										var fileName = parts[1].trim();
										var file = {name: fileName, size: fileSize};
										file.type = (file.name.indexOf("/") !== -1)?"D":"F";
										cacheContentObj.files.push(file);
									}
								}
							}
							partition.cacheContent = cacheContentObj;
					 	}
					 	else if(!partition.cacheContent){
					 		partition.cacheContent = [];
					 	}
					 }
					 disks[partition.numDisk-1].partitions.push(partition);
				}

			}
			return disks;
		}

		function getUnits(bytes){
          var units = "B";
          var divider = 1;
          if(bytes > 1073741824){
            units = "GB";
            divider = 1024*1024*1024;
          }
          else if(bytes > 1048576){
            units = "MB";
            divider = 1024*1024; 
          }
          else if(bytes > 1024){
            units = "KB";
            divider = 1024;  
          }
          return Math.round((bytes/divider)*100)/100+" "+units;
        }

	}
})();