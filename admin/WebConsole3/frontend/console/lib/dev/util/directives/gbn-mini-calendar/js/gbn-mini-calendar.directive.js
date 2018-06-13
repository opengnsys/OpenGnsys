(function(){
  'use strict';
  /**
   * @ngdoc directive
   * @name gbnMiniCalendar
   * @module globunet.utils
   * @restrict E
   *
   * @description
   * Esta directiva genera un calendario minimo con posibilidad de configurar los días individualmente.
   * @param search-model {string} variable del scope que actua como campo de búsqueda
   * @param filter-cols {array} Columnas a filtrar mediante search-model
   * @param array {string} nombre del array del que se obtendrán los datos (debe estar en el scope)
   *
   */
  angular.module("globunet.utils")
    .provider("gbnMiniCalendarConfig", GbnMiniCalendarConfig)
    .directive("gbnMiniCalendar", GbnMiniCalendarfunction);

    GbnMiniCalendarfunction.$inject = ["$parse", "$templateCache", "gbnMiniCalendarConfig"];
    
    function GbnMiniCalendarConfig(){
      var self = this;


      self.miniCalendarConfig = {
        leftArrow: true,
        rightArrow: true,
        weekDays: getWeekDays("en"),
        disabledDays: [0,6], // deshabilita todos los domingos y sabados
        locale: "en",
        moment: moment()
      };

      self.setOptions = setOptions;
      self.init = init;

      function getWeekDays(locale){
        var weekDays = [];
        for(var index = 0; index < 7; index++){
          weekDays.push(moment().locale(locale).weekday(index).format("ddd"));
        }
        return weekDays;
      }

      function setOptions(options){
        angular.extend(self.miniCalendarConfig, options);
        init(self.miniCalendarConfig);
       
      }

      function init(miniCalendarConfig){
         // Set locale
        miniCalendarConfig.moment.locale(miniCalendarConfig.locale);
        if(miniCalendarConfig.month){
          miniCalendarConfig.month.locale(miniCalendarConfig.locale);
        }
        // set weekdays names
        miniCalendarConfig.weekDays = getWeekDays(miniCalendarConfig.locale);
      }

    

      this.$get = function(){
        return self;
      }
    }


    function GbnMiniCalendarfunction($parse, $templateCache, gbnMiniCalendarConfig) {

          $templateCache.put('gbn-mini-calendar.html', '<div class="header">\
              <i class="fa fa-angle-left" ng-click="previous()" ng-if="miniCalendarConfig.leftArrow"></i>\
              <span>{{month.format("MMMM, YYYY")}}</span>\
              <i class="fa fa-angle-right" ng-click="next()" ng-if="miniCalendarConfig.rightArrow"></i>\
          </div>\
          <div class="week names">\
              <span class="day" ng-repeat="weekDay in miniCalendarConfig.weekDays">\
                  {{weekDay}}\
              </span>\
          </div>\
          <div class="week" ng-repeat="week in weeks">\
              <span class="day" ng-class="{ today: day.isToday, \'different-month\': !day.isCurrentMonth, \'selected\': day.selected, \'disabled\': day.disabled }" ng-click="select(day)" ng-repeat="day in week.days">\
                  {{day.number}}\
              </span>\
          </div>');


          return {
              restrict: "E",
              templateUrl: "gbn-mini-calendar.html",
              scope: {
                selectHandler: "&",
                calendarHandler: "&",
                options: "=options",
                selected: "=",
                initialSelected: "="
              },
              link: function(scope, element, attrs) {
                  var miniCalendarConfig = angular.extend({},gbnMiniCalendarConfig.miniCalendarConfig);
                  // Sobreescribimos si nos llega una configuracion externa
                  if(scope.options){
                    angular.extend(miniCalendarConfig, scope.options);
                    // reordenar los dias de la semana si se cambió "firstDay"
                    gbnMiniCalendarConfig.init(miniCalendarConfig);
                  }
                  scope.miniCalendarConfig = miniCalendarConfig;


                  scope.selected = scope.selected;
                  scope.month = (scope.selected && scope.selected.date)?scope.selected.date.clone():miniCalendarConfig.moment.clone();
                  var start = (scope.selected && scope.selected.date)?scope.selected.date.clone():miniCalendarConfig.moment.clone();

                  if(miniCalendarConfig.month){
                    scope.month = miniCalendarConfig.month;
                    start = scope.month.clone();
                  }
               

                  start.date(1);
                  _removeTime(start.weekday(0));
                  _buildMonth(scope, start, scope.month);

                  if(scope.calendarHandler){
                    angular.extend(scope.calendarHandler(), {
                      next: next,
                      previous: previous
                    });
                  }

                  scope.select = select;
                  scope.next = next;
                  scope.previous = previous;

                  if(scope.selected)
                    select(scope.selected);

                  function select(day) {
                      if(!day.disabled){
                        if(scope.selected){
                          scope.selected.selected = false;
                        }
                        scope.selected = day;
                        day.selected = true;
                        if(scope.selectHandler){
                          var expressionHandler = scope.selectHandler();
                          expressionHandler(day);
                        }
                      }
                  };
                  
                  function next() {
                      // Cambiamos un mes mas
                      scope.month = scope.month.clone().add(1,"M");
                      var next = scope.month.clone();
                      _removeTime(next.weekday(0));
                      _buildMonth(scope, next, scope.month);
                  };

                  
                  function previous() {
                      // Cambiamos un mes menos
                      scope.month = scope.month.clone().subtract(1,"M");
                      var previous = scope.month.clone();
                      _removeTime(previous.weekday(0));
                      _buildMonth(scope, previous, scope.month);
                  };

                  function _removeTime(date) {
                      return date.hour(0).minute(0).second(0).millisecond(0);
                  }

                  function _buildMonth(scope, start, month) {
                      scope.weeks = [];
                      var done = false, date = start.clone(), monthIndex = date.month(), count = 0;
                      while (!done) {
                          scope.weeks.push({ days: _buildWeek(scope, date.clone(), month) });
                          date.add(1, "w");
                          //done = count++ > 2 && monthIndex !== date.month();
                          done = count++ > 4;
                          monthIndex = date.month();
                      }
                  }

                  function _buildWeek(scope, date, month) {
                      var days = [];
                      for (var i = 0; i < 7; i++) {
                        // Comprobar que dia de la semana es
                        var disabled = (miniCalendarConfig.disabledDays && miniCalendarConfig.disabledDays.indexOf(date.day()) !== -1);
                        var day = {
                            name: date.format("dd").substring(0, 1),
                            number: date.date(),
                            isCurrentMonth: date.month() === month.month(),
                            isToday: date.isSame(new Date(), "day"),
                            date: date,
                            disabled: disabled,
                            selected: false
                        };

                        var comparator = (scope.selected)?scope.selected.date:scope.initialSelected;

                        if(comparator && comparator.isSame(day.date,'date')){
                          day.selected = true;
                          scope.selected = day;
                        }
                        days.push(day);
                        date = date.clone();
                        date.add(1, "d");
                      }
                      return days;
                  }
              }
          };
      };
        

})();