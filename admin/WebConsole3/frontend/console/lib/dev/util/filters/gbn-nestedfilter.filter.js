(function(){
	'use strict';

	/**
	 * Globunet
	 * Extension del filter de angular para permitir propiedades anidadas que vengan en forma de string
	 */
	angular.module("globunet.utils").filter('gbnNestedFilter', gbnNestedFilter);

	gbnNestedFilter.$inject = ["$filter"];

	function gbnNestedFilter($filter) {
		var self = this;

		/**
		 * El string debe ser del tipo "objeto : valor"
		 * si hay nivel de anidamiento se marca con "."
		 * Ej: category: "nombre"
		 *     category.name : "nombre"
		 */
		this.createObjectFromString = function(string, object){
			// buscamos el indice del primer "."
			var index = string.indexOf(".");
			// Comprobamos que el "." no esté en el valor del objeto
			var dpIndex = string.indexOf(":");
			if(!object){
				object = {};
			}
			if(index === -1 || index > dpIndex){
				object[string.split(":")[0]] = string.split(":")[1]?string.split(":")[1]:"";
			}
			else{
				// Creamos el objeto principal y llamamos recursivamente al siguiente string
				object[string.substring(0,index)] = {};
				var nextString = string.substring(index+1, string.length);
				self.createObjectFromString(nextString, object[string.substring(0,index)]);
			}

			return object;
		}

		this.isMatch = function(item, prop, value){
			var result = false;
			if(typeof value === "object"){
				var keys = Object.keys(value);
	            for (var i = 0; i < keys.length; i++) {
					result = result || self.isMatch(item[prop],keys[i], value[keys[i]]);
				}
			}
			else{
				var text = value.toLowerCase();
                result = (item[prop].toString().toLowerCase().indexOf(text) !== -1);
			}
			return result;
		}

	    return function (items, props) {
	    	var out = [];
	    	var newProps = {};
	    	if(typeof props !== "object"){
	    		out = $filter("filter")(items,props);
	    	}
	    	else{
		    	// recorremos las propiedades pasadas y comprobamos si alguna contiene un "."
		    	for(var prop in props){
		    		if(prop.indexOf(".") !== -1){
			    		var string = prop + ":" + props[prop];
			    		self.createObjectFromString(string, newProps);
			    	}
			    	else{
			    		newProps[prop] = props[prop];
			    	}
		    	}
		    	props = newProps;
		    	out = $filter("filter")(items,props);
		    }

	        return out;
	    };
	};
})();