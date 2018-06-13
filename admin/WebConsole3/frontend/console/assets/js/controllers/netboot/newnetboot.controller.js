(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('NewNetbootController', NewNetbootController);

	NewNetbootController.$inject = ['$rootScope', '$filter','$state', '$timeout', 'toaster', 'NetbootsResource'];

	function NewNetbootController($rootScope, $filter, $state, $timeout, toaster, NetbootsResource) {
		var vm = this;
		vm.netboot = {};
		vm.save = save;

		init();


		function init(){
			if($rootScope.user){
				loadFormOptions();
				if($state.params.copyId){
					NetbootsResource.get({netbootId: $state.params.copyId}).then(
						function(response){
							vm.netboot.template = response.template;
							vm.netboot.filename = response.filename + "-copy";
							vm.netboot.name = response.name + "-copy";
						},
						function(error){
							toaster.pop({type: "error", title: "error",body: error});
						}
					);
				}
			}
		}

		function save(){
  			var result = $rootScope.ValidateFormsService.validateForms(vm);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
           		// Si se indicó un padre en la url, se añade dicha propiedad
	  			NetbootsResource.save(vm.netboot).then(
		  			function(response){
			  			$state.go("app.netboot",{},{reload: true});
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
			NetbootsResource.options().then(
				function(result){
					vm.formOptions = result;
				}
			);
		};

	  };
})();