(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('CreateImageController', CreateImageController);

	CreateImageController.$inject = ['$rootScope', '$scope','$state', '$filter', '$timeout', '$q', 'toaster', 'ogSweetAlert', 'ImagesResource', 'CommandsResource'];

	function CreateImageController($rootScope, $scope, $state, $filter, $timeout, $q, toaster, ogSweetAlert, ImagesResource, CommandsResource) {
		var vm = this;
		vm.client = {};
		vm.execution = {};
		vm.command = {};
		vm.images = [];
		vm.setCanonicalName = setCanonicalName;
		vm.sendCommand = sendCommand;
		vm.isClonable = isClonable;

		init();


		function init(){
			if($rootScope.user && $rootScope.selectedClients){
				var clientId = Object.keys($rootScope.selectedClients)[0];
				vm.client = $rootScope.selectedClients[clientId];
				vm.execution.clients = clientId;
				loadImages();
			}
			else{
				// TODO - dar error?
				ogSweetAlert.error($filter("translate")("opengnsys_error"), $filter("translate")("not_clients_selected"));
				$state.go("app.ous");
			}
		}


		function sendCommand(){
  			var result = $rootScope.ValidateFormsService.validateForms(vm);
  			if(!vm.selectedPartition){
  				toaster.pop({type: "error", title: "error",body: $filter("translate")("you_must_select_partition")});
  			}
  			else{
  				var disk = vm.client.partitions[vm.selectedPartition].numDisk;
  				var partition = vm.client.partitions[vm.selectedPartition].numPartition;
  				// Al crear la imagen, le asociamos un perfil software
  				vm.execution.script	 = $rootScope.constants.commands.SOFTWARE_INVENTORY + " " + disk + " " + partition+"\n";
	  			vm.execution.script += $rootScope.constants.commands.CREATE_IMAGE + " " + disk + " " + partition + " " + vm.command.canonicalName + " REPO ";
	  			vm.execution.script = vm.execution.script.replace(/\"/g, "\\\"").replace(/\$/g, "\\\$");

	  			var image = vm.command.image;
	  			var newImage = false;


	  			// Crear la imagen si no existe
	  			if(!image){
	  				newImage = true;
	  				// Comprobar que exista el repositorio, sino no podemos crear la nueva imagen
	  				if(!$rootScope.repositories){
	  					result = false;
						toaster.pop({type: "error", title: "error",body: $filter("translate")("no_repository_exist")});
	  				}
	  				else{
	  					// Usar el repositorio por defecto
	  					var repository = $rootScope.repositories[0];
	  					image = {
		  					canonicalName: vm.command.canonicalName,
		  					description: $filter("translate")("image_created_automatically"),
		  					repository: repository.id
		  				};
	  				}
	  			}

	  			// Asignar a la imagen los atributos del sistema operativo elegido
	  			image.client = vm.client.id;

	            // Si no hubo ningun error se guardan todas las pgms
	           	if(result == true){
	           		var promises = [];
	           		if(newImage == true){
	           			promises.push(ImagesResource.save(image));
	           		}
	           		else{
	           			var imageCopy = angular.copy(image);
	           			delete imageCopy.id;
	           			delete imageCopy.softwareProfile;
	           			promises.push(ImagesResource.update({imageId: image.id}, imageCopy));	
	           		}
	           		vm.execution.type = "CREATE_IMAGE";
	           		promises.push(CommandsResource.execute(vm.execution));
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
		  	}
  		}

  		function setCanonicalName() {
  			vm.command.canonicalName = vm.command.image.canonicalName;
  		}

  		function loadImages() {
  			ImagesResource.query().then(
  				function(response){
  					vm.images = response;
  				},
  				function(error){
  					toaster.pop({type: "error", title: "error",body: error});
  				}
  			);
  		}

  		function isClonable(partition) {
  			var clonable = false;
  			var index = 0;
  			var code = partition.partitionCode;

  			if(partition.numPartition != 0){
	  			// Buscar el codigo entre las constantes
	  			while(index < $rootScope.constants.partitiontable.length && !clonable){
	  				// para cada tabla de particiones, buscamos el codigo de la particion
					var elements = $filter("filter")($rootScope.constants.partitiontable[index].partitions, {id: partition.partitionCode.padStart(2,"0")}, true);
					clonable = (elements.length > 0 && elements[0].clonable == true);
					index++;
	  			}
	  		}

			return clonable;
  		}

	  };
})();