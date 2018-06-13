(function(){
	'use strict';
	angular.module(appName).controller("LoginController", LoginController);

	LoginController.$inject = ['$rootScope', '$scope', '$http', '$state', '$filter', '$interval', 'OAuth', 'UserResource', 'OGCommonService'];

	function LoginController($rootScope, $scope, $http, $state, $filter, $interval, OAuth, UserResource, OGCommonService){
		var vm = this;
		vm.user = {
			username: "",
			password: "",
			ou: {}
		};

		$rootScope.$on('oauth:error', authError);

		vm.login = login;
		vm.logout = logout;
		
		function login(){
			vm.loginError = '';
          	vm.authPending = true;
/*
          	localStorage.setItem("user", JSON.stringify(vm.user));

          	$state.go('app.dashboard', {},{reload: true}).then(
  				function(result,a,b){
  					console.log(result);
  				}
  			);

          	/**/
          	OAuth.getAccessToken(vm.user).then(
	  			function(response){
	  				// La respuesta fue correcta
	  				$http.defaults.headers.common.Authorization = 'Bearer '+response.data.access_token;
	  				console.log(response);
	  				vm.user.auth = response.data;
	  				UserResource.me().then(
	  					function(response){
	  						vm.user = angular.extend(vm.user,response);
				  			localStorage.setItem("user", JSON.stringify(vm.user));

				  			$rootScope.user = vm.user;
				  			OGCommonService.loadUserConfig();
				  			OGCommonService.loadEngineConfig().then(
				  				function(response){
					  				$state.go('app.dashboard', {},{reload: true}).then(
						  				function(result,a,b){
						  					console.log(result);
						  				}
						  			);
					  			},
					  			function(error){
					  				vm.error = error;
					  			}
				  			);
	  					},
	  					function(error){
	  						console.log(error);
	  					}
	  				);
	  			},
	  			function(error){
	  				vm.error="invalid_login";
	  			}
	  		);
	  		/**/
		}

		function logout(){
			$rootScope.user = {};
			localStorage.removeItem("user");
			// Parar los timers de actualizacion que haya
			if($rootScope.timers.clientsStatusInterval.object != null){
				$interval.cancel($rootScope.timers.clientsStatusInterval.object);
	            $rootScope.timers.clientsStatusInterval.object = null;
	        }
	        if($rootScope.timers.serverStatusInterval.object != null){
				$interval.cancel($rootScope.timers.serverStatusInterval.object);
	            $rootScope.timers.serverStatusInterval.object = null;
	        }
	        if($rootScope.timers.executionsInterval.object != null){
				$interval.cancel($rootScope.timers.executionsInterval.object);
	            $rootScope.timers.executionsInterval.object = null;
	        }
			$state.go("login.signin");
		}

		function authError(event, rejection) {
	  		// Si no estamos en la ruta login no hacemos nada
	  		if($state.includes('login')){
				// Ignore `invalid_grant` error - should be catched on `LoginController`.
				if ('invalid_grant' === rejection.data.error) {
					vm.error = $filter("gbnToUTF8")($filter("translate")(rejection.data.error));
				}
				else{
					// Redirect to `/login` with the `error_reason`.
	      			vm.error = $filter("gbnToUTF8")($filter("translate")(rejection.data.error));
				}
	  		}
	      
	    };

		
	};
})();