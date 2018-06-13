(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('DeployImageController', DeployImageController);

	DeployImageController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'ogSweetAlert', 'toaster', 'ImagesResource', 'OGCommonService'];

	function DeployImageController($rootScope, $scope, $state, $timeout, $filter, ogSweetAlert, toaster, ImagesResource, OGCommonService) {
		var vm = this;
		vm.torrent = {
			mode: "peer",
			seedTime: "60"
		};
		vm.multicast = {
			port: "9000",
			address: "239.194.16.140",
			mode: "full-duplex",
			speed: 90,
			maxClients: 50,
			maxWaitTime: 60
		}
		vm.disk = 1;
		vm.partition = 1;

		vm.images = [];
		vm.deployMethods = [];
		vm.deployMethod = "MULTICAST";
		vm.updateDeployOptions = updateDeployOptions;
		vm.generateOgInstruction = generateOgInstruction;
		
		init();

		function init(){
			vm.deployImage = "true";
			updateDeployOptions();
			if($rootScope.user){
				// Comprobar la selección de clientes
				if($rootScope.selectedClients){
					ImagesResource.query().then(
						function(response){
							vm.images = response;
						},
						function(error){

						}
					);

				}
				else{
					// TODO - dar error?
					ogSweetAlert.error($filter("translate")("opengnsys_error"), $filter("translate")("not_clients_selected"));
					$state.go("app.ous");
				}
			}
		}

		function updateDeployOptions(){
			if(vm.deployImage === "true"){
				vm.deployMethods = $rootScope.constants.deployMethods.deployImage;
			}
			else{
				// Si es updateCache, se quitan las opciones de deploy direct
				vm.deployMethods = $rootScope.constants.deployMethods.updateCache;
			}
		}
	
		/**/
		function generateOgInstruction(){
			var script = "";
			var disk = vm.disk;
			var partition = vm.partition;
			// Capturar ip del repositorio de la imagen elegida
			var ip = "172.16.140.210";
			var imgName = vm.image.canonicalName;
			var target = " "+disk+" "+partition;
			var log = "ogEcho log session \"[0] $MSG_SCRIPTS_TASK_START ";

			// Modo deploy
			if(vm.deployImage === "true"){
				script = "deployImage ";
			}
			// Modo updatecache
			else{
				script = "updateCache ";
				ip = "REPO";
				imgName += ".img";
				target = "";
			}
			script += ip + " /" + imgName+target+ " " + vm.deployMethod;
			log += script+"\"\n";
			script = log+script;

			// Modo
			var params = "";
			if(vm.deployMethod == 'MULTICAST' || vm.deployMethod == 'MULTICAST-DIRECT'){
				params = vm.multicast.port+":"+vm.multicast.mode+":"+vm.multicast.address+":"+vm.multicast.speed+"M:"+vm.multicast.maxClients+":"+vm.multicast.maxWaitTime;
			}
			else if(vm.deployMethod == 'TORRENT'){
				params = vm.torrent.mode+":"+vm.torrent.seedTime;
			}
			script += " "+params;

 			$rootScope.OGCommandsService.ogInstructions = script;
		}

	  };
})();