(function(){
	'use strict';

	/**
	 * Globunet
	 * Filtro para poner en mayuscula la primera letra de un string
	 */
	angular.module("globunet.utils").filter('gbnCapitalize', gbnCapitalize);

	gbnCapitalize.$inject = [];

	function gbnCapitalize() {
		var self = this;

		
	    return function (input) {
	    	var output = "input_not_string";
	    	if(typeof input === "string"){
		        output = input.charAt(0).toUpperCase() + input.slice(1);
		    }
		    return output;
	    };
	};
})();