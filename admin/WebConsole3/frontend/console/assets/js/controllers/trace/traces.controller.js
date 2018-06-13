(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('TracesController', TracesController);

	TracesController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', '$q', '$transitions', '$interval', 'ogSweetAlert', 'toaster', 'TracesResource'];

	function TracesController($rootScope, $scope, $state, $timeout, $filter, $q, $transitions, $interval, ogSweetAlert, toaster, TracesResource) {
		var vm = this;
		vm.traces = [];
		vm.selection = [];
		vm.filters = {
			searchText: "",
			status: {
				"finished": {
					name: "finished", 
					selected: true
				},
				"execution": {
					name: "execution",
					selected: true
				}
			},
			finishedStatus: {
				"noErrors": {
					name: "no-errors",
					selected: true
				},
				"withErrors":{
					name: "with-errors",
					selected: true
				},
			},
			dateRange: {
				startDate: null,
				endDate: null
			}
		};

		vm.selectTrace = selectTrace;
		vm.selectAllTraces = selectAllTraces;
		vm.relaunchTraces = relaunchTraces;
		vm.deleteTraces = deleteTraces;
		vm.deleteExecutionTace = deleteExecutionTace;
		vm.relaunchExecutionTask = relaunchExecutionTask;
		vm.filterTraceStatus = filterTraceStatus;
		
		$transitions.onStart({to: "app.*"}, function(trans){
			if($rootScope.timers.executionsInterval.object == null){
              $rootScope.timers.executionsInterval.object = $interval(function() {
                getExectutionTasks();
              }, $rootScope.timers.executionsInterval.tick);
            }
        });

		
		init();

		function init(){
			vm.datePickerOptions = {
				    "locale": {
			        "format": "DD/MM/YYYY HH:mm",
			        "separator": " - ",
			        "applyLabel": $filter("translate")("apply"),
			        "cancelLabel": $filter("translate")('cancel'),
			        "fromLabel": $filter("translate")("from"),
			        "toLabel": $filter("translate")("to"),
			        "customRangeLabel": $filter("translate")('custom_range'),
			        "weekLabel": "W",
			        "daysOfWeek": [
			            $filter("translate")("sun"),
			            $filter("translate")("mon"),
			            $filter("translate")("tue"),
			            $filter("translate")("wed"),
			            $filter("translate")("thu"),
			            $filter("translate")("fri"),
			            $filter("translate")("sat")
			        ],
			        "monthNames": [
			            $filter("translate")("january"),
			            $filter("translate")("february"),
			            $filter("translate")("march"),
			            $filter("translate")("april"),
			            $filter("translate")("may"),
			            $filter("translate")("june"),
			            $filter("translate")("july"),
			            $filter("translate")("august"),
			            $filter("translate")("september"),
			            $filter("translate")("october"),
			            $filter("translate")("november"),
			            $filter("translate")("december")
			        ],
			        "firstDay": 1
			    },
		        timePicker: true,
		        timePickerIncrement: 30,
		        timePicker24Hour: true,
		        format: 'DD/MM/YYYY HH:mm'
		    };
			if($rootScope.timers.executionsInterval.object == null){
				getExectutionTasks();
				$rootScope.timers.executionsInterval.object = $interval(function() {
					getExectutionTasks();
				}, $rootScope.timers.executionsInterval.tick);
			}
			TracesResource.query().then(
				function(response){
					vm.traces = response;
				},
				function(error){
					toaster.pop({type: "error", title: "error",body: error});
				}
			)
		}

		function selectTrace(trace){
			var index = vm.selection.indexOf(trace);
			if(trace.selected == true && index == -1){
				vm.selection.push(trace);
			}
			else if(trace.selected == false && index != -1){
				vm.selection.splice(index,1);
			}
		}

		function selectAllTraces(){
			var filter = $filter("filter")(vm.traces, vm.searchText);
			for(var index = 0; index < filter.length; index++){
				filter[index].selected = vm.selectAll;
				selectTrace(filter[index]);
			}
		}

		function relaunchTraces(){

		}

		function deleteTraces(){
			ogSweetAlert.swal(
	  			{
				   title: $filter("translate")("sure_to_delete")+"?",
				   html: $filter("translate")("action_cannot_be_undone"),
				   type: "warning",
				   showCancelButton: true,
				   confirmButtonColor: "#DD6B55",
				   confirmButtonText: $filter("translate")("yes_delete")
				}, 
				function(response){
					if(response == true){
						var promises = [];
						for(var index = 0; index < vm.selection.length; index++){
							promises.push(TracesResource.delete({traceId: vm.selection[index].id}));
						}
						$q.all(promises).then(
							function(response){
								toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_deleted")});
								vm.selectAll = false;
								vm.selection = [];
								vm.searchText = "";
								init();
							},
							function(error){
								toaster.pop({type: "error", title: "error",body: error});
							}
						);
					}
				},
				function(cancel){
				}
			);
		}

		function getExectutionTasks(){
			TracesResource.query({finished: 0}).then(
				function(result){
					$rootScope.executionTasks = result;
				},
				function(error){

				}
			);
		}

		function deleteExecutionTace(task){
			ogSweetAlert.question(
				$filter("translate")("delete_task"), 
				$filter("translate")("sure_to_delete_task")+"?",
				function(result){
					if(result){
						TracesResource.delete({traceId: task.id}).then(
							function(response){
								toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_deleted")});
								getExectutionTasks();
							},
							function(error){
								toaster.pop({type: "error", title: "error",body: error});
							}
						);

					}
				}
			);

		}

		function relaunchExecutionTask(task){
			ogSweetAlert.question(
				$filter("translate")("relaunch_task"), 
				$filter("translate")("sure_to_relaunch_task")+"?",
				function(result){
					if(result){

					}
				}
			);
		}

		function filterTraceStatus(trace, index, array){

			// Comprobar si para el filtro de estado actual de la traza
			var result = (trace.finishedAt != null && vm.filters.status["finished"].selected == true)||(trace.finishedAt == null && vm.filters.status["execution"].selected == true);
			result = result && (trace.finishedAt != null && (trace.status == 0 && vm.filters.finishedStatus["noErrors"].selected == true)||(trace.status != 0 && vm.filters.finishedStatus["withErrors"].selected == true));
			if(vm.filters.dateRange.startDate != null){
				result = result && moment(trace.executedAt).isAfter(vm.filters.dateRange.startDate);
			}
			if(vm.filters.dateRange.endDate != null){
				result = result && moment(trace.executedAt).isBefore(vm.filters.dateRange.endDate);
			}

			return result;
		}

	  };
})();