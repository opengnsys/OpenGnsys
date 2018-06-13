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
      .filter('decodeHtml', decodeHtml);

      	decodeHtml.$inject = ["$filter"];

		// encode(decode) html text into html entity
		var decodeHtmlEntity = function(str) {
			return str.replace(/&#(\d+);/g, function(match, dec) {
				return String.fromCharCode(dec);
			});
		};

		var encodeHtmlEntity = function(str) {
			var buf = [];
			for (var i=str.length-1;i>=0;i--) {
				buf.unshift(['&#', str[i].charCodeAt(), ';'].join(''));
			}
			return buf.join('');
		};


      function decodeHtml($filter) {
        return function(input){
          return decodeHtmlEntity(input);
        }

      };

})();
 