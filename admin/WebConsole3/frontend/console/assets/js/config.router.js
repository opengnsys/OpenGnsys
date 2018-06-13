(function(){
  'use strict';
  angular.module(appName).config(['$stateProvider','$urlRouterProvider',function ($stateProvider,$urlRouterProvider) {
     
      $urlRouterProvider.otherwise('/login/signin');

      $stateProvider
        .state('app', {
          url:'/app',
          abstract: true,
          templateUrl: 'assets/views/app.html',
          controller: 'MainMenuController',
          controllerAs: 'mc',
          resolve: {
            isLogged: isLogged,
            ous: getOus
          }
      })
        .state('app.dashboard', {
          url:'/dashboard',
          views: {
            content: {
              templateUrl: 'assets/views/dashboard/main.html',
              controller: 'DashboardController',
              controllerAs: 'vm'
            }
          }
          
      })
      .state('app.users',{
        url:'/users',
        views: {
          content: {
             controller: 'UsersController',
             controllerAs: "vm",
             templateUrl:'assets/views/users/users.html'
          }
        }
       
      })
      .state('app.user',{
        url:'/user',
        abstract: true,
        views: {
          content: {
            template: "<ui-view></ui-view>"
          }
        }
      })
      .state('app.user.profile',{
        url:'/profile',
        controller: 'ProfileController',
        controllerAs: "vm",
        templateUrl:'assets/views/users/profile.html'
      })
      .state('app.repositories',{
        url:'/repositories',
        views: {
          content: {
             controller: 'RepositoriesController',
             controllerAs: "vm",
             templateUrl:'assets/views/repository/repositories.html'
          }
        }
       
      })
       .state('app.hardware',{
        url:'/hardware',
        views: {
          content: {
             controller: 'HardwareController',
             controllerAs: "vm",
             templateUrl:'assets/views/hardware/hardware.html'
          }
        },
        resolve:{
          hardwareTypes: getHardwareTypes,
          hardwareComponents: getHardwareComponents,
          groups: getGroups
        }
       
      })
       .state('app.hardware.profile',{
          url:'/profile',
          abstract: true,
          template: "<ui-view></ui-view>",
          resolve: {
            profileFunctions: profileFunctions
          }
      })
       .state('app.hardware.profile.new',{
         url:'/new',
         controller: 'NewHardwareProfileController',
         controllerAs: "vm",
         templateUrl:'assets/views/hardware/hardware-profile.html'
       
      })
       .state('app.hardware.profile.edit',{
         url:'/edit/:profileId',
         controller: 'EditHardwareProfileController',
         controllerAs: "vm",
         templateUrl:'assets/views/hardware/hardware-profile.html'
       
      })
       .state('app.hardware.component',{
         url:'/component',
         abstract: true,
         template: "<ui-view></ui-view>"
       
      })
       .state('app.hardware.component.new',{
         url:'/new',
         controller: 'NewHardwareComponentController',
         controllerAs: "vm",
         templateUrl:'assets/views/hardware/hardware-component.html'
       
      })
       .state('app.software',{
        url:'/software',
        views: {
          content: {
             controller: 'SoftwareController',
             controllerAs: "vm",
             templateUrl:'assets/views/software/software.html'
          }
        },
        resolve:{
          softwareTypes: getSoftwareTypes,
          softwareComponents: getSoftwareComponents,
          groups: getGroups
        }
       
      })
       .state('app.software.profile',{
          url:'/profile',
          abstract: true,
          template: "<ui-view></ui-view>",
          resolve: {
            profileFunctions: profileFunctions
          }
      })
       .state('app.software.profile.new',{
         url:'/new',
         controller: 'NewSoftwareProfileController',
         controllerAs: "vm",
         templateUrl:'assets/views/software/software-profile.html'
       
      })
       .state('app.software.profile.edit',{
         url:'/edit/:profileId',
         controller: 'EditSoftwareProfileController',
         controllerAs: "vm",
         templateUrl:'assets/views/software/software-profile.html'
       
      })
       .state('app.software.component',{
         url:'/component',
         abstract: true,
         template: "<ui-view></ui-view>"
       
      })
       .state('app.software.component.new',{
         url:'/new',
         controller: 'NewSoftwareComponentController',
         controllerAs: "vm",
         templateUrl:'assets/views/software/software-component.html'
       
      })
      .state('app.menus',{
        url:'/menus',
        views: {
          content: {
             controller: 'MenusController',
             controllerAs: "vm",
             templateUrl:'assets/views/menu/menus.html'
          }
        },
        resolve:{
          groups: getGroups
        }
      })
      .state('app.menus.new',{
          url:'/new',
          controller: 'NewMenuController',
          controllerAs: "vm",
          templateUrl:'assets/views/menu/menu.html'
      })
      .state('app.menus.edit',{
          url:'/:menuId',
          controller: 'EditMenuController',
          controllerAs: "vm",
          templateUrl:'assets/views/menu/menu.html'
      })
      .state('app.images',{
        url:'/images',
        views: {
          content: {
             controller: 'ImagesController',
             controllerAs: "vm",
             templateUrl:'assets/views/image/images.html'
          }
        }
      })
      .state('app.images.new',{
          url:'/new/:type',
          controller: 'NewImageController',
          controllerAs: "vm",
          templateUrl:'assets/views/image/new-image.html',
          resolve: {
            repositories: getRepositories
          }
      })
      .state('app.images.edit',{
        url:'/edit/:imageId',
        controller: 'EditImageController',
        controllerAs: "vm",
        templateUrl:'assets/views/image/edit-image.html',
        resolve: {
          repositories: getRepositories
        }
      })
      .state('app.ous',{
          url:'/ous',
          views: {
            content: {
              templateUrl:'assets/views/ous/ous.html',
              controller: "OusController",
              controllerAs: "vm"
            }
          }
      })
      .state('app.ous.new',{
          url:'/new/:parent',
          templateUrl:'assets/views/ous/ou.html',
          controller: "NewOuController",
          controllerAs: "vm",
          params: {parent: null}
      })
      .state('app.ous.edit',{
          url:'/edit/:ou',
          templateUrl:'assets/views/ous/ou.html',
          controller: "EditOuController",
          controllerAs: "vm"
      })
      .state('app.client',{
          url: '/client/:ou',
          abstract: true,
          views: {
              content: {
                template: "<ui-view></ui-view>"
              }
          }
      })
      .state('app.client.new',{
          url:'/new',
          templateUrl:'assets/views/client/new-client.html',
          controller: "NewClientController",
          controllerAs: "vm",
          resolve: {
            repositories: getRepositories
          }
      })
      .state('app.client.edit',{
          url:'/edit/:clientId',
          templateUrl:'assets/views/client/new-client.html',
          controller: "EditClientController",
          controllerAs: "vm",
           resolve: {
            repositories: getRepositories
          }
      })
      .state('app.client.dhcp',{
          url:'/dhcp',
          templateUrl:'assets/views/client/dhcp-clients.html',
          controller: "DhcpClientController",
          controllerAs: "vm",
          resolve: {
            repositories: getRepositories
          }
      })
      .state('app.commands',{
        url:'/commands',
        views: {
          content: {
             controller: 'CommandsController',
             controllerAs: "vm",
             templateUrl:'assets/views/command/commands.html'
          }
        }
      })
      .state('app.commands.new',{
          url:'/new',
          controller: 'NewCommandController',
          controllerAs: "vm",
          templateUrl:'assets/views/command/new-command.html'
      })
      .state('app.commands.edit',{
          url:'/:commandId',
          controller: 'EditCommandController',
          controllerAs: "vm",
          templateUrl:'assets/views/command/new-command.html'
      })
      .state('app.commands.execute',{
          url:'/execute',
          controller: 'ExecuteCommandController',
          controllerAs: "vm",
          templateUrl:'assets/views/command/execute-command.html'
      })
      .state("app.commands.create_image",{
        url: "/create_image",
        controller: 'CreateImageController',
        controllerAs: 'vm',
        templateUrl:'assets/views/command/create-image-command.html',
        resolve: {
          repositories: getRepositories
        }
      })
      .state("app.commands.deploy_image",{
        url: "/deploy_image",
        controller: 'DeployImageController',
        controllerAs: 'vm',
        templateUrl:'assets/views/command/deploy-image-command.html',
        resolve: {
          repositories: getRepositories
        }
      })
      .state("app.commands.partition_format",{
        url: "/partition_format",
        controller: 'PartitionFormatController',
        controllerAs: 'vm',
        templateUrl:'assets/views/command/partition-format-command.html'
       
      })
      .state("app.commands.format",{
        url: "/format",
        controller: 'FormatController',
        controllerAs: 'vm',
        templateUrl:'assets/views/command/format-command.html'
       
      })
      .state("app.commands.login",{
        url: "/login",
        controller: 'LoginCommandController',
        controllerAs: 'vm',
        templateUrl:'assets/views/command/login-command.html'
      })
       .state("app.commands.delete_cache_image",{
        url: "/delete_cache_image",
        controller: 'DeleteCacheImageController',
        controllerAs: 'vm',
        templateUrl:'assets/views/command/delete-cache-image-command.html'
      })
      .state("app.traces", {
        url:"/traces",
        views: {
          content: {
            controller: "TracesController",
            controllerAs: "vm",
            templateUrl: "assets/views/trace/traces.html"
          }
        }
      })
      .state("app.netboot", {
        url: "/netboot",
        views: {
          content: {
            controller: "NetbootController",
            controllerAs: "vm",
            templateUrl: "assets/views/netboot/netboot.html"
          }
        }
      })
      .state("app.netboot.new", {
          url: "/new/:copyId",
          controller: "NewNetbootController",
          controllerAs: "vm",
          templateUrl: "assets/views/netboot/new-netboot.html",
          params: {copyId: null}
      })
      .state("app.netboot.edit", {
        url: "/edit/:netbootId",
        controller: "EditNetbootController",
        controllerAs: "vm",
        templateUrl: "assets/views/netboot/new-netboot.html"
        
      })
      .state("app.netboot.clients", {
        url: "/clients",
        controller: "ClientsNetbootController",
        controllerAs: "vm",
        templateUrl: "assets/views/netboot/netboot-clients.html"
      })
      .state("login",{
        abstract: true,
        url: "/login",
        template: "<ui-view></ui-view>"
      })
      .state("login.signin",{
        url: "/signin",
       
            controller: 'LoginController',
            controllerAs: 'vm',
            templateUrl:'assets/views/pages/login.html'
      })
      /*
      .state("login.logout",{
        url: "/logout",
         views: {
          content:{
            controller: 'LogoutController',
            controllerAs: 'vm',
            templateUrl:'assets/views/pages/login.html'
          }
        }
      })*/;
  }]);

  getOus.$inject = ["$state", "$rootScope", "toaster", "OusResource"]
  function getOus($state, $rootScope, toaster, OusResource){

    return $rootScope.ous || OusResource.query().then(
                              function(response){
                                $rootScope.ous = response;
                                return response;
                              },
                              function(error){
                               toaster.pop({type: "error", title: "error",body: error});
                              }
                            );
  }

  getGroups.$inject = ["$state", "$rootScope", "toaster", "GroupsResource"]
  function getGroups($state, $rootScope, toaster, GroupsResource){

    return [];
  }


  getRepositories.$inject = ["$rootScope", "toaster", "RepositoriesResource"];
  function getRepositories($rootScope, toaster, RepositoriesResource){
    return $rootScope.repositories || RepositoriesResource.query().then(
                              function(response){
                                $rootScope.repositories = response;
                                return response;
                              },
                              function(error){
                                toaster.pop({type: "error", title: "error",body: error});
                              }
                            );
  }

  getHardwareTypes.$inject = ["$rootScope"];
  function getHardwareTypes($rootScope){
    return $rootScope.constants.hardwaretypes;
  }

  getHardwareComponents.$inject = ['$rootScope', "HardwareComponentsResource"];
  function getHardwareComponents($rootScope, HardwareComponentsResource){
    return HardwareComponentsResource.query().then(
          function(response){
            return response;
          },
          function(error){
            alert(error);
          }
        );
  }

  getSoftwareTypes.$inject = ["$rootScope"];
  function getSoftwareTypes($rootScope){
    return $rootScope.constants.softwaretypes;
  }

  getSoftwareComponents.$inject = ['$rootScope', "SoftwareComponentsResource"];
  function getSoftwareComponents($rootScope, SoftwareComponentsResource){
    return SoftwareComponentsResource.query().then(
          function(response){
            return response;
          },
          function(error){
            alert(error);
          }
        );
  }


  profileFunctions.$inject = [];
  function profileFunctions(){
    return {
      /**
       * Funcion que selecciona/deselecciona un componente de un perfil hardware o software
       */
      checkUnchekComponent: function (profile, component){
          // Seleccionar o deseleccionar el componente en el perfil hardware o software
          var array = profile.hardwares||profile.softwares;
          // Si el componente que llega está deseleccionado
          if(component.$$selected == false){
            // Hay que quitarlo del perfil hardware
            var index = array.indexOf(component.id);
            if(index != -1){
              array.splice(index,1);
            }
          }
          else{
            array.push(component.id);
          }
      } 
    }
  }

  isLogged.$inject = ["$rootScope"]
  function isLogged($rootScope){
    var result = false
     if($rootScope.user && $rootScope.user.username){
       result = true;
     }
     return result;
  }

})();