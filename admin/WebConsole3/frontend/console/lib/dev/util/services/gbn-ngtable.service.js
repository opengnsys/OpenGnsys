(function(){
	'use strict';
	angular.module("globunet.utils")
	.provider("GbnNgTableServiceConfig", GbnNgTableServiceConfig)
	.service("GbnNgTableService", function(GbnNgTableServiceConfig, $filter, $templateCache, ngTableParams, toaster, SweetAlert){});


	function GbnNgTableServiceConfig(){
		this.tableOptions = ["view", "edit", "delete"];
		this.customErrorDialogClass = null;
		
		this.getViewTemplate = function(arrayName){
			var object = arrayName.substring(0, arrayName.length-1);
			var route = "app."+arrayName+".view({"+object+"Id: "+object+".id})";
			return "<a class='btn btn-default btn-o btn-xs tooltips btn-opt-view margin-right-5' tooltip-placement='top' tooltip='View' ui-sref=\""+route+"\"><i class='icon-eye'></i>View</a>";
		}

		this.getEditTemplate = function(arrayName){
			var object = arrayName.substring(0, arrayName.length-1);
			var route = "app."+arrayName+".view.edit({"+object+"Id: "+object+".id})";
			return "<a class='btn btn-default btn-o btn-xs tooltips btn-opt-edit margin-right-5' tooltip-placement='top' tooltip='Edit' ui-sref=\""+route+"\"><i class='icon-paper'></i>Edit</a>";
		}

		var getDeleteFunctionName = function(arrayName){
			var object = arrayName.substring(0, arrayName.length-1);
			var funcName = "delete"+object.capitalizeFirstLetter();
			return funcName;
		}

		this.getDeleteFunctionName = getDeleteFunctionName;


		this.getDeleteTemplate = function(arrayName){
			var self = this;
			var object = arrayName.substring(0, arrayName.length-1);
			var delFunction = getDeleteFunctionName(arrayName)+"("+object+".id)";
			return "<a class='btn btn-default btn-o btn-xs tooltips btn-opt-delete' tooltip-placement='top' tooltip='Delete' ng-click=\""+delFunction+"\">\
			<i class='icon-cross'></i>\
			Delete</a>";
		}

		this.setOptions = function(options){
			var self = this;
			self.tableOptions = options;
		}

		this.setCustomErrorDialogClass = function(customErrorDialogClass){
			var self = this;
			self.customErrorDialogClass = customErrorDialogClass;
		}

		this.getCustomErrorDialogClass = function(){
			var self = this;
			return self.customErrorDialogClass;
		}

		this.addOptions = function(options){
			var self = this;
			if(Array.isArray(options)){
				self.tableOptions = self.tableOptions.concat(options);
			}
			else{
				self.tableOptions.push(options);
			}
		}

		this.setOptionsTemplate = function(option, optionTemplate){
			var self = this;
			if(typeof optionTemplate === "function"){
				self["get"+option.capitalizeFirstLetter()+"Template"] = optionTemplate;
			}
			else{
				self["get"+option.capitalizeFirstLetter()+"Template"] = function(){
					return optionTemplate;
				}
			}
		}

		this.$get = function(){
			return this;
		}

	}
})();
