(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('CommandsExecutionController', CommandsExecutionController);

	CommandsExecutionController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$transitions', '$interval', '$filter', 'toaster', 'ogSweetAlert', 'TracesResource', 'CommandsResource', 'OGCommonService'];

	function CommandsExecutionController($rootScope, $scope, $state, $timeout, $transitions, $interval,$filter, toaster, ogSweetAlert, TracesResource, CommandsResource, OGCommonService) {
		var vm = this;
		vm.execution = {};
		vm.sendCommand = sendCommand;
		vm.execute = execute;
		vm.deleteTask = deleteTask;
		vm.relaunchTask = relaunchTask;
		
		$transitions.onStart({to: "app.*"}, function(trans){
			if($rootScope.timers.executionsInterval.object == null){
              $rootScope.timers.executionsInterval.object = $interval(function() {
                getExectutionTasks();
              }, $rootScope.timers.executionsInterval.tick);
            }
        });


		init();


		function init(){
			if($rootScope.user){
				if($rootScope.timers.executionsInterval.object == null){
					getExectutionTasks();
					$rootScope.timers.executionsInterval.object = $interval(function() {
						getExectutionTasks();
					}, $rootScope.timers.executionsInterval.tick);
				}
				loadClients();
			}
		}

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
           		OGCommonService.ogInstructions = "";
	  			CommandsResource.execute(vm.execution).then(
		  			function(response){
			  			toaster.pop({type: "success", title: "success",body: "Successfully saved"});
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
	  			else if(command == 'HARDWARE_INVENTORY'){
	  				vm.execution.script = commands.HARDWARE_INVENTORY;
	  			}
	  			else if(command == 'RUN_SCRIPT'){
	  				vm.execution.script = params.script;
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


		function getExectutionTasks(){
			TracesResource.query({finished: 0}).then(
				function(result){
					$rootScope.executionTasks = result;
				},
				function(error){

				}
			);
		}

		function deleteTask(task){
			ogSweetAlert.question(
				$filter("translate")("delete_task"), 
				$filter("translate")("sure_to_delete_task")+"?",
				function(result){
					if(result){
						TracesResource.delete({traceId: task.id}).then(
							function(response){
								toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_deleted")});
								getExectutionTasks();
							},
							function(error){
								toaster.pop({type: "error", title: "error",body: error});
							}
						);

					}
				}
			);

		}

		function relaunchTask(task){
			ogSweetAlert.question(
				$filter("translate")("relaunch_task"), 
				$filter("translate")("sure_to_relaunch_task")+"?",
				function(result){
					if(result){

					}
				}
			);
		}


	  };
})();