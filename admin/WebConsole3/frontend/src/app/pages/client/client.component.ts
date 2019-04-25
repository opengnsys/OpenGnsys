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
import {GlobunetFormType} from '../../form-type/globunet.form-type';

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



}
