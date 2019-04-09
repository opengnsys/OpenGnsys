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
      startDate: null,
      endDate: null
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
  }

  ngOnDestroy() {
    if (this.config.timers && this.config.timers.executionsInterval) {
      clearInterval(this.config.timers.executionsInterval.object);
    }
  }

ngOnInit(): void {
  const self = this;
  this.ogCommonService.loadEngineConfig().subscribe(
    data => {
      this.config = data;

      if (this.config.timers.executionsInterval.object === null) {
        this.config.timers.executionsInterval.object = setInterval(function() {
          self.getExecutionTasks();
        }, this.config.timers.executionsInterval.tick);
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
      this.traceService.list().subscribe(
        (response) => {
          this.traces = response;
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
      );
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
    this.ogSweetAlert.question( this.translate.instant('sure_to_delete') + '?', this.translate.instant('action_cannot_be_undone'), function(response) {
      const promises = [];
      for (let index = 0; index < this.selection.length; index++) {
        promises.push(this.traceService.delete(this.selection[index].id));
      }
      forkJoin(promises).subscribe(
        (success) => {
          this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_deleted')});
          this.selectAll = false;
          this.selection = [];
          this.searchText = '';
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
      );
    });
  }

  getExecutionTasks() {
    this.traceService.list(new QueryOptions({finished: 0})).subscribe(
      (result) => {
        this.executionTasks = result;
      },
      (error) => {

      }
    );
  }

  deleteExecutionTace(task) {
    this.ogSweetAlert.question(
      this.translate.instant('delete_task'),
      this.translate.instant('sure_to_delete_task') + '?',
      function(result) {
        if (result) {
          this.traceService.delete(task.id).subscribe(
            (response) => {
              this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_deleted')});
              this.getExecutionTasks();
            },
            (error) => {
              this.toaster.pop({type: 'error', title: 'error', body: error});
            }
          );

        }
      }
    );

  }

  relaunchExecutionTask(task) {
    this.ogSweetAlert.question(
      this.translate.instant('relaunch_task'),
      this.translate.instant('sure_to_relaunch_task') + '?',
      function(result) {
        if (result) {

        }
      }
    );
  }

  filterTraceStatus(trace, index, array) {

    // Comprobar si para el filtro de estado actual de la traza
    let result = (trace.finishedAt != null && this.filters.status['finished'].selected === true) || (trace.finishedAt === null && this.filters.status['execution'].selected === true);
    result = result && (trace.finishedAt != null && (trace.status === 0 && this.filters.finishedStatus['noErrors'].selected === true) || (trace.status !== 0 && this.filters.finishedStatus['withErrors'].selected === true));
    if (this.filters.dateRange.startDate != null) {
      result = result && moment(trace.executedAt).isAfter(this.filters.dateRange.startDate);
    }
    if (this.filters.dateRange.endDate != null) {
      result = result && moment(trace.executedAt).isBefore(this.filters.dateRange.endDate);
    }

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
