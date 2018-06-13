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
	  .controller('LabsViewController', function($state, $rootScope, $scope, LabsResource) {
	  		var labId = $state.params.labId;

	  		LabsResource.get({ouid: $rootScope.user.ou.id, labid: labId}).then(
	  			function(response){
	  				$scope.lab = response;
	  			},
	  			function(error){
	  				console.log(error);
	  			}
	  		);



	  		
	  });
})();