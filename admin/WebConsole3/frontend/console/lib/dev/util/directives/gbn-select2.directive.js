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
    .directive("gbnSelect2", GbnSelect2)
    .directive("gbnSelect2Options", GbnSelect2Options);

    GbnSelect2.$inject = ["$timeout", "$interpolate", "$parse", "$compile"];
    function GbnSelect2($timeout, $interpolate, $parse, $compile) {
      var template = '<select name="{{name}}" {{required}} class="{{selectClass}}" ng-model="$innerModel" multiple="{{multiple}}" data-placeholder="{{placeholder}}" style="{{style}}">\
        {{options}}\
      </select>';

      return {
        restrict: 'E',
        scope: {
          name: "@name",
          required: "@required",
          selectClass: "@selectClass",
          ngModel: "@ngModel",
          multiple: "@multiple",
          placeholder: "@placeholder",
          style: "@style",
          collection: "@",
          optionValue: "@"
        },
        controller: function($scope, $compile, $http) {
          // $scope is the appropriate scope for the directive
          this.addOptions = function(nestedDirective) { // this refers to the controller
            $scope.optionLabel = nestedDirective;
          };
        },
        link: function(scope, element, attrs) {
            // Renderizar la plantilla con los atributos pasados por parametro
            if(!scope.name){
              scope.name = "select2";
            }
            if((typeof scope.required === "boolean" && scope.required === true) || ((typeof scope.required === "string" && scope.required === "true"))){
              scope.required = "required";
            }
            scope.$collection = $parse(scope.collection)(scope.$parent);
            scope.selectClass = scope.selectClass||"form-control select2";
            if(scope.selectClass.indexOf("select2") == -1)
              scope.selectClass += " select2";
            // Asignar como valor inicial el que tenga el modelo seleccionado
            scope.$innerModel = $parse(scope.ngModel)(scope.$parent);
            scope.optionValue = scope.optionValue||"option";
            // Montamos el options
            scope.options = "<option ng-repeat='option in $collection' value=\"{{"+scope.optionValue+"}}\">"+scope.optionLabel+"</option>";
            var selecHtml = $interpolate(template)(scope);

            
            // Montar la instrucción para el options
            element.html(selecHtml);
            $compile(element.contents())(scope);

            scope.$parent.$watch(scope.collection,function(n,o,e){
              scope.$collection = n;
            });
            scope.$watch("$innerModel", function(n,o,e){
              $parse(scope.ngModel+"="+JSON.stringify(n))(scope.$parent);
            });
            $timeout(function(){
              element.find(".select2").select2();
            },0);
            
            
        }
      }
    };

    GbnSelect2Options.$inject = ["$timeout", "$interpolate", "$compile"];
    function GbnSelect2Options($timeout, $interpolate, $compile){
      return {
        restrict: 'E',
        require: '^gbnSelect2',
        scope:{
          label: "@"
        },
        link: function(scope, element, attrs, controllerInstance) {

            controllerInstance.addOptions(element.html());
        }
      }
    };

})();