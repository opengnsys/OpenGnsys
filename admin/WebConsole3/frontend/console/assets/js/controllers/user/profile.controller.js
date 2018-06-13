(function(){
    'use strict';
    /**
     * @ngdoc function
     * @name ogWebConsole.controller:MainCtrl
     * @description
     * # MainCtrl
     * Controller of the ogWebConsole
     */
    angular.module(appName).controller('ProfileController', ProfileController);

    ProfileController.$inject=['$rootScope', '$state', '$filter', 'toaster'];

    function ProfileController($rootScope,$state, $filter, toaster) {
      var vm = this;
      vm.changeTheme = changeTheme;
      vm.save = save;

      init();

      function init(){
        vm.user  = $rootScope.user;

        vm.formOptions = {
          fields: {
            rows: [
              {
                "username": {
                  type: "text",
                  readonly: true,
                  label: "name",
                  css: "col-md-2"
                }
              }
            ]
          }
        };
        
      }

      function changeTheme() {
        $rootScope.app.theme = vm.user.preferences.theme;
      }

      function save(){
        $rootScope.user = vm.user;
        localStorage.setItem("user", JSON.stringify($rootScope.user));
        toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_saved")});
      }

    } 
       
})();
