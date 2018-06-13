(function(){
	'use strict';
	angular.module(appName).service("OGCommandsService", OGCommandsService);

	OGCommandsService.$inject = ['$rootScope', '$state', '$filter', '$timeout', 'toaster', 'ogSweetAlert', 'CommandsResource'];

	function OGCommandsService($rootScope, $state, $filter, $timeout, toaster, ogSweetAlert,  CommandsResource){
		var vm = this;

		vm.ogInstructions = "";
		vm.execution = {};
		vm.sendCommand = sendCommand;
		vm.execute = execute;
		
		

		return vm;

		function sendCommand(){
			var result = true;
			if(vm.Form){
	  			result = $rootScope.ValidateFormsService.validateForms(vm);
	  		}

  			if(!vm.execution.script){
  				result = false;
  				toaster.pop({type: "error", title: "error",body: $filter("translate")("command_not_valid")});
  			}
  			else if(!vm.execution.clients){
  				result = false;
  				toaster.pop({type: "error", title: "error",body: $filter("translate")("not_clients_selected")});
  			}
            // Si no hubo ningun error
           	if(result == true){
           		vm.execution.script = vm.execution.script.replace(/\"/g, "\\\"").replace(/\$/g, "\\\$");
           		// Resetar las instrucciones del script opengnsys almacenadas.
           		vm.ogInstructions = "";
	  			CommandsResource.execute(vm.execution).then(
		  			function(response){
		  				// Buscar en la respuesta si hay algún statuscode diferente de 200
		  				var errors = $filter("filter")(response, {statusCode: "!200"});
		  				var errorStr = "";
		  				var toasterOpts = {type: "success", title: "success",body: $filter("translate")("successfully_executed")};
		  				if(errors.length > 0){
		  					for(var e = 0; e < errors.length; e++){
			  					errorStr += $filter("translate")("execution_failed_in") + " " + errors[e].name+"\n";
			  				}

			  				toasterOpts = {type: "error", title: "error",body: errorStr};
		  				}
		  				$timeout(function(){toaster.pop(toasterOpts)}, 500);
			  			$state.go("app.ous",{},{reload: true});
			  		},
			  		function(error){
			  			toaster.pop({type: "error", title: "error",body: error});
			  		}
		  		);
	  		}
  		}

  		function execute(command, params){

			vm.execution.type = command;
			var commands = $rootScope.constants.commands;

  			if(command == 'HISTORY_LOG'){
  				var clientIp = null;
  				// Abrir ventana de log
  				if(typeof params === "undefined" || typeof params.clientIp === "undefined"){
					var client = $rootScope.selectedClients[Object.keys($rootScope.selectedClients)[0]];
					if(client){
						clientIp = client.ip;
					}
  				}
  				else{
  					clientIp = params.clientIp;
  				}
  				
  				if(clientIp){
	  				var url = "http://"+clientIp+commands.HISTORY_LOG;
	  				window.open(url,"","resizable=yes,toolbar=no,status=no,location=no,menubar=no,scrollbars=yes");
	  			}
  			}
  			else if(command == 'REALTIME_LOG'){
  				var clientIp = null;
  				// Abrir ventana de log
  				if(typeof params === "undefined" || typeof params.clientIp === "undefined"){
					var client = $rootScope.selectedClients[Object.keys($rootScope.selectedClients)[0]];
					if(client){
						clientIp = client.ip;
					}
  				}
  				else{
  					clientIp = params.clientIp;
  				}
  				
  				if(clientIp){
	  				var url = "http://"+clientIp+commands.REALTIME_LOG;
	  				window.open(url,"","resizable=yes,toolbar=no,status=no,location=no,menubar=no,scrollbars=yes");
	  			}
  			}
  			else if(command == 'SOFTWARE_INVENTORY'){
  				var client = $rootScope.selectedClients[Object.keys($rootScope.selectedClients)[0]];
  				// Preparar el scope para el sweet alert
  				var options = {
  					scope: {
  						partitions: [], 
  						selectedPart: null
  					}
  				};

  				// Comprobar tipo de cada particion para ver si es clonable
  				// var parttable = $rootScope.constants.partitiontable[client.partitions[0].partitionCode-1];
  				// buscar las particiones que sean clonables
  				for(var index = 1; index < client.partitions.length; index++){
  					if(client.partitions[index].osName !== "DATA" && client.partitions[index].osName !== ""){
  						// Crear como nombre para mostrar, el disco y partición del sistema
  						var obj = angular.copy(client.partitions[index]);
  						obj.name = "disco: "+obj.numDisk+", part: "+obj.numPartition+", SO: "+client.partitions[index].osName;
  						options.scope.partitions.push(obj);
  					}
  				}

  				ogSweetAlert.swal({
				   title: $filter("translate")("select_partition_to_inventary"),
				   //text: $filter("translate")("action_cannot_be_undone"),
				   type: "info",
				   html:"<select ng-model='selectedPart' ng-options='partition as partition.name for partition in partitions'></select>",				    
				   showCancelButton: true,
				   confirmButtonColor: "#3c8dbc",
				   confirmButtonText: $filter("translate")("done"),
				   closeOnConfirm: true
				}, 
				function(result, $scope){
					if(result == true && $scope.selectedPart){						
						// Montar el script con el disco y partición elegida
						vm.execution.script = commands.SOFTWARE_INVENTORY + " "+$scope.selectedPart.numDisk+" "+$scope.selectedPart.numPartition;
						loadClients();
						sendCommand();
					}
				},
				null,
				options);
  			}
  			else{
	  			if(command == 'REBOOT'){
	  				vm.execution.script = commands.REBOOT;
	  			}
	  			else if(command == 'POWER_OFF'){
	  				vm.execution.script = commands.POWER_OFF;
	  			}
	  			else if(command == 'POWER_ON'){
	  				vm.execution.script = "wakeonlan";
	  			}
	  			else if(command == 'HARDWARE_INVENTORY'){
	  				vm.execution.script = commands.HARDWARE_INVENTORY;
	  			}
	  			else if(command == 'RUN_SCRIPT'){
	  				vm.execution.script = params?(params.script||vm.ogInstructions):vm.ogInstructions;
	  			}
	  			else if(command == 'REFRESH_INFO'){
	  				vm.execution.script = commands.REFRESH_INFO;
	  			}

	  			// Comprobar si en los parametros viene la opcion de guardar
	  			if(typeof params !== "undefined" && params.save == true){
	  				var options = {
	  					scope: {
	  						execute: true,
	  						command:{}
	  					}
	  				}
	  				// Mostrar cuadro de dialogo para guardar procedimiento
	  				ogSweetAlert.swal({
					   title: $filter("translate")("new_command_name"),
					   //text: $filter("translate")("action_cannot_be_undone"),
					   type: "info",
					   html:
					   '<form style="text-align: left; padding-left: 10px">\
						   <div class="form-group">\
		                    	<label for="execute" translate="execute">\
		                    	</label>\
		                    	<div class="checkbox clip-check check-primary checkbox-inline">\
		                      		<input id="execute" icheck checkbox-class="icheckbox_square-blue" radio-class="iradio_square-blue" type="checkbox" class="selection-checkbox" ng-model="execute" />\
		                      	</div>\
		                  	</div>\
							<div class="form-group">\
								<label translate="title"></label>\
								<input type="text" class="form-control" ng-model="command.title" />\
							</div>\
							<div class="form-group">\
		                    	<label for="parameters" translate="parameters"></label>\
		                    	<div class="checkbox clip-check check-primary checkbox-inline">\
		                      		<input id="parameters" icheck checkbox-class="icheckbox_square-blue" radio-class="iradio_square-blue" type="checkbox" class="selection-checkbox" ng-model="command.parameters" />\
		                      	</div>\
		                      	<p class="help-block" translate="help_command_parameters"></p>\
		                  	</div>\
	                  	</form>',
					   showCancelButton: true,
					   confirmButtonColor: "#3c8dbc",
					   confirmButtonText: $filter("translate")("done"),
					   closeOnConfirm: true
					}, 
					function(result, $scope){
						if(result == true && $scope.command){
							$scope.command.script = vm.execution.script;
							$scope.command.type = vm.execution.type;
							CommandsResource.save($scope.command).then(
								function(response){
									// Si se seleccionó continuar con la ejecución
									if($scope.execute){
										loadClients();
			  							sendCommand();
			  						}
			  						else{
			  							$state.go("app.commands",{},{reload: true});
			  						}
								},
								function(error){
									toaster.pop({type: "error", title: "error",body: error});
								}
							)
						}
					},
					null,
					options);
	  			}
	  			else{

		  			loadClients();
		  			sendCommand();
		  		}
	  		}
  		}


		function loadClients(){
			if($rootScope.selectedClients){
				vm.execution.clients = _.join(Object.keys($rootScope.selectedClients));
			}
		}

	}
})();