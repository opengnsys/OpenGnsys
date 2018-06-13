(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('ImagesController', ImagesController);

	ImagesController.$inject = ['$rootScope', '$scope','$filter', 'toaster', 'ImagesResource','ogSweetAlert'];
	
	function ImagesController($rootScope, $scope, $filter, toaster, ImagesResource, ogSweetAlert) {
		var vm = this;
		vm.images = [];
		vm.ngTableSearch = {
			text: ""
		};


		vm.getImageFileSystem = getImageFileSystem;
		vm.getPartitionType = getPartitionType;
		vm.deleteImage = deleteImage;

		init();


		function init(){
			loadImages();
		}

		function loadImages(){

			ImagesResource.query().then(
				function(result){
					vm.images = result;

				},
				function(error){
					alert(error);
				}
			);
		};

		function getImageFileSystem(image){
			var result = "";
			if(typeof image.partitionInfo === "string"){
				image.partitionInfo = JSON.parse(image.partitionInfo);
			}
			else if(!image.partitionInfo){
				image.partitionInfo = {};
			}
			return image.partitionInfo.filesystem;
		}


		function getPartitionType(partition){
			// buscar la particion en el array global
			var result = $rootScope.partitionTypes.filter(function(obj){ return obj.id == partition.id});
			result = result[0];
			return result.type;
		}

		function deleteImage(image){
			var options = {
				scope: {
					removeFile: false
				}
			};
			ogSweetAlert.swal({
			   title: $filter("translate")("sure_to_delete")+"?",
			   html: '<form style="text-align: center; padding-left: 10px">\
			   			<div class="form-group" translate="action_cannot_be_undone"></div>\
					   	<div class="form-group">\
	                    	<div class="checkbox clip-check check-primary checkbox-inline">\
	                      		<input id="removeFile" icheck checkbox-class="icheckbox_square-blue" radio-class="iradio_square-blue" type="checkbox" class="selection-checkbox" ng-model="removeFile" />\
	                      	</div>\
	                      	<label for="removeFile" translate="remove_file">\
	                    	</label>?\
	                  	</div>\
                  	</form>',
			   type: "warning",
			   showCancelButton: true,
			   confirmButtonColor: "#3c8dbc",
			   confirmButtonText: $filter("translate")("yes_delete"),
			   closeOnConfirm: true
			}, 
			function(result, $scope){
				if(result == true){
					if($scope.removeFile == true){
						// TODO Borrar fichero físico...
					}
				   ImagesResource.delete({imageId: image.id}).then(
				   	function(response){
				   		toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_deleted")});
				   		// Buscar el elemento en el array y borrarlo
				   		var index = vm.images.indexOf(image);
				   		if(index !== -1){
				   			vm.images.splice(index, 1);
				   		}
				   	},
				   	function(error){
				   		toaster.pop({type: "error", title: "error",body: error});
				   	}
				   );
				}
			},
			null,
			options);
		}

	}
})();