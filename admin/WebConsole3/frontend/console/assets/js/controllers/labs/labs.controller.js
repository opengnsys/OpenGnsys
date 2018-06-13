(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('LabsController', LabsController);

	LabsController.$inject = ['$rootScope', '$scope','$filter', '$state', 'GroupsResource', 'LabsResource', 'ClientsResource'];

	function LabsController($rootScope, $scope, $filter, $state, GroupsResource, LabsResource, ClientsResource) {
		var vm = this;
		$scope.user = $rootScope.user;
		vm.labs = {
			groups: []
		};
		vm.status = [];
		vm.selectedStatus = [];
		vm.showGrid = true;
		vm.labClientStatus = labClientStatus;
		vm.selectLab = selectLab;		
		vm.deleteLab = deleteLab;
		vm.editGroupName = editGroupName;
		vm.deleteGroup = deleteGroup;

  		init();

	  	function init(){
	  		for(var index in $rootScope.constants.clientStatus){
		  		vm.selectedStatus[$rootScope.constants.clientStatus[index]] = true;
		  	}
	  		if($state.$current.name === "app.labs"){
		  		GroupsResource.query({ouid: $rootScope.user.ou.id}).then(
		  			function(result){
		  				// Guardar en rootScope todos los grupos
		  				$rootScope.groups = result;
		  				// Usar solo los grupos de aulas en este caso.
		  				vm.labs.groups = transformToTree($filter("filter")(result, {type: $rootScope.constants.groups.LABS_TYPE}));

		  				// Obtener todas las aulas del centro
		  				LabsResource.query({ouid: $rootScope.user.ou.id}).then(
		  					function(result){
		  						// Añadir los laboratorios a su grupo correspondiente si existiese
		  						_.each(result, function(lab){
		  							if(lab.group.id != 0){
			  							var group = _(vm.labs.groups).thru(function(coll) { return _.union(coll, _.map(coll, 'groups'));}).flatten().find({ id: lab.group.id });
			  							(group.labs || (group.labs = [])).push(lab);
			  						}
			  						else{
			  							(vm.labs.labs || (vm.labs.labs =[])).push(lab);
			  						}

		  							LabsResource.clients({ouid: $rootScope.user.ou.id, labid: lab.id}).then(
		  								function(clients){
		  									_.each(clients,function(client){
		  										vm.status[client.ip] = "unknown";

		  										if(client.group && client.group.id != 0){
		  											
		  											var group = getGroup(lab.classroomGroups, client.group.id);
		  											(group.clients || (group.clients = [])).push(client);
		  											/*
		  											// Buscar el grupo e insertarlo
		  											var group = _(lab.classroomGroups).thru(function(coll) { 
		  												var tmp =  _.union(coll, _.map(coll, 'classroomGroups'));
		  												return tmp;
		  											}).flatten().find({ id: client.group.id });
		  											(group.clients || (group.clients = [])).push(client);
		  											/**/
		  										}
		  										else{
		  											(lab.clients || (lab.clients = [])).push(client);
		  										}
		  									});
		  								},
		  								function(error){

		  								}
		  							);

		  						});
		  					},
		  					function(error){
		  						alert(error);
		  					}
		  				);
		  			},
		  			function(error){
		  				alert(error);
		  			}
		  		);
	  		}
	  	}

	  	function labClientStatus(labId){
			//obtener el estado de todos los clientes para el laboratorio
			ClientsResource.statusAll().query({ouid: $rootScope.user.ou.id, labId: labId}).then(
				function(result){
					result.forEach(function(element) {
					    vm.status[element.ip] = element.status;
					});
				},
				function(error){

				}
			);
	  	}

	  	function getGroup(classroomGroups, groupId){
	  		var found = false;
	  		var result = null;
	  		var index = 0;
	  		while(!found && index < classroomGroups.length){
	  			if(classroomGroups[index].id == groupId){
	  				found = true;
	  				result = classroomGroups[index];
	  			}
	  			else if(classroomGroups[index].classroomGroups.length > 0){
	  				result = getGroup(classroomGroups[index].classroomGroups, groupId);
	  				if(result != null){
	  					found = true;
	  				}
	  			}
	  			index++;
	  		}
	  		return result;
	  	}


	  	function selectClients(clients, selected){
	  		if(typeof clients != "undefined"){
		  		for(var index = 0; index < clients.length; index++){
		  			clients[index].selected = selected;
		  		}
		  	}
	  	}

	  	function selectGroup(group, selected){
	  		group.selected = selected;
	  		selectClients(group.clients, selected);
	  		for(var index = 0; index < group.classroomGroups.length; index++){
	  			selectGroup(group.classroomGroups[index], selected);
	  		}
	  	}

	  	function selectLab(lab){
	  		 // seleccionar/deseleccionar todos los elementos dentro de lab
	  		 selectGroup(lab, lab.selected);
	  	}

	  	function deleteLab(labId){
	  		

	  	}

	  	function editGroupName(group){
	  		group.name=group.tmpName;
	  		delete group.tmpName; 
	  		group.editing = true;
	  	}

	  	function deleteGroup(groupId){

	  	}

	  	function transformToTree(arr){
		    var nodes = {};    
		    return arr.filter(function(obj){
		        var id = obj.id,
		        parentId = obj.parent?obj.parent.id:undefined;

		        nodes[id] = _.defaults(obj, nodes[id], { groups: [] });
		        parentId && (nodes[parentId] = (nodes[parentId] || { groups: [] }))["groups"].push(obj);

		        return !parentId;
		    });    
		}
	  };
})();