(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('ClientsNetbootController', ClientsNetbootController);

	ClientsNetbootController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', '$sce', 'toaster', 'ogSweetAlert', 'NetbootsResource'];

	function ClientsNetbootController($rootScope, $scope, $state, $timeout, $filter, $sce, toaster, ogSweetAlert, NetbootsResource) {
		var vm = this;
		vm.multiSelection = false;
		vm.netboots = [];
		vm.assignedNetboots = {};
		vm.selectionForMove = [];
		vm.rangeSelection = {
			start: -1,
			end: -1
		};

		vm.moveSelectionToNetboot = moveSelectionToNetboot;
		vm.checkSelection = checkSelection;
		vm.save = save;
		
		init();

		function init(){
			if($rootScope.user && $rootScope.selectedClients){
				NetbootsResource.query().then(
					function(result){
						vm.netboots = result;
						var clientIds = Object.keys($rootScope.selectedClients);
						// Recorrer todos los clientes y formar los grupos según el partitionCode de sus particiones, deben coincidir todos
						for(var index = 0; index < clientIds.length; index++){
							// Generamos una clave usando disco-particion-code para comparar
							var client = $rootScope.selectedClients[clientIds[index]];
							if(!client.netboot){
								client.netboot = vm.netboots[0];
							}
							if(!vm.assignedNetboots[client.netboot.id]){
								vm.assignedNetboots[client.netboot.id] = [];
							}
							vm.assignedNetboots[client.netboot.id].push(client.id);
						}
					},
					function(error){
						toaster.pop({type: "error", title: "error",body: error});
					}
				);
			}
			else{
				// TODO - dar error?
				ogSweetAlert.error($filter("translate")("opengnsys_error"), $filter("translate")("not_clients_selected"));
				$state.go("app.ous");
			}
		}

		function moveSelectionToNetboot(id){
			// La selección está formada por dos parametros separados por "_", netbootId y clientId
			for(var i = 0; i < vm.selectionForMove.length; i++){
				var ids = vm.selectionForMove[i].split("_");
				var netbootId = parseInt(ids[0]);
				var clientId = parseInt(ids[1]);
				// Eliminar el id del cliente del netboot origen
				var index = vm.assignedNetboots[netbootId].indexOf(clientId);
				vm.assignedNetboots[netbootId].splice(index,1);
				// Se introduce en el netboot seleccionado
				if(!vm.assignedNetboots[id]){
					vm.assignedNetboots[id] = [];
				}
				vm.assignedNetboots[id].push(clientId);
			}
			// Reiniciar los rangos seleccionados por si los hubiese
			vm.rangeSelection.start = -1;
			vm.rangeSelection.end = -1;

		}

		function checkSelection(netbootId, clientId){
			if(vm.multiSelection == true){
				if(vm.rangeSelection.start == -1){
					vm.rangeSelection.start = clientId;
				}
				else if(vm.rangeSelection.end == -1){
					vm.rangeSelection.end = clientId;
					// Realizar la seleccion
					var start = vm.assignedNetboots[netbootId].indexOf(vm.rangeSelection.start);
					var end = vm.assignedNetboots[netbootId].indexOf(vm.rangeSelection.end);
					if(end < start){
						var tmp = start;
						start = end;
						end = tmp;
					}
					vm.selectionForMove = [];
					for(var index = start; index <= end; index++){
						vm.selectionForMove.push(netbootId +  "_" + vm.assignedNetboots[netbootId][index]);
					}
				}
				else{
					vm.rangeSelection.start = clientId;
					vm.rangeSelection.end = -1;
				}
			}
			else {
				vm.rangeSelection.start = -1;
				vm.rangeSelection.end = -1;
			}

		}

		function save(Form){
  			NetbootsResource.updateFiles(vm.assignedNetboots).then(
  				function(response){
		  			$state.go("app.ous",{},{reload: true});
		  			$timeout(function(){
		  				toaster.pop({type: "success", title: $filter("translate")("success"),body: $filter("translate")("successfully_updated")});
		  			},0);
		  		},
		  		function(error){
		  			toaster.pop({type: "error", title: "error",body: error});
		  		}
			);

  		}

	  };
})();