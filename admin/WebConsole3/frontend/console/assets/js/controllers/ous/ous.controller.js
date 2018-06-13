(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('OusController', OusController);

	OusController.$inject = ['$rootScope', '$scope', '$state', '$sce', '$q', '$timeout', '$transitions','$filter', '$interval', 'toaster', 'ogSweetAlert', 'OusResource', 'ClientsResource'];

	function OusController($rootScope, $scope, $state, $sce, $q, $timeout, $transitions, $filter, $interval, toaster, ogSweetAlert, OusResource, ClientsResource) {
		var vm = this;
		$scope.user = $rootScope.user;
		vm.ous = {
			children: []
		};
		vm.moveChildren = false;
		$rootScope.selectedClients = [];
		$rootScope.clientStatus = [];
		vm.selectedStatus = [];
		vm.showGrid = showGrid;
		vm.getClientStatus = getClientStatus;
		vm.selectClient = selectClient;
		vm.selectOu = selectOu;
		vm.selectForMove = selectForMove;
		vm.deleteOu = deleteOu;
		vm.deleteClient = deleteClient;
		vm.moveClientsToOu = moveClientsToOu;
		vm.isMovingClients = isMovingClients;
		vm.doMove = doMove;
		vm.deleteSelectedClients = deleteSelectedClients;


		$transitions.onStart({to: "app.**"}, function(trans){
            if(trans.to().name === "app.ous"){
            	if($rootScope.timers.clientsStatusInterval.object == null){
	              $rootScope.timers.clientsStatusInterval.object = $interval(function() {
	                getClientStatus();
	              }, $rootScope.timers.clientsStatusInterval.tick);
	            }
	            else{
	            	getClientStatus();
	            }
	        }
	        else{
              $interval.cancel($rootScope.timers.clientsStatusInterval.object);
              $rootScope.timers.clientsStatusInterval.object = null;
            }
        });

  		init();

	  	function init(){
	  		for(var index = 0; index < $rootScope.constants.clientstatus.length; index++){
		  		vm.selectedStatus[$rootScope.constants.clientstatus[index].id] = true;
		  	}
	  		if($state.$current.name === "app.ous"){
	  			var request = null;
	  			if($rootScope.user.ou && $rootScope.user.ou.id){
					request = OusResource.get({ouid: $rootScope.user.ou.id, children: 1});
	  			}
	  			else{
	  				request = OusResource.query();
	  			}

	  			request.then(
	  				function(response){
	  					vm.ous = Array.isArray(response)?response:[response];
	  					// La primera vez que entra
				        if($rootScope.timers.clientsStatusInterval.object == null){
				          getClientStatus();
				          $rootScope.timers.clientsStatusInterval.object = $interval(function() {
				              getClientStatus();
				          }, $rootScope.timers.clientsStatusInterval.tick);
				        }
				        else{
				        	getClientStatus();
				        }

	  				},
	  				function(error){

	  				}
	  			);
	  		}

	  	}

	  	function showGrid(show){
	  		$rootScope.user.preferences.ous.showGrid = show;
	  		localStorage.setItem("user", JSON.stringify($rootScope.user));
	  	}

	  	function getClientStatus(){
	  		var promises = [];
	  		for(var index = 0; index < vm.ous.length; index++){
				promises.push(ClientsResource.statusAll().query({ouId: vm.ous[index].id}));
			}

			$q.all(promises).then(
				function(response){
					for(var p = 0; p < response.length; p++){
						for(var elem = 0; elem < response[p].length; elem++){
							$rootScope.clientStatus[response[p][elem].id] = response[p][elem].status;
						}
					}
				},
				function(error){
					
				}
			)
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

	  	function selectClient(client, parent){
	  		client.parent = parent;
	  		if(client.selected){
	  			$rootScope.selectedClients[client.id] = client;
	  		}
	  		else{
	  			delete $rootScope.selectedClients[client.id];
	  		}
	  	}

	  	function selectOu(ou){
			// seleccionar/deseleccionar todos los elementos dentro de ou
			for(var i = 0; i < ou.children.length; i++){
				ou.children[i].selected = ou.selected;
				selectOu(ou.children[i]);
			}
			for(var i = 0; i < ou.clients.length; i++){
				ou.clients[i].selected = ou.selected;
				selectClient(ou.clients[i], ou);
			}
	  	}

	  	function selectForMove(ou, select){
	  		// si existe una operacion de movimiento de clientes se cancela
	  		vm.movingClients = false;
	  		if(typeof select == "undefined"){
		  		vm.movingOu = (vm.movingOu == ou)?null:ou;
		  		select = select || (vm.movingOu == ou);
		  	}
	  		// seleccionar/deseleccionar todos los elementos dentro de ou
			for(var i = 0; i < ou.children.length; i++){
				ou.children[i].selectedForMove = select;
				selectForMove(ou.children[i], select);
			}
	  	}

	  	function deleteOu(ou){
	  		var htmlText = "";
	  		//if(ou.parent != null){
	  			htmlText = '<input ng-model="moveChildren" icheck checkbox-class="icheckbox_square-blue" radio-class="iradio_square-blue" type="checkbox" class="ng-scope selection-checkbox"><span translate="move_children_to_parent"></span>';
	  		//}
	  		var options = {
	  			scope:{
		  			moveChildren: false
		  		}
	  		}
	  		ogSweetAlert.swal(
	  			{
				   title: $filter("translate")("sure_to_delete")+"?",
				   html: htmlText,
				   type: "warning",
				   showCancelButton: true,
				   confirmButtonColor: "#DD6B55",
				   confirmButtonText: $filter("translate")("yes_delete")
				}, 
				function(response){
					if(response == true){
						var promises = [];
						$q(function(resolve, reject){
							if(options.scope.moveChildren){
								// Obtener la ou para saber el id de su padre
								OusResource.get({ouid: ou.id, parent: 1}).then(
									function(response){
										// Mover todos los hijos al nivel superior de la ou actual
										var parentId = response.parent.id;
										for(var i = 0; i < ou.children.length; i++){
											ou.children[i].parent = parentId;
											promises.push(OusResource.update({ouid: ou.children[i].id}, {parent: parentId}));
										}
										ou.clients = ou.clients||[];
										for(var i = 0; i < ou.clients.length; i++){
											ou.clients[i].organizationalUnit = parentId;
											promises.push(ClientsResource.update({clientId: ou.clients[i].id}, {organizationalUnit: parentId}));
										}
										$q.all(promises).then(
											function(response){
												resolve(true);
											},
											function(error){
												reject(error);
											}
										)
									},
									function(error){
										reject(error);
									}
								)
							}
							else{
								resolve(true);
							}
						}).then(
							function(response){
								OusResource.delete({ouid: ou.id}).then(
									function(response){
										// Si la unidad organizativa es un nivel superior, se recarga la lista y se borra la unidad organizativa del usuario
										if(ou.parent == null){
											delete $rootScope.ous;
											$rootScope.user.ou = null;
										}

										$state.go("app.ous",{},{reload: true});
										$timeout(function(){
											toaster.pop({type: "success", title: "success",body: "Successfully deleted"});
										}, 0);
									},
									function(error){
										toaster.pop({type: "error", title: "error",body: error});
									}
								)
							},
							function(error){
								toaster.pop({type: "error", title: "error",body: error});
							}
						)
					}
				},
				function(cancel){
				},
				options
			);

	  	}

	  	function deleteClient(ou, client){
	  		ogSweetAlert.swal(
	  			{
	  			   title: $filter("translate")("sure_to_delete")+"?",
				   text: $filter("translate")("action_cannot_be_undone"),
				   type: "warning",
				   showCancelButton: true,
				   confirmButtonColor: "#DD6B55",
				   confirmButtonText: $filter("translate")("yes_delete")

		  		},
		  		function(response){
		  			if(response == true){
		  				ClientsResource.delete({clientId: client.id}).then(
		  					function(response){
		  						// Lo borramos de la unidad organizativa
		  						var index = ou.clients.indexOf(client);
		  						if(index != -1){
			  						ou.clients.splice(index, 1);
			  					}
								toaster.pop({type: "success", title: "success",body: "Successfully deleted"});
							},
							function(error){
								toaster.pop({type: "error", title: "error",body: error});
							}
		  				)
		  			}

		  		},
		  		function(cancel){

		  		}
	  		);
	  	}

	  	function deleteSelectedClients(){

	  		ogSweetAlert.swal(
	  			{
	  			   title: $filter("translate")("sure_to_delete")+"?",
				   text: $filter("translate")("action_cannot_be_undone"),
				   type: "warning",
				   showCancelButton: true,
				   confirmButtonColor: "#DD6B55",
				   confirmButtonText: $filter("translate")("yes_delete")

		  		},
		  		function(response){
		  			if(response == true){
		  				var clientIds = Object.keys($rootScope.selectedClients);
			  			var cId = "";
			  			var promises = [];
			  			for(var i = 0; i < clientIds.length; i++){
			  				cId = clientIds[i];
			  				promises.push(ClientsResource.delete({clientId: cId}));
				  		}
				  		$q.all(promises).then(
		  					function(response){
								for(var i = 0; i < clientIds.length; i++){
									cId = clientIds[i];
						  			deleteClientFromOu(vm.ous, $rootScope.selectedClients[cId]);
						  		}
						  		toaster.pop({type: "success", title: "success",body: "Successfully deleted"});
						  		$rootScope.selectedClients = [];
							},
							function(error){
								toaster.pop({type: "error", title: "error",body: error});
							}
		  				);
		  			}

		  		},
		  		function(cancel){

		  		}
		  	);
	  	}

	  	function moveClientsToOu(){
	  		if(vm.movingClients == true){
	  			vm.movingClients = false;
	  		}
	  		else{
	  			// Si existe una operacion de movimiento de Ou se cancela
	  			if(vm.movingOu != null){
	  				vm.selectForMove(vm.movingOu);
	  			}
		  		if(Object.keys($rootScope.selectedClients).length > 0){
			  		vm.movingClients = true;
			  	}
			  	else{
			  		ogSweetAlert.info("opengnsys_info",$filter("translate")("you_must_to_select_any_clients"));
			  	}
			  }
	  	}

	  	function isMovingClients(){
	  		return (vm.movingClients == true);
	  	}

	  	function deleteOuFromModel(ous, ou){
	  		var found = false;
	  		var nOus = ous.length;
	  		var index = 0;
	  		while(!found && index < nOus){
	  			if(ous[index] == ou){
	  				found = true;
	  				ous.splice(index,1);
	  			}
	  			else if(ous[index].children.length > 0){
	  				found = deleteOuFromModel(ous[index].children, ou);
	  			}
	  			index++;
	  		}
	  		return found;
	  	}

	  	function deleteClientFromOu(ous, client){
	  		var found = false;
	  		var nOus = ous.length;
	  		var index = 0;
	  		while(!found && index < nOus){
	  			if(ous[index].id == client.parent.id){
	  				found = true;
	  				var cIndex = ous[index].clients.indexOf(client);
	  				if(cIndex != -1){
	  					ous[index].clients.splice(cIndex,1);
	  				}
	  			}
	  			else if(ous[index].children.length > 0){
	  				found = deleteClientFromOu(ous[index].children, client);
	  			}
	  			index++;
	  		}
	  		return found;
	  	}

	  	function doMove(ou){
	  		// Comprobar si hay que mover clientes o una ou a la ou pasada por parametro
	  		if(vm.movingOu != null){
	  			/**/
	  			var id = ou?ou.id:null;
	  			OusResource.update({ouid: vm.movingOu.id}, {parent: id}).then(
	  				function(response){
						toaster.pop({type: "success", title: "success",body: "Successfully moved"});
						deleteOuFromModel(vm.ous, vm.movingOu);
						if(ou){
				  			ou.children.push(vm.movingOu);
				  		}
				  		else{
				  			vm.ous.push(vm.movingOu);
				  		}
						vm.movingOu = null;
					},
					function(error){
						toaster.pop({type: "error", title: "error",body: error});
					}
	  			);
	  			/**/

	  		}
	  		else if(vm.movingClients == true){
	  			var clientIds = Object.keys($rootScope.selectedClients);
	  			var cId = "";
	  			var promises = [];
	  			for(var i = 0; i < clientIds.length; i++){
	  				cId = clientIds[i];
	  				promises.push(ClientsResource.update({clientId: cId}, {organizationalUnit: ou.id}));
		  		}
		  		$q.all(promises).then(
  					function(response){
						for(var i = 0; i < clientIds.length; i++){
							cId = clientIds[i];
				  			deleteClientFromOu(vm.ous, $rootScope.selectedClients[cId]);
				  			ou.clients.push($rootScope.selectedClients[cId]);
				  			selectClient($rootScope.selectedClients[cId], ou);
				  		}
				  		toaster.pop({type: "success", title: "success",body: "Successfully moved"});
				  		vm.movingClients = false;
					},
					function(error){
						toaster.pop({type: "error", title: "error",body: error});
						vm.movingClients = false;
					}
  				);
	  		}
	  	}

	  	function editGroupName(group){
	  		group.name=group.tmpName;
	  		delete group.tmpName; 
	  		group.editing = true;
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