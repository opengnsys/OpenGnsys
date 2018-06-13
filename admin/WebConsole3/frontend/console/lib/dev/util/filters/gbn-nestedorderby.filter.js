(function(){
    'use strict';
    angular
      .module('globunet.utils')
      .filter('gbnNestedOrderBy', gbnNestedOrderBy);

      gbnNestedOrderBy.$inject = ["$filter"];

      function gbnNestedOrderBy($filter) {
        return function (items, field, reverse) {
         
          var filtered = [];
          if(!containsNestedProperties(field)){
            filtered = $filter("orderBy")(items, field, reverse);
          }
          else{
            if(field){
              parseField();

              angular.forEach(items, function(item, key) {
                item.key = key;
                filtered.push(item);
              });

              // Si field es un array de campos, ordenamos por cada uno de ellos
              filtered.sort(sorting);

              if (reverse) {
                filtered.reverse();
              }
            }
            else{
              filtered = items;
            }
          }

          return filtered;

          //////////////////////////////

          function containsNestedProperties(prop){
            var result = false;
            if(typeof prop === "string" && prop.indexOf(".") !== -1){
              result = true;
            }
            else if(typeof prop === "object"){
              for(var key in prop){
                result = containsNestedProperties(prop[key])||result;
              }
            }
            return result;
          }

          /*
           * Procesa el campo buscado por si contiene + o - indicar el reverse
           * Elimina el + o el - del string
           */
          function parseField(){
             // Comparaciones multiples
            if(!Array.isArray(field)){
              field = [field];
            }
            for(var _index = 0; _index < field.length; _index++){
              // si contiene el +, reverse será false
              if(field[_index].indexOf("+") !== -1){
                field[_index] = field[_index].replace("+","");
                reverse = false;
              }
              else if(field[_index].indexOf("-") !== -1){
                field[_index] = field[_index].replace("-","");
                reverse = true; 
              }
            }

          }

          function isNumeric(n) {
            return !isNaN(parseFloat(n)) && isFinite(n);
          }

          function index(obj, i) {
            return obj[i];
          }

          function sorting(a, b) {
            var comparator = 0;
            var _index = 0;
           

            while(comparator == 0 && _index < field.length){
              var currentField = field[_index];
              var reducedA = currentField.split('.').reduce(index, a);
              var reducedB = currentField.split('.').reduce(index, b);

              if (isNumeric(reducedA) && isNumeric(reducedB)) {
                reducedA = Number(reducedA);
                reducedB = Number(reducedB);
              }

              if (reducedA === reducedB) {
                comparator = 0;
              } else {
                comparator = reducedA > reducedB ? 1 : -1;
              }
              _index++;
            }
            

            return comparator;
          }
        };
      };
})();

