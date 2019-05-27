import {Component, OnDestroy, OnInit} from '@angular/core';

import { TraceService } from 'src/app/api/trace.service';
import { Trace } from 'src/app/model/trace';
import {ToasterService} from '../../service/toaster.service';
import {OgSweetAlertService} from '../../service/og-sweet-alert.service';
import {Router} from '@angular/router';
import {OgCommonService} from '../../service/og-common.service';
import {environment} from '../../../environments/environment';
import {TranslateService} from '@ngx-translate/core';
import {QueryOptions} from 'globunet-angular/core/providers/api/query-options';
import {forkJoin} from 'rxjs';

import * as moment from 'moment';

@Component({
  selector: 'app-trace',
  templateUrl: './trace.component.html',
  styleUrls: [ './trace.component.scss' ]
})
export class TraceComponent implements OnInit, OnDestroy {
  public traces = [];
  public selection = [];
  public searchText = '';
  public filters = {
    searchText: '',
    status: {
      'finished': {
        name: 'finished',
        selected: true
      },
      'execution': {
        name: 'execution',
        selected: true
      }
    },
    finishedStatus: {
      'noErrors': {
        name: 'no-errors',
        selected: true
      },
      'withErrors': {
        name: 'with-errors',
        selected: true
      },
    },
    dateRange: {
      startDate: moment(),
      endDate: moment()
    }
  };
  config: { constants: any; timers: any; };

  private datePickerOptions: { timePickerIncrement: number; timePicker: boolean; format: string; timePicker24Hour: boolean; locale: { fromLabel: any; toLabel: any; cancelLabel: any; firstDay: number; applyLabel: any; format: string; daysOfWeek: any[]; separator: string; customRangeLabel: any; weekLabel: string; monthNames: any[] } };
  private selectAll: any;
  private executionTasks: Trace[];
  public showInfo: string;
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public traceService: TraceService,
              private ogCommonService: OgCommonService,
              private router: Router,
              private ogSweetAlert: OgSweetAlertService,
              private toaster: ToasterService,
              private translate: TranslateService) {
    this.ogCommonService.showLoader = false;
  }

  ngOnDestroy() {
    if (this.config.timers && this.config.timers.executionsInterval) {
      this.config.timers.executionsInterval.object = null;
    }
    this.ogCommonService.showLoader = true;
  }

ngOnInit(): void {
  const self = this;
  this.ogCommonService.loadEngineConfig().subscribe(
    data => {
      this.config = data;

      if (this.config.timers.executionsInterval.object === null && this.config.timers.executionsInterval.tick > 0) {
        this.config.timers.serverStatusInterval.object = 1;
      }

      this.datePickerOptions = {
        'locale': {
          'format': 'DD/MM/YYYY HH:mm',
          'separator': ' - ',
          'applyLabel': this.translate.instant('apply'),
          'cancelLabel': this.translate.instant('cancel'),
          'fromLabel': this.translate.instant('from'),
          'toLabel': this.translate.instant('to'),
          'customRangeLabel': this.translate.instant('custom_range'),
          'weekLabel': 'W',
          'daysOfWeek': [
            this.translate.instant('sun'),
            this.translate.instant('mon'),
            this.translate.instant('tue'),
            this.translate.instant('wed'),
            this.translate.instant('thu'),
            this.translate.instant('fri'),
            this.translate.instant('sat')
          ],
          'monthNames': [
            this.translate.instant('january'),
            this.translate.instant('february'),
            this.translate.instant('march'),
            this.translate.instant('april'),
            this.translate.instant('may'),
            this.translate.instant('june'),
            this.translate.instant('july'),
            this.translate.instant('august'),
            this.translate.instant('september'),
            this.translate.instant('october'),
            this.translate.instant('november'),
            this.translate.instant('december')
          ],
          'firstDay': 1
        },
        timePicker: true,
        timePickerIncrement: 30,
        timePicker24Hour: true,
        format: 'DD/MM/YYYY HH:mm'
      };
      this.updateTraces();
    }
  );
}


  selectTrace(trace) {
    const index = this.selection.indexOf(trace);
    if (trace.selected === true && index === -1) {
      this.selection.push(trace);
    } else if (trace.selected === false && index !== -1) {
      this.selection.splice(index, 1);
    }
  }

  selectAllTraces() {
    const filter = this.traces.filter(function(trace: Trace) {
      return true;
    });
    for (let index = 0; index < filter.length; index++) {
      filter[index].selected = this.selectAll;
      this.selectTrace(filter[index]);
    }
  }

  relaunchTraces() {

  }

  deleteTraces() {
    const self = this;
    this.ogSweetAlert.question( this.translate.instant('sure_to_delete') + '?', this.translate.instant('action_cannot_be_undone'), function(response) {
      const promises = [];
      for (let index = 0; index < self.selection.length; index++) {
        promises.push(self.traceService.delete(self.selection[index].id));
      }
      forkJoin(promises).subscribe(
        (success) => {
          self.toaster.pop({type: 'success', title: 'success', body: self.translate.instant('successfully_deleted')});
          self.selectAll = false;
          self.selection = [];
          self.searchText = '';
          self.updateTraces();
        },
        (error) => {
          self.toaster.pop({type: 'error', title: 'error', body: error});
        }
      );
    });
  }

  updateTraces() {
    this.traceService.list(new QueryOptions({fromDate: moment(this.filters.dateRange.startDate).format('YYYY-MM-DD'), toDate: moment(this.filters.dateRange.endDate).add(1, 'days').format('YYYY-MM-DD')})).subscribe(
        (response) => {
          this.traces = response;
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
    );
  }

  getExecutionTasks() {
    this.traceService.list(new QueryOptions({finished: 0})).subscribe(
      (result) => {
        this.executionTasks = result;
      },
      (error) => {

      }
    );
    if(this.config.timers.executionsInterval.object !== null) {
      const self = this;
      setTimeout(function () {
        self.getExecutionTasks();
      }, this.config.timers.executionsInterval.tick);
    }
  }


  filterTraceStatus(trace, index, array) {

    // Comprobar si para el filtro de estado actual de la traza
    let result = (trace.finishedAt != null && this.filters.status['finished'].selected === true) || (trace.finishedAt === null && this.filters.status['execution'].selected === true);
    result = result && (trace.finishedAt != null && (trace.status === 0 && this.filters.finishedStatus['noErrors'].selected === true) || (trace.status !== 0 && this.filters.finishedStatus['withErrors'].selected === true));

    return result;
  }


  filteredTraces() {
    const self = this;
    return this.traces.filter(function(trace, index, array) {
      return self.filterTraceStatus(trace, index, array);
    });
  }

  getTraceCssClass(trace: any) {
    let result = '';
    if (!trace.finishedAt) {
      result = 'fa-warning text-yellow';
    }
    if (trace.status === 0) {
      result += ' fa-check-circle text-green';
    } else {
      result += ' fa-times-circle text-red';
    }
    return result;
  }
}
