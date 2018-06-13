(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('RepositoriesController', RepositoriesController);

	RepositoriesController.$inject = ['$rootScope', '$scope', '$filter', 'toaster', 'ogSweetAlert','RepositoriesResource'];
	
	function RepositoriesController($rootScope, $scope, $filter, toaster, ogSweetAlert,RepositoriesResource) {
		var vm = this;
		vm.repositories = [];
		vm.formOptions = {};
		
		vm.newRepository = newRepository;
		vm.loadFormOptions = loadFormOptions;
		vm.loadRepositories = loadRepositories;
		vm.saveRepository = saveRepository;
		vm.deleteRepository = deleteRepository;
		vm.refreshRepoInfo = refreshRepoInfo;
		vm.isImageFile = isImageFile;
		vm.deleteImageFile = deleteImageFile;

		vm.loadRepositories();
		vm.loadFormOptions();

		function loadRepositories(){

			RepositoriesResource.query({ouid: $rootScope.user.ou.id}).then(
				function(result){
					angular.forEach(result, function(value) {
						value.port = parseInt(value.port);
						// Actualizamos información si el repositorio tiene api key
						if(value.apikey && value.apikey.length > 0){
							vm.refreshRepoInfo(value);
						}
					});
					vm.repositories = result;
					// Se asigna al rootScope
					$rootScope.repositories = result;

				},
				function(error){
					alert(error);
				}
			);
		};

		function loadFormOptions(){
			RepositoriesResource.options().then(
				function(result){
					vm.formOptions = result;
				}
			);
		};


		function newRepository(){
			vm.repositories.push({});
		}

		function saveRepository(Form, repository){
			var result = $rootScope.ValidateFormsService.validateForm(Form);

	        // Si no hubo ningun error se guardan todas las pgms
	        if(result == true){
				repository.$$loading = true;
				// Con angular.toJson se eliminan los atributos que empiecen por $$
				var method;
				if(!repository.id){
					method = RepositoriesResource.save(JSON.parse(angular.toJson(repository)));
				}
				else{
					var obj = JSON.parse(angular.toJson(repository));
					delete obj.id;
					method = RepositoriesResource.update({repoId: repository.id}, obj);
				}
				method.then(
					function(response){
						repository.$$loading = false;
						repository.id = response.id;
						toaster.pop({type: "success", title: "success",body: "Successfully saved"});
					},
					function(error){
						repository.$$loading = false;
						toaster.pop({type: "error", title: "error",body: error});
					}
				);
			}
		};

		function deleteRepository(repository){
			ogSweetAlert.question($filter("translate")("opengnsys_question"),$filter("translate")("sure_to_delete")+"?",function(yes){
				if(repository.id){
					RepositoriesResource.delete({repoId: repository.id}).then(
						function(response){
				  			removeRepositoryFromArray(repository);
				  		},
				  		function(error){
				  			toaster.pop({type: "error", title: "error",body: error});
					  	}
					)
				}
				else{
					removeRepositoryFromArray(repository);
				}
			});
		}

		function removeRepositoryFromArray(repository){
			var index = vm.repositories.indexOf(repository);
  			if(index != -1){
  				vm.repositories.splice(index, 1);
  			}
  			toaster.pop({type: "success", title: "success",body: "Successfully deleted"});
  			$scope.$apply();
		}

		function refreshRepoInfo(repository){
			repository.$$loading = true;
			RepositoriesResource.getInfo(repository).then(
				function(response){
					repository.$$loading = false;
					repository.info = response;
					var fileGroups = {};
					// Agrupamos los ficheros de imágenes según su nombre
					angular.forEach(repository.info.files, function(file, key){
						var basename = file.name.split(".")[0];
						// Es una imagen de backup
						if(file.name.match(/\.ant$/)){
							basename += ".ant";
						}
						
						if(!fileGroups[basename]){
							fileGroups[basename] = [];
						}
						fileGroups[basename].push(file);
						
					});

					repository.info.files = fileGroups;
				},
				function(error){
					repository.$$loading = false;
				}
			);
		};

		function isImageFile(file){
			return !(file.name.match(/(\.img$)|(\.img.ant$)/) === null);
		}

		function deleteImageFile(file){
			ogSweetAlert.question(
				$filter("translate")("opengnsys_question"),
				$filter("translate")("sure_to_delete")+"?",
				function(yes){
					// TODO - borrar el fichero físico y también la imagen asociada
				}
			);
		}
	}
})();