var appName = "ogWebConsole";
(function(){
  'use strict';
  /**
   * @ngdoc overview
   * @name ogWebConsole
   * @description
   * # ogWebConsole
   *
   * Main module of the application.
   */
  angular.module(appName, [
      'ui.router',
      'ui.bootstrap',
      'ngTable',
      'ngTouch',
      'joshtate04.ngSweetAlert2',
      'ngSanitize',
      'angular-loading-bar',
      'superlogin',
      'ngResource',
      'ngCookies',
      'angular-oauth2',
      'pascalprecht.translate',
      'gbn-lte-admin',
      'globunet.utils',
      'angular-flot',
      'toaster',
      'daterangepicker'])
  .config(oauthConfig)
  .config(translateConfig)
  .config(autoFormConfig)
  .run(runInit);

  oauthConfig.$inject = ['OAuthProvider', 'OAuthTokenProvider', 'URL_BASE', 'API_PUBLIC_URL', 'OAUTH_DOMAIN', 'OAUTH_CLIENT_ID', 'OAUTH_CLIENT_SECRET'];
  translateConfig.$inject = ['$translateProvider'];
  autoFormConfig.$inject = ['gbnAutoFormConfigProvider'];
  runInit.$inject = ['$rootScope', '$http', '$q', '$state', 'lteAdminInitService', 'constants', 'OgEngineResource', 'ValidateFormsService', 'OGCommonService', 'OGCommandsService'];

  function runInit($rootScope, $http, $q, $state, lteAdminInitService, constants, OgEngineResource, ValidateFormsService, OGCommonService, OGCommandsService){
    // Inicializar la plantilla
    lteAdminInitService.init();

    $rootScope.app = {
      name: "OpenGnsys",
      shortName: "OG",
      version: "3.0.0 beta",
      theme: "skin-black"
    };
    // Inyectar el servicio opengnsys a nivel global
    $rootScope.OGCommonService = OGCommonService;
     $rootScope.OGCommandsService = OGCommandsService;

    // Inyectar en rootScope las constantes
    $rootScope.constants = constants;
    // servicio de validacion de formularios
    $rootScope.ValidateFormsService = ValidateFormsService;
    $rootScope.state = $state;


    if(localStorage.getItem("user")){
      OGCommonService.loadUserConfig();
      $http.defaults.headers.common.Authorization = "Bearer "+$rootScope.user.auth.access_token;
      OGCommonService.loadEngineConfig();
    }
  }

  function oauthConfig(OAuthProvider, OAuthTokenProvider, URL_BASE, API_PUBLIC_URL, OAUTH_DOMAIN, OAUTH_CLIENT_ID, OAUTH_CLIENT_SECRET) {
    OAuthProvider.configure({
      baseUrl: URL_BASE,
      clientId: OAUTH_CLIENT_ID,
      clientSecret: OAUTH_CLIENT_SECRET, // optional
      grantPath: OAUTH_DOMAIN,
    });
    OAuthTokenProvider.configure({
      name: 'token',
      options: {
        secure: true
      }
    });
  };


  // translate config
  function translateConfig($translateProvider) {

      // prefix and suffix information  is required to specify a pattern
      // You can simply use the static-files loader with this pattern:
      $translateProvider.useStaticFilesLoader({
          prefix: 'assets/i18n/',
          suffix: '.json'
      });

      // Since you've now registered more then one translation table, angular-translate has to know which one to use.
      // This is where preferredLanguage(langKey) comes in.
      $translateProvider.preferredLanguage('es');

      // Store the language in the local storage
      $translateProvider.useLocalStorage();

      // Enable sanitize
      $translateProvider.useSanitizeValueStrategy('escape');
  }

  function autoFormConfig(gbnAutoFormConfigProvider){
  var templates = {
      checkbox: '<div class="form-group"\ ng-class="{{ngClass}}">\
                    <label for="{{field}}">\
                      {{label}}\
                    </label>\
                    <div class="checkbox clip-check check-primary checkbox-inline">\
                      <input id="{{field}}" ng-change="{{ngChange}}" icheck checkbox-class="icheckbox_square-blue" radio-class="iradio_square-blue" type="checkbox" class="selection-checkbox" value="{{value}}" ng-model="{{model}}" />\
                      </div>\
                  </div>'
  };
  gbnAutoFormConfigProvider.setTemplates(templates);
}

})();
  
    
    


    
