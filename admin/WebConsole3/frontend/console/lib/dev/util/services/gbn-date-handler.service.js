(function(){
	'use strict';
	angular.module("globunet.utils")
	.provider("gbnDateHandlerConfig", gbnDateHandlerConfig)
	.service('gbnDateHandler',gbnDateHandler);

	gbnDateHandlerConfig.$inject = [];
	gbnDateHandler.$inject = ["$filter", 'gbnDateHandlerConfig'];


	function gbnDateHandlerConfig(){
		var self = this;
		self.options = {
			isoFormat: "yyyy-MM-dd",
			format: "dd-MM-yyyy",
			formatLong: "dd-MM-yyyy HH:mm"
		}
		

		self.setOptions = setOptions;

		function setOptions(options){
			self.options = angular.extend(self.options, options);
		}

		this.$get = function(){
			return self;
		}

	}


	function gbnDateHandler($filter , gbnDateHandlerConfig){
		var self = this;
		this.isoFormat = gbnDateHandlerConfig.options.isoFormat;
		this.format = gbnDateHandlerConfig.options.format;
		this.formatLong = gbnDateHandlerConfig.options.formatLong;
		this.status = {};

		// Devuelve un string de fecha con el formato iso
		this.getDate = function(date, format){
			var result = "1970-01-01";
			if(format && moment(date,format).isValid()){
				result = moment(date,format.toUpperCase());
			}
			else if(moment(date).isValid()){
				result = moment(date).format(self.isoFormat.toUpperCase());
			}
			return result;
		};

		this.transformToDateFormat = function(object, property, format){
			format = format || self.format;
			object[property] = $filter("date")(new Date(object[property]), format);
			return object[property];
		}

		this.getStatus = function(datepickerName){
			if(!self.status[datepickerName]){
				self.status[datepickerName] = {open: false}
			}
			return self.status[datepickerName];
		};

		this.open = function($event, datepickerName){
			if(!self.status[datepickerName]){
				self.status[datepickerName] = {open: true}
			}
			self.status[datepickerName].open = true;
		};

		return this;
	};
})();