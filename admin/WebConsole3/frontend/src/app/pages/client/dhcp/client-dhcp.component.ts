import {Component, OnInit} from '@angular/core';

import {ClientService} from 'src/app/api/client.service';
import {ActivatedRoute, ParamMap, Router} from '@angular/router';
import {forkJoin, Observable} from 'rxjs';
import {NetbootService} from '../../../api/netboot.service';
import {ToasterService} from '../../../service/toaster.service';
import {RepositoryService} from '../../../api/repository.service';
import {HardwareProfileService} from '../../../api/hardware-profile.service';
import {Repository} from '../../../model/repository';
import {HardwareProfile} from '../../../model/hardware-profile';
import {OgCommonService} from '../../../service/og-common.service';
import {ClientFormType} from '../../../form-type/client.form-type';
import {TranslateService} from '@ngx-translate/core';

@Component({
    selector: 'app-client-dhcp',
    templateUrl: './client-dhcp.component.html',
    styleUrls: ['./client-dhcp.component.scss']
})
export class ClientDhcpComponent implements OnInit {
    public netboots: any = [];
    public repositories: Repository[] = [];
    public hardwareProfiles: HardwareProfile[] = [];
    public oglives: any[] = [];
    private formType: ClientFormType;
    public form;
    public constants = {};
    private dhcpFile: string;
    public commonProperties: any;
    public dhcpText: string;
    public clients: any[];
    public selectAll: boolean;

    // this tells the tabs component which Pages
    // should be each tab's root Page
    constructor(private router: Router,
                private activatedRouter: ActivatedRoute,
                private clientService: ClientService,
                private netbootService: NetbootService,
                private translate: TranslateService,
                private toaster: ToasterService,
                private repositoryService: RepositoryService,
                private hardwareProfileService: HardwareProfileService,
                private ogCommonService: OgCommonService) {

        this.commonProperties = {};
        this.clients = [];

    }

    ngOnInit() {
        this.ogCommonService.loadEngineConfig().subscribe(
            data => {
                this.constants = data.constants;
            }
        );
        this.dhcpFile = '/etc/dhcp/dhcpd.conf';
        this.loadNetboots();
        // Los repositorios vienen cargados ya desde config.router
        this.repositoryService.list().subscribe(
            (repositories) => {
                this.repositories = repositories;
                this.commonProperties.repository = this.repositories[0].id;
                if (!this.hardwareProfiles) {
                    this.hardwareProfileService.list().subscribe(
                        (response) => {
                            this.hardwareProfiles = response;
                            this.commonProperties.hardwareProfile = this.hardwareProfiles[0].id;
                        },
                        (error) =>  {
                            alert(error);
                        }
                    );
                } else {
                    this.hardwareProfiles = this.hardwareProfiles;
                }
            },
            (error) => {
                this.toaster.pop({type: 'error', title: 'error', body: error});
            }
        );

    }

    loadNetboots() {
        this.netbootService.list().subscribe(
            (result) => {
                this.netboots = result;
                this.commonProperties.netboot = this.netboots[0];
            },
            (error) => {
                this.toaster.pop({type: 'error', title: 'error', body: error});
            }
        );
    }


    downloadFromServer() {
        this.dhcpText = 'ddns-update-style none;\n' +
            'option domain-name "example.org";\n' +
            'log-facility local7;\n' +
            'not-authoritative;\n' +
            '\n' +
            'subnet 172.16.140.0 netmask 255.255.255.0 {\n' +
            '    option domain-name-servers 172.16.3.1;\n' +
            '    option routers 172.16.140.254;\n' +
            '    option broadcast-address 172.16.140.255;\n' +
            '    default-lease-time 600;\n' +
            '    max-lease-time 7200;\n' +
            '    next-server 172.16.140.210;\n' +
            '    filename "grldr";\n' +
            '    use-host-decl-names on;\n' +
            '\n' +
            '#    host HOSTNAME1 {\n' +
            '#        hardware ethernet HOSTMAC1;\n' +
            '#        fixed-address HOSTIP1;\n' +
            '#   }\n' +
            '\n' +
            '    host pc-pruebas {\n' +
            '        hardware ethernet 00:1B:21:1F:EE:9D;\n' +
            '        fixed-address 172.16.140.213;\n' +
            '    }\n' +
            '    host pc-virtualbox {\n' +
            '        hardware ethernet 20:CF:30:BF:9A:39;\n' +
            '        fixed-address 172.16.140.214;\n' +
            '    }\n' +
            '\n' +
            '\n' +
            '}\n';
        /*
        ServerDchpResource.getDhcp().then(
            function(response) {
                this.dhcpText = response.text;
                this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_loaded')});
            },
            function(error) {
                this.toaster.pop({type: 'error', title: 'error', body: error});
            }
        );
        /**/
    }

    proccessDhcp() {
        if (typeof this.dhcpText !== 'undefined' && this.dhcpText !== '') {
            const lines = this.dhcpText.split('\n');
            this.clients = [];
            for (let i = 0; i < lines.length; i++) {
                // Comprobar si la línea actual contiene la palabra "host" sin ninguna # delante que sería comentario
                if (/^host/.test(lines[i].trim())) {
                    // Unimos las siguientes líneas hasta encontrar "}"
                    let line = '';
                    while(lines[i].indexOf("}") === -1 && i < lines.length){
                        line += lines[i];
                        i++;
                    }
                    // procesar la linea
                    // host pc53-151 { hardware ethernet 00:1E:33:61:49:B8; fixed-address 172.16.53.151; }
                    let parts = line.split('{');
                    const hostname = parts[0].trim().split(' ')[1];

                    // Las siguientes partes pueden estar en la linea actual o las siguientes
                    parts = parts[1].trim().split(";");
                    const mac = parts[0].trim().split('ethernet')[1];
                    // lo mismo puede ocurrir con fixed-address puede estar en lineas diferentes
                    parts = parts[1].trim().split('fixed-address');
                    const ip = parts[1];
                    this.clients.push(
                        {
                            name: hostname,
                            ip: ip,
                            mac: mac,
                            $$selected: true
                        }
                    );
                }
            }
        } else {
            this.toaster.pop({type: 'error', title: 'error', body: this.translate.instant('nothing_to_proccess')});
        }
    }

    selectedUnselectAll() {
        for (let c = 0; c < this.clients.length; c++) {
            this.clients[c].$$selected = this.selectAll;
        }
    }

    save() {
        const promises = [];
        let ou = '';
        this.activatedRouter.queryParams.subscribe(
            query => {
                ou = query.ou;
            }
        );
        for (let c = 0; c < this.clients.length; c++) {
            if (this.clients[c].$$selected === true) {
                const client = Object.assign({}, this.clients[c]);

                // Si se indicó un padre en la url, se añade dicha propiedad
                client.organizationalUnit = ou;
                client.idproautoexec = 0;
                client.netdriver = this.commonProperties.netdriver;
                client.netiface = this.commonProperties.netiface;
                // Propiedades comunes
                // client.repository = this.commonProperties.repository;
                // client.hardwareProfile = this.commonProperties.hardwareProfile;
                promises.push(this.clientService.create(client));
            }
        }
        forkJoin(promises).subscribe(
            (response) => {
                this.toaster.pop({type: 'success', title: 'success', body: 'Successfully saved'});
                this.router.navigate(['/app/ous']);
            },
            (error) => {
                this.toaster.pop({type: 'error', title: 'error', body: error});
            }
        );
    }

}
