import {Component, NgZone, OnInit} from '@angular/core';

import {ClientService} from 'src/app/api/client.service';
import {Client} from 'src/app/model/client';
import {ActivatedRoute, ParamMap, Router} from '@angular/router';
import {Observable} from 'rxjs';
import {NetbootService} from '../../api/netboot.service';
import {ToasterService} from '../../service/toaster.service';
import {RepositoryService} from '../../api/repository.service';
import {HardwareProfileService} from '../../api/hardware-profile.service';
import {Repository} from '../../model/repository';
import {HardwareProfile} from '../../model/hardware-profile';
import {OgCommonService} from '../../service/og-common.service';
import {ClientFormType} from '../../form-type/client.form-type';
import * as pluginDataLabels from 'chartjs-plugin-datalabels';
import {ChartOptions} from 'chart.js';

@Component({
    selector: 'app-client',
    templateUrl: './client.component.html',
    styleUrls: ['./client.component.scss']
})
export class ClientComponent implements OnInit {
    public client: Client;
    public netboots: any = [];
    public repositories: Repository[] = [];
    public hardwareProfiles: HardwareProfile[] = [];
    public oglives: any[] = [];
    private formType: ClientFormType;
    public form;
    public pluginDataLabels;

    // this tells the tabs component which Pages
    // should be each tab's root Page
    constructor(private router: Router,
                private activatedRouter: ActivatedRoute,
                private clientService: ClientService,
                private netbootService: NetbootService,
                private toaster: ToasterService,
                private repositoryService: RepositoryService,
                private hardwareProfileService: HardwareProfileService,
                private ogCommonService: OgCommonService) {
        this.client = new Client();
        this.client.disksConfig = [];
        this.formType = new ClientFormType();
        this.form = this.formType.getForm();
    }

    ngOnInit(): void {
        this.loadNetboots();
        this.loadOgLives();
        this.loadRepositories();
        this.loadHardwareProfiles();
        // Comprobar por un lado si es edicion o un nuevo cliente
        this.activatedRouter.paramMap.subscribe(
            (data: ParamMap) => {
                if (data.get('id')) {
                    // @ts-ignore
                    const id: number = data.get('id');
                    this.clientService.read(id).subscribe(
                        client => {
                            this.client = client;
                            this.ogCommonService.selectedClients = {};
                            this.ogCommonService.selectedClients[client.id] = client;

                            this.client.disksConfig = this.ogCommonService.getDisksConfigFromPartitions(this.client.partitions);

                            const self = this;
                            this.client.disksConfig.forEach(function(diskConfig) {
                                const chartData = self.getChartData(diskConfig);
                                diskConfig.diskChartData = chartData.diskChartData;
                                diskConfig.diskChartLabels = chartData.diskChartLabels;
                                diskConfig.diskPieChartColors = chartData.diskPieChartColors;
                                diskConfig.diskChartOptions = chartData.diskChartOptions;
                            });

                        }
                    );
                }
                this.activatedRouter.queryParams.subscribe(
                    query => {
                        this.client.organizationalUnit = query.ou;
                    }
                );
            },
            error => {
                console.log(error);
            }
        );




    }

    private loadOgLives() {
        this.ogCommonService.loadEngineConfig().subscribe(
            data => {
                this.oglives = data.constants.ogliveinfo;
                this.formType.getField(this.form, 'oglive').options.items = this.oglives;
            }
        );
    }

    private loadNetboots() {
        this.netbootService.list().subscribe(
            (result) => {
                this.netboots = result;
                this.formType.getField(this.form, 'netboot').options.items = this.netboots;
            },
            (error) => {
                this.toaster.pop({type: 'error', title: 'error', body: error});
            }
        );
    }

    private loadRepositories() {
        this.repositoryService.list().subscribe(
            list => {
                this.repositories = list;
                this.formType.getField(this.form, 'repository').options.items = this.repositories;
            },
            error => {
                this.toaster.pop({type: 'error', title: 'error', body: error});
            }
        );
    }

    private loadHardwareProfiles() {
        this.hardwareProfileService.list().subscribe(
            list => {
                this.hardwareProfiles = list;
                this.formType.getField(this.form, 'hardwareProfile').options.items = this.hardwareProfiles;

            },
            error => {
                this.toaster.pop({type: 'error', title: 'error', body: error});
            }
        );
    }

    getChartData(diskConfig) {
        const diskChartData = [];
        const diskChartLabels = [];
        const diskPieChartColors = [{
            backgroundColor: []
        }];
        const self = this;
        diskConfig.partitions.forEach(function(partition) {
            if (partition.size > 0) {
                self.setPartitionPercentOfDisk(diskConfig, partition);
                diskChartData.push(partition.percentOfDisk);
                diskChartLabels.push([
                    (partition.os || partition.filesystem),
                    partition.percentOfDisk + '%'
                ]);
                diskPieChartColors[0].backgroundColor.push(self.ogCommonService.getPartitionColor(partition));
            }
        });
        const diskChartOptions: ChartOptions = {
            responsive: true,
            legend: {
                position: 'bottom'
            },
            plugins: {
                datalabels: {
                    formatter: (value, ctx) => {
                        const label = ctx.chart.data.labels[ctx.dataIndex];
                        return label;
                    },
                },
            }
        };

        return {
            diskChartData: diskChartData,
            diskChartLabels: diskChartLabels,
            diskChartOptions: diskChartOptions,
            diskPieChartColors: diskPieChartColors
        };
    }

    setPartitionPercentOfDisk(diskConfig, partition) {
        partition.percentOfDisk = Math.round(((partition.size * 100) / diskConfig.size) * 100) / 100;
    }

    labelFormatter(label, series) {
        return '<div style="font-size:13px; text-align:center; padding:2px; color: #000; font-weight: 600;">'
            + '<br>'
            + series.percentOfDisk + '%</div>';
    }


    save() {
        let request: Observable<Client>;
        if (this.client.id !== 0) {
            request = this.clientService.update(this.client);
        } else {
            request = this.clientService.create(this.client);
        }
        request.subscribe(
            data => {
                this.toaster.pop({type: 'success', title: 'success', body: 'Successfully saved'});
                this.router.navigate(['/app/ous']);
            },
            error => {
                this.toaster.pop({type: 'error', title: 'error', body: error});
            }
        );
    }

    getSizeInGB(size) {
        size = size / (1024 * 1024);
        return Math.round(size * 100) / 100;
    }


    getPartitionUsageClass(usage: number | string | number) {
        let result = '';

        if (usage < 60) {
            result = 'bg-green';
        } else if (usage >= 60 && usage < 80) {
            result = 'bg-yellow';
        } else if (usage >= 80) {
            result = 'bg-red';
        }
        return result;
    }
}
