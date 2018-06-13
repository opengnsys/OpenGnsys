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
	  .controller('EditOuController', EditOuController);

	  EditOuController.$inject = ['$state', '$rootScope', '$scope', '$timeout', 'toaster', 'OusResource'];
	  
	  function EditOuController($state, $rootScope, $scope, $timeout, toaster, OusResource) {
	  		var vm = this;
	  		vm.ou = {};
	  		vm.save = save;

	  		OusResource.get({ouid: $state.params.ou}).then(
	  			function(response){
	  				vm.ou = response;
	  			},
	  			function(error){
	  				console.log(error);
	  			}
	  		);

	  		function save(){
	  			var result = $rootScope.ValidateFormsService.validateForms(vm);

	            // Si no hubo ningun error se guardan todas las pgms
	           	if(result == true){
	           		// Si se indicó un padre en la url, se añade dicha propiedad
	           		if($state.params.parent != null){
	           			vm.ou.parent = $state.params.parent;
	           		}
	           		var ouCopy = angular.copy(vm.ou);
	           		delete ouCopy.clients;
	           		delete ouCopy.comments;
	           		delete ouCopy.urlphoto;
	           		delete ouCopy.id;
	           		if(ouCopy.networkSettings){
		           		delete ouCopy.networkSettings.id;
		           	}
		  			OusResource.update({ouid: vm.ou.id}, ouCopy).then(
			  			function(response){
				  			$state.go("app.ous",{},{reload: true});
				  			$timeout(function(){
					  			toaster.pop({type: "success", title: "success",body: "Successfully saved"});
					  		},0);
				  		},
				  		function(error){
				  			toaster.pop({type: "error", title: "error",body: error});
				  		}
			  		);
		  		}
	  		}
	  };
})();