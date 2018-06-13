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
	  .controller('MainCtrl', function($rootScope, $scope, LabsResource) {

	  		$scope.numLabs = 0;
	  		$scope.labsText = "Loading";
	  		
	  		LabsResource.query({ouid: $rootScope.user.ou.ouid}).$promise.then(
	  			function(response){
	  				$scope.labsText = "Labs loaded";
	  				$scope.numLabs = response.length;
	  				$rootScope.user.ou.labs = response;
	  				localStorage.setItem("user", JSON.stringify($rootScope.user));
	  			},
	  			function(error){
	  				console.log(error);
	  			}
	  		);
	  });
})();