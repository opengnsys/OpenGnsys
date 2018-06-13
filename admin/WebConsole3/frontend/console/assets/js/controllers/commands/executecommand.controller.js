(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('ExecuteCommandController', ExecuteCommandController);

	ExecuteCommandController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$transitions', '$interval', '$filter', 'toaster', 'ogSweetAlert', 'TracesResource', 'CommandsResource', 'OGCommandsService'];

	function ExecuteCommandController($rootScope, $scope, $state, $timeout, $transitions, $interval,$filter, toaster, ogSweetAlert, TracesResource, CommandsResource, OGCommandsService) {
		var vm = this;
		vm.execution = {};
		vm.selectedCommand = {
			inputs: [],
			script: ""
		};
		vm.newCommand = "true";
		vm.sendCommand = sendCommand;
		vm.loadCommands = loadCommands;
		vm.executeSelectedCommand = executeSelectedCommand;
		vm.updateSelectedCommand = updateSelectedCommand;
		vm.updateScript = updateScript;

		init();


		function init(){
			if($rootScope.user && $rootScope.selectedClients){
				loadFormOptions();
			}
			else{
				// TODO - dar error?
				ogSweetAlert.error($filter("translate")("opengnsys_error"), $filter("translate")("not_clients_selected"));
				$state.go("app.ous");
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
           		OGCommandsService.ogInstructions = "";
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

  		function loadFormOptions(){
			CommandsResource.options().then(
				function(result){
					vm.formOptions = result;
				}
			);
		};

		function loadCommands(){
			CommandsResource.query().then(
				function(result){
					vm.commands = result;
				}
			);
		}


		function executeSelectedCommand() {
			// Ejecuta el contenido de ogInstructions
			OGCommandsService.execute("RUN_SCRIPT");
		}

		function updateSelectedCommand() {
			getParamsNumber(vm.selectedCommand);
			OGCommandsService.ogInstructions = vm.selectedCommand.script;
		}


		function updateScript(){
			var script = vm.selectedCommand.script;
			for(var index = 0; index < vm.selectedCommand.inputs.length; index++){
				script = script.replace("@"+(index+1), vm.selectedCommand.inputs[index]);
			}
			OGCommandsService.ogInstructions = script;
			
		}

		function getParamsNumber(command){
			var params = [];
			if(command.parameters == true){
				var allparams = command.script.match(/@[1-9]+/g);
				for(var index = 0; index < allparams.length; index++){
					if(params.indexOf(allparams[index]) == -1){
						params.push(allparams[index]);
					}
				}
				vm.selectedCommand.inputs = params;
			}
			return params.length;
		}

	  };
})();