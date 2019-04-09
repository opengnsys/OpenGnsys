import {Component, OnDestroy, OnInit} from '@angular/core';
import {AuthModule, tr} from 'globunet-angular/core';
import {TranslateService} from '@ngx-translate/core';
import {OgCommonService} from '../../service/og-common.service';
import {StatusService} from '../../api/status.service';
import { Chart } from 'chart.js';

class Info {
  cpu: any = {
    usage: 0,
    model: '-'
  };
  ram: any = {
    total: 0,
    used: 0,
    units: '-'
  };
  disk: any = {};
  network: any = {
    inBytes: 0,
    outBytes: 0,
    card: '-'
  };
  ogServices: any[] = [];
}


@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit, OnDestroy {
  private timers: any;
  maxLength = 50;
  currentPos = 0;
  info: Info;
  diskIndex = 0;
  status: any;
  chart: Chart;

  constructor(private authModule: AuthModule, private translate: TranslateService, private ogCommonService: OgCommonService, private statusService: StatusService) {
    this.status = {
      data: [
        {
          label: translate.instant('memory'),
          data: [],
          color: '#3c8dbc'
        },
        {
          label: translate.instant('cpu'),
          data: [],
          color: '#00FF00'
        }
      ],
      options: {
        grid: {
          borderColor: '#f3f3f3',
          borderWidth: 1,
          tickColor: '#f3f3f3'
        },
        series: {
          shadowSize: 0, // Drawing is faster without shadows
          color: '#3c8dbc'
        },
        lines: {
          fill: true, // Converts the line chart to area chart
          color: '#3c8dbc'
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
    this.info = new Info();
  }

  ngOnInit() {
    this.chart = new Chart('canvas', this.status);
    // La primera vez que entra en dashboard
    this.ogCommonService.loadEngineConfig().subscribe(
      (config) => {
        this.timers = config.timers;
        if (this.timers.serverStatusInterval.object == null) {
          this.updateStatus();
          this.timers.serverStatusInterval.object = setInterval(() => {
            this.updateStatus();
          }, this.timers.serverStatusInterval.tick);
        }
      },
      (error) => {

      }
    );

  }

  ngOnDestroy(): void {
    if (this.timers && this.timers.serverStatusInterval) {
      clearInterval(this.timers.serverStatusInterval.object);
    }
  }

  updateStatus() {
    this.statusService.list().subscribe(
      (response: any) => {
        response = response[0];
        response.ogServices = response.ogServices || [];
        for (let index = 0; index < response.ogServices.length; index++) {
          response.ogServices[index].etime = response.ogServices[index].etime.replace('-', ' d, ');
        }

        // Pasar de bytes a KB, MB o GB dependiendo del caso
        response.network.inBytes = this.ogCommonService.getUnits(response.network.inBytes);
        response.network.outBytes = this.ogCommonService.getUnits(response.network.outBytes);

        this.info = {
          cpu: response.cpu,
          ram: {
            total: Math.round((response.memInfo.total / (1024 * 1024))),
            used: Math.round((response.memInfo.used / (1024 * 1024)) * 100) / 100,
            units: 'GB'
          },
          disk: response.disk,
          network: response.network,
          ogServices: response.ogServices

        };
        // Calcular porcentaje de memoria
        const mem = Math.round(((response.memInfo.used * 100) / response.memInfo.total) * 100) / 100;
        let index = 0;
        if (this.status.data[0].data.length > 0) {
          index = this.status.data[0].data[this.status.data[0].data.length - 1][0] + 1;
        }
        this.status.data[0].data.push([index, mem]);
        this.status.data[1].data.push([index, response.cpu.usage]);
        if (this.status.data[0].data.length > this.maxLength) {
          this.status.data[0].data.shift();
          this.status.data[1].data.shift();
        }
      },
      (error) => {
        alert(error);
      }
    );
  }

  changeDiskIndex() {
    this.diskIndex = (this.diskIndex + 1) % this.info.disk.length;
  }



}
