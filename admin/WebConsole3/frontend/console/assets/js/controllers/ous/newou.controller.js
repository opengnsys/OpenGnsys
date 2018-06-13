(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName)
	  .controller('NewOuController', NewOuController);

	  NewOuController.$inject = ['$state', '$rootScope', '$scope', 'toaster', 'OusResource'];
	  
	  function NewOuController($state, $rootScope, $scope, toaster, OusResource) {
	  		var vm = this;
	  		vm.ou = {};
	  		vm.save = save;

	  		init();

	  		function init(){
	  			// Si viene de una unidad organizativa superior, copiamos sus propiedades
	  			OusResource.get({ouid: $state.params.parent}).then(
	  				function(response){
	  					vm.ou = angular.copy(response);
	  					delete vm.ou.name;
	  					delete vm.ou.id;
	  				},
	  				function(error){
	  					toaster.pop({type: "error", title: "error",body: error});
	  				}
	  			);
	  			OusResource.options().then(
		  			function(response){
			  			vm.formOptions = response;
			  		},
			  		function(error){
			  			console.error(error);
			  		}
		  		);
	  		}


	  		function save(){
	  			var result = $rootScope.ValidateFormsService.validateForms(vm);

	            // Si no hubo ningun error se guardan todas las pgms
	           	if(result == true){
	           		// Si se indicó un padre en la url, se añade dicha propiedad
	           		if($state.params.parent != null){
	           			vm.ou.parent = $state.params.parent;
	           		}
		  			OusResource.save(vm.ou).then(
			  			function(response){
			  				// borrar las ous para recargarlas en el desplegable de la barra de menu
			  				if($state.params.parent == null){
				  				delete $rootScope.ous;
				  			}
				  			toaster.pop({type: "success", title: "success",body: "Successfully saved"});
				  			$state.go("app.ous",{},{reload: true});
				  		},
				  		function(error){
				  			toaster.pop({type: "error", title: "error",body: error});
				  		}
			  		);
		  		}
	  		}

	  };
})();