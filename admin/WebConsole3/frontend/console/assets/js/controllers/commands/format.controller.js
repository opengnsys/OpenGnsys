(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('FormatController', FormatController);

	FormatController.$inject = ['$rootScope', '$scope','$state', '$filter', '$timeout', '$q', 'toaster', 'ogSweetAlert', 'CommandsResource', 'OGCommonService'];

	function FormatController($rootScope, $scope, $state, $filter, $timeout, $q, toaster, ogSweetAlert, CommandsResource, OGCommonService) {
		var vm = this;
		vm.clientGroups = {};
		vm.execution = {};
		vm.command = {};
		vm.sendCommand = sendCommand;
		vm.getPartitionTypes = getPartitionTypes;

		init();


		function init(){
			if($rootScope.user && $rootScope.selectedClients){
				var clientIds = Object.keys($rootScope.selectedClients);
				// Recorrer todos los clientes y formar los grupos según el partitionCode de sus particiones, deben coincidir todos
				for(var index = 0; index < clientIds.length; index++){
					// Generamos una clave usando disco-particion-code para comparar
					var client = $rootScope.selectedClients[clientIds[index]];
					var key = getPartitionsCode(client.partitions);

					if(!vm.clientGroups[key]){
						vm.clientGroups[key] = [];
					}
					vm.clientGroups[key].push(client);
				}
			}
			else{
				// TODO - dar error?
				ogSweetAlert.error($filter("translate")("opengnsys_error"), $filter("translate")("not_clients_selected"));
				$state.go("app.ous");
			}
		}

		function getPartitionsCode(partitions){
			var key = "";
			for(var p = 0; p < partitions.length; p++){
				// Además de calcular la clave, alteramos el partitionCode pasandolo a mayusculas y aplicando padding de "0" a la izquierda si es necesario
				partitions[p].partitionCode = partitions[p].partitionCode.toUpperCase();
				if(partitions[p].partitionCode.length == 1){
					partitions[p].partitionCode = "0"+partitions[p].partitionCode;
				}
				key += partitions[p].numDisk+partitions[p].numPartition+partitions[p].partitionCode;
			}
			return key;
		}


		function sendCommand(){
  			// Comrobar qué particiones se han seleccionado de qué grupos
  			var executions = {};
  			var groups = Object.keys(vm.clientGroups);
  			for(var g = 0; g < groups.length; g++){
  				// Recorrer las particiones del primer cliente de la lista y ver si hay alguna seleccionada
  				var found = false;
  				// La partición 0 no se usa, solo indica las propiedades del disco
  				var index = 1;
  				var client = vm.clientGroups[groups[g]][0];
  				while(!found && index < client.partitions.length){
  					var partition = client.partitions[index];
  					if(partition.selected == true){
  						if(!executions[g]){
  							executions[g] = {
  								clients: "",
  								script: "ogUnmountAll "+partition.numDisk+"\n"
  							};
  						}
  						executions[g].clients += client.id + ",";
  						// Si la particion es cache
  						if(partition.partitionCode.toUpperCase() == "CA"){
  							executions[g].script += "ogFormatCache";
  						}
  						else{
	  						executions[g].script += "ogFormat "+ partition.numDisk + " " + partition.numPartition + " " + partition.filesystem;
	  					}
  					}
  					index++;
  				}
  			}

  			// Creamos tantas promises como diferentes grupos de ejecución haya
  			var promises = [];
  			var len = Object.keys(executions).length;
  			for(var index = 0; index < len; index++){
  				var execution = {
  					type: "RUN_SCRIPT",
  					script: executions[index].script,
  					clients: executions[index].clients.substring(0, executions[index].clients.length-1)	// Quitar la ultima ","
  				};
  				promises.push(CommandsResource.execute(execution));
  			}
  			$q.all(promises).then(
	  			function(response){
		  			toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_executed")});
		  			$state.go("app.ous",{},{reload: true});
		  		},
		  		function(error){
		  			toaster.pop({type: "error", title: "error",body: error});
		  		}
	  		);
  		}

  		function getPartitionTypes(partitions){
  			var types = [];
			var infoPart = $filter("filter")(partitions, {numPartition: 0});
			if(infoPart.length == 1){
				var partitionTable = OGCommonService.getPartitionTable(infoPart[0]);
				types = partitionTable.partitions;
			}
			return types;
		}

	  };
})();