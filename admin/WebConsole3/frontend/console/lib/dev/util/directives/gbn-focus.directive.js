(function(){
  'use strict';
  /**
   * @ngdoc directive
   * @name gbnGenerateTableParams
   * @module globunet.utils
   * @restrict E
   *
   * @description
   * Esta directiva genera de forma automatica los parametros necesarios para una ng-table así como su función para recargar los datos, ordenar y filtrar
   * a partir del modelo.
   * @param search-model {string} variable del scope que actua como campo de búsqueda
   * @param filter-cols {array} Columnas a filtrar mediante search-model
   * @param array {string} nombre del array del que se obtendrán los datos (debe estar en el scope)
   *
   */
  angular.module("globunet.utils")
    .directive("gbnFocus", GbnFocus);

    GbnFocus.$inject = ["$timeout", "$parse"];
  function GbnFocus($timeout, $parse) {
    return {
      restrict: 'A',
      link: function(scope, element, attrs) {
          scope.$watch(attrs.focus, function(newValue, oldValue) {
              if (newValue) { element[0].focus(); }
          });

          if(attrs.gbnBlur){          
            element.bind("blur", function(e) {
                $timeout(function() {
                    $parse(attrs.gbnBlur)(scope);
                }, 0);
            });
          }

          element.bind("focus", function(e) {
              $timeout(function() {
                  $parse(attrs.gbnFocus)(scope);
              }, 0);
          })
      }
    }
  };

})();