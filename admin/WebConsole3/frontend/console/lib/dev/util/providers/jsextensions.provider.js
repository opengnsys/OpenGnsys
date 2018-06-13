(function(){
	'use strict';
	angular.module("globunet.utils")
	.service("JsExtensions", JsExtensions)

	JsExtensions.$inject = ["$parse"];
	function JsExtensions($parse){
		var parse = $parse;
		
		
		String.prototype.capitalizeFirstLetter = function() {
	        return this.charAt(0).toUpperCase() + this.slice(1);
	    };

	    Array.prototype.inArray = function (value)
	    {
	      // Returns true if the passed value is found in the array.
	      // Returns false if it is not.
	      var i;
	      for (i = 0; i < this.length; i++){
	        if (this[i] == value){
	          return true;
	        }
	      }
	      return false;
	    };

	    // Busca un objeto en el array por la clave "key" en el objeto comparandola con la clave
	    Array.prototype.indexOfByKey = function (object, objKey, myKey)
	    {
	    	myKey = myKey||objKey;
	    	var result = 0;
	    	var found = false;
	    	var index = 0;
	    	while(!found && index < this.length){
	    		if(parse(myKey)(this[index]) == parse(objKey)(object)){
	    			found = true;
	    		}
	    		index++;
	    	}
	    	result = (found)?index-1:-1;
	    	return result;
	    };
	};
})();
