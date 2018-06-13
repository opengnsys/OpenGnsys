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
      .filter('ogCommands', ogCommandsFilter);

      ogCommandsFilter.$inject = [];


      function ogCommandsFilter() {
        var ogCommands = [
          "ogEcho",
          "ogGetCacheSize",
          "ogCreatePartitionTable",
          "ogUnmountAll",
          "ogUnmountCache",
          "ogUnmount",
          "ogFormatCache",
          "ogFormat",
          "ogDeletePartitionTable",
          "ogExecAndLog",
          "ogUpdatePartitionTable",
          "initCache",
          "ogListPartitions",
          "ogCreatePartitions",
          "ogSetPartitionActive",
          "deployImage",
          "updateCache"
        ];

        return function(input){
          var out = input;
          if (typeof out == "string"){
            // Sustituimos en el input cualquier comando de ogCommands por su versión con etiquetas html span class="og-command"
            for (var i = 0; i < ogCommands.length; i++) {
                out = out.replace(new RegExp("\\b"+ogCommands[i], "g"),"<span class='og-command'>"+ogCommands[i]+"</span>");
            }
            out = out.replace(new RegExp('\n',"g"), "<br>");
            // Todo lo que esté entre comillas dobles se considera string y se muestra entre span class="og-string"
            out = out.replace(new RegExp('"(.*?)"',"g"),"<span class='og-string'>\"$1\"</span>");
          }

          return out;
        }

      };

})();
 