(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('DeleteCacheImageController', DeleteCacheImageController);

	DeleteCacheImageController.$inject = ['$rootScope', '$scope','$state', '$filter', '$timeout', '$q', 'toaster', 'ogSweetAlert', 'CommandsResource', 'OGCommonService'];

	function DeleteCacheImageController($rootScope, $scope, $state, $filter, $timeout, $q, toaster, ogSweetAlert, CommandsResource, OGCommonService) {
		var vm = this;
		vm.execution = {};
		vm.command = {};
		vm.sendCommand = sendCommand;
		vm.cacheImages = [];

		init();


		function init(){
			if($rootScope.user && $rootScope.selectedClients){
				var clientIds = Object.keys($rootScope.selectedClients);
				vm.execution.clients = _.join(clientIds);
				// Capturar para todos los clientes todas las imágenes de cache
				vm.cacheImages = [];
				for(var index = 0; index < clientIds.length; index++){
					var client = $rootScope.selectedClients[clientIds[index]];
					var diskConfigs = OGCommonService.getDisksConfigFromPartitions(client.partitions);
					for(var dc = 0; dc < diskConfigs.length; dc++){
						var diskConfig = diskConfigs[dc];
						for(var p = 0; p < diskConfig.partitions.length; p++){
							var partition = diskConfig.partitions[p];
							if(partition.partitionCode == "ca"){
								// Solo cogemos las imagenes .img, no los .sum
								for(var f = 0; f < partition.cacheContent.files.length; f++){
									var file = partition.cacheContent.files[f];
									// Si no es un .sum
									if(!file.name.match(".sum")){
										vm.cacheImages.push(file);
									}
								}
							}
						}
					}
				}
			}
			else{
				// TODO - dar error?
				ogSweetAlert.error($filter("translate")("opengnsys_error"), $filter("translate")("not_clients_selected"));
				$state.go("app.ous");
			}
		}


		function sendCommand(){
			vm.execution.script = "";
			for(var f = 0; f < vm.cacheImages.length; f++){
				if(vm.cacheImages[f].selected == true){
					if(vm.cacheImages[f].type != "D"){
						vm.execution.script += "rm -rf $OGCAC/$OGIMG/"+vm.cacheImages[f].name.trim()+"*";
					}
					else {
						vm.execution.script += "rm -rf $OGCAC/$OGIMG/"+vm.cacheImages[f].name.trim();
					}
					vm.execution.script += "\n";
				}
			}
			vm.execution.script += $rootScope.constants.commands.REFRESH_INFO+"\n";
  			vm.execution.script = vm.execution.script.replace(/\"/g, "\\\"").replace(/\$/g, "\\\$");
			vm.execution.type = "RUN_SCRIPT";

  			CommandsResource.execute(vm.execution).then(
	  			function(response){
		  			toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_executed")});
		  			$state.go("app.ous",{},{reload: true});
		  		},
		  		function(error){
		  			toaster.pop({type: "error", title: "error",body: error});
		  		}
	  		);
  		}


	  };
})();