(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('NewImageController', NewImageController);

	NewImageController.$inject = ['$rootScope', '$scope', '$state', '$filter', '$timeout', 'ogSweetAlert', 'toaster', 'ImagesResource'];
	
	function NewImageController($rootScope, $scope, $state, $filter, $timeout, ogSweetAlert, toaster, ImagesResource) {
		var vm = this;
		vm.image = {};
		vm.ngTableSearch = {
			text: ""
		};
		vm.formOptions = {};
		vm.save = save;

		init();

		function init(){
			// Los repositorios vienen cargados ya desde config.router
			vm.repositories = $rootScope.repositories;
			loadFormOptions();
		}

		function save(Form){
  			var result = $rootScope.ValidateFormsService.validateForm(Form);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
           		var imageCopy = angular.copy(vm.image);
           		imageCopy.repository = vm.image.repository.id;
	  			ImagesResource.save(imageCopy).then(
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

		function loadFormOptions(){
			ImagesResource.options().then(
				function(result){
					vm.formOptions = result;
					if($state.params.type == "basic"){
						vm.formOptions.fields.rows[1].path = {
								type: "text",
								label: "origin_path",
								required: false,
								css: "col-md-12"
						};
					}
				}
			);
		};

	}
})();