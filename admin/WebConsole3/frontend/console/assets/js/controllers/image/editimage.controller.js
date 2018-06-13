(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('EditImageController', EditImageController);

	EditImageController.$inject = ['$rootScope', '$scope', '$state', '$timeout', '$filter', 'toaster','ImagesResource', 'ValidateFormsService'];
	
	function EditImageController($rootScope, $scope, $state, $timeout, $filter, toaster, ImagesResource, ValidateFormsService) {
		var vm = this;
		vm.image = {};
		vm.formOptions = {};
		
		vm.loadImage = loadImage;
		vm.loadFormOptions = loadFormOptions;
		vm.save = save;

		init();

		function init(){
			// Los repositorios vienen cargados ya desde config.router
			vm.repositories = $rootScope.repositories;
			loadImage();
			loadFormOptions();
		}

		function save(Form){
  			var result = $rootScope.ValidateFormsService.validateForm(Form);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
           		var imageCopy = {};
           		// Propiedades a guardar del objeto imagen
           		var properties = ["canonicalName", "repository", "description", "comments"];
           		for(var p = 0; p < properties.length; p++){
           			imageCopy[properties[p]] = vm.image[properties[p]];
           		}
           		imageCopy.repository = vm.image.repository.id;

	  			ImagesResource.update({imageId: vm.image.id}, imageCopy).then(
		  			function(response){
		  				$state.go("app.images",{},{reload: true});
			  			$timeout(function(){
			  				toaster.pop({type: "success", title: $filter("translate")("success"),body: $filter("translate")("successfully_saved")});
			  			},0);
			  			
			  		},
			  		function(error){
			  			toaster.pop({type: "error", title: "error",body: error});
			  		}
		  		);
	  		}
  		}
		

		function loadImage(){

			ImagesResource.get({imageId: $state.params.imageId}).then(
				function(result){
					vm.image = result;
					if(typeof vm.image.partitionInfo === "string"){
						vm.image.partitionInfo = JSON.parse(vm.image.partitionInfo);
					}
					else if(!image.partitionInfo){
						vm.image.partitionInfo = {};
					}

				},
				function(error){
					alert(error);
				}
			);
		};

		function loadFormOptions(){
			ImagesResource.options().then(
				function(result){
					vm.formOptions = result;
				}
			);
		};

	}
})();