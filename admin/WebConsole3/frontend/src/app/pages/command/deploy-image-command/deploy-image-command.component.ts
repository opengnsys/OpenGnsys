import {Component, OnInit} from '@angular/core';

import {ToasterService} from '../../../service/toaster.service';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {OgCommonService} from '../../../service/og-common.service';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {AuthModule} from 'globunet-angular/core';
import {User} from '../../../model/user';
import {ImageService} from '../../../api/image.service';
import {OGCommandsService} from '../../../service/og-commands.service';

@Component({
  selector: 'app-deploy-image-command',
  templateUrl: './deploy-image-command.component.html',
  styleUrls: [ './deploy-image-command.component.scss' ]
})
export class DeployImageCommandComponent implements OnInit {
  torrent = {
    mode: 'peer',
    seedTime: '60'
  };
  multicast = {
    port: '9000',
    address: '239.194.16.140',
    mode: 'full-duplex',
    speed: 90,
    maxClients: 50,
    maxWaitTime: 60
  };
  disk = 1;
  partition = 1;

  images = [];
  deployMethods = [];
  deployMethod = 'MULTICAST';
  public deployImage: string;
  private user: User;
  private constants: any;
  public image: any;
  public editInstructions = false;


  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public ogCommandsService: OGCommandsService,
              private authModule: AuthModule,
              private router: Router,
              private activatedRoute: ActivatedRoute,
              private ogCommonService: OgCommonService,
              private imageService: ImageService,
              private ogSweetAlert: OgSweetAlertService,
              private toaster: ToasterService,
              private translate: TranslateService) {
    this.user = this.authModule.getLoggedUser();
  }


  ngOnInit() {
    this.deployImage = 'true';
    this.updateDeployOptions();
    if (this.user) {
      // Comprobar la selección de clientes
      if (this.ogCommonService.getSelectionSize() > 0) {
        this.imageService.list().subscribe(
            (response)  => {
              this.images = response;
            },
            (error) => {
              this.images = [];

            }
        );

      } else {
        // TODO - dar error?
        this.toaster.pop({type: 'error', body: this.translate.instant('not_clients_selected'), title: this.translate.instant('opengnsys_error')});
        this.router.navigate(['/app/ous']);
      }
    }
  }

  updateDeployOptions() {
    this.ogCommonService.loadEngineConfig().subscribe(
        data => {
          this.constants = data.constants;
          if (this.deployImage === 'true') {
            this.deployMethods = this.constants.deployMethods.deployImage;
          } else {
            // Si es updateCache, se quitan las opciones de deploy direct
            this.deployMethods = this.constants.deployMethods.updateCache;
          }
        }
    );

  }

  /**/
  generateOgInstruction() {
    let script = '';
    const disk = this.disk;
    const partition = this.partition;
    // Capturar ip del repositorio de la imagen elegida
    let ip = '172.16.140.210';
    let imgName = this.image.canonicalName;
    let target = ' ' + disk + ' ' + partition;
    let log = 'ogEcho log session "[0] $MSG_SCRIPTS_TASK_START ';

    // Modo deploy
    if (this.deployImage === 'true') {
      script = 'deployImage ';
    } else {
      script = 'updateCache ';
      ip = 'REPO';
      imgName += '.img';
      target = '';
    }
    script += ip + ' /' + imgName + target + ' ' + this.deployMethod;
    log += script + '"\n';
    script = log + script;

    // Modo
    let params = '';
    if (this.deployMethod === 'MULTICAST' || this.deployMethod === 'MULTICAST-DIRECT') {
      params = this.multicast.port + ':' + this.multicast.mode + ':' + this.multicast.address + ':' + this.multicast.speed + 'M:' + this.multicast.maxClients + ':' + this.multicast.maxWaitTime;
    } else if (this.deployMethod === 'TORRENT') {
      params = this.torrent.mode + ':' + this.torrent.seedTime;
    }
    script += ' ' + params;

    this.ogCommandsService.ogInstructions = script;
  }

  setCanonicalName() {
    //this.command.canonicalName = this.command.image.canonicalName;
  }
}
