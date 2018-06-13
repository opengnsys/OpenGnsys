(function(){
    'use strict';
    /**
     * @ngdoc function
     * @name ogWebConsole.controller:MainCtrl
     * @description
     * # MainCtrl
     * Controller of the ogWebConsole
     */
    angular.module(appName).controller('DashboardController', DashboardController);

    DashboardController.$inject=['$rootScope', '$state', '$transitions','$filter', '$interval', 'ServerStatusResource'];

    function DashboardController($rootScope,$state, $transitions, $filter, $interval, ServerStatusResource) {
        var vm = this;
        $rootScope.timers.serverStatusInterval.object = null;
        var maxLength = 50;
        var currentPos = 0;
        vm.info = null;
        vm.diskIndex = 0;
        vm.changeDiskIndex = changeDiskIndex;
        vm.status = {
          data: [
            {
              label: $filter("translate")("memory"),
              data: [],
              color: "#3c8dbc"
            },
            {
              label: $filter("translate")("cpu"),
              data: [],
              color: "#00FF00"
            }
          ],
          options: {
            grid: {
              borderColor: "#f3f3f3",
              borderWidth: 1,
              tickColor: "#f3f3f3"
            },
            series: {
              shadowSize: 0, // Drawing is faster without shadows
              color: "#3c8dbc"
            },
            lines: {
              fill: true, //Converts the line chart to area chart
              color: "#3c8dbc"
            },
            yaxis: {
              min: 0,
              max: 100,
              show: true
            },
            xaxis: {
              show: true
            }
          }
        };

        $transitions.onStart({to: "app.*"}, function(trans){
            if(trans.to().name === "app.dashboard"){
              $rootScope.timers.serverStatusInterval.object = $interval(function() {
                updateStatus();
              }, $rootScope.timers.serverStatusInterval.tick);
            }
            else{
              $interval.cancel($rootScope.timers.serverStatusInterval.object);
            }
        });

          // La primera vez que entra en dashboard
        if($rootScope.timers.serverStatusInterval.object == null){
          updateStatus();
          $rootScope.timers.serverStatusInterval.object = $interval(function() {
              updateStatus();
          }, $rootScope.timers.serverStatusInterval.tick);
        }
        


        function updateStatus(){
          ServerStatusResource.get().then(
              function(response){
                response.ogServices = response.ogServices||[];
                for(var index = 0; index < response.ogServices.length; index++){
                  response.ogServices[index].etime = response.ogServices[index].etime.replace("-", " d, ");
                }

                // Pasar de bytes a KB, MB o GB dependiendo del caso
                response.network.inBytes = $rootScope.OGCommonService.getUnits(response.network.inBytes);
                response.network.outBytes = $rootScope.OGCommonService.getUnits(response.network.outBytes);

                vm.info = {
                  cpu: response.cpu,
                  ram:{ 
                    total: Math.round((response.memInfo.total/(1024*1024))),
                    used: Math.round((response.memInfo.used/(1024*1024))*100)/100,
                    units: "GB"
                  },
                  disk: response.disk,
                  network: response.network,
                  ogServices: response.ogServices

                }
                // Calcular porcentaje de memoria
                var mem = Math.round(((response.memInfo.used*100)/response.memInfo.total)*100)/100
                var index = 0;
                if(vm.status.data[0].data.length > 0){
                  index = vm.status.data[0].data[vm.status.data[0].data.length-1][0]+1;
                };
                vm.status.data[0].data.push([index,mem]);
                vm.status.data[1].data.push([index,response.cpu.usage]);
                if(vm.status.data[0].data.length > maxLength){
                  vm.status.data[0].data.shift();
                  vm.status.data[1].data.shift();
                }
              },
              function(error){
                alert(error);
              }
          );
        }

        function changeDiskIndex(){
          vm.diskIndex = (vm.diskIndex+1)%vm.info.disk.length;
        }
    }
       
})();
