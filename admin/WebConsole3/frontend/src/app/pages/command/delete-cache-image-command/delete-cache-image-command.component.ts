import {Component, OnInit} from '@angular/core';

import {ToasterService} from '../../../service/toaster.service';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {OgCommonService} from '../../../service/og-common.service';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {AuthModule} from 'globunet-angular/core';
import {User} from '../../../model/user';
import {OGCommandsService} from '../../../service/og-commands.service';
import {CommandService} from '../../../api/command.service';
import {Image} from '../../../model/image';
import {Command, Execution} from '../../../model/command';
import {Client} from '../../../model/client';
import {Repository} from '../../../model/repository';
import {RepositoryService} from '../../../api/repository.service';
import {ImageService} from '../../../api/image.service';

@Component({
  selector: 'app-delete-cache-image-command',
  templateUrl: './delete-cache-image-command.component.html',
  styleUrls: [ './delete-cache-image-command.component.scss' ]
})
export class DeleteCacheImageCommandComponent implements OnInit {
  private readonly user: User;
  private constants: any;
  public repositories: Repository[];
  public execution = new Execution();
  public commands: Command[] = [];
  public client: Client;
  public cacheImages = [];
  public command = {canonicalName: '', image: new Image()};


  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public ogCommandsService: OGCommandsService,
              private authModule: AuthModule,
              private router: Router,
              private activatedRoute: ActivatedRoute,
              private ogCommonService: OgCommonService,
              private commandService: CommandService,
              private imageService: ImageService,
              private repositoryService: RepositoryService,
              private ogSweetAlert: OgSweetAlertService,
              private toaster: ToasterService,
              private translate: TranslateService) {
    this.user = this.authModule.getLoggedUser();
  }




  ngOnInit() {
    if (this.user && this.ogCommonService.selectedClients) {
      this.ogCommonService.loadEngineConfig().subscribe(
          data => {
            this.constants = data.constants;
          }
      );
      const clientIds = Object.keys(this.ogCommonService.selectedClients);
      this.execution.clients = clientIds.join(',');
      // Capturar para todos los clientes todas las imágenes de cache
      this.cacheImages = [];
      for (let index = 0; index < clientIds.length; index++) {
        const client = this.ogCommonService.selectedClients[clientIds[index]];
        const diskConfigs = this.ogCommonService.getDisksConfigFromPartitions(client.partitions);
        for (let dc = 0; dc < diskConfigs.length; dc++) {
          const diskConfig = diskConfigs[dc];
          for (let p = 0; p < diskConfig.partitions.length; p++) {
            const partition = diskConfig.partitions[p];
            if (partition.partitionCode === 'ca') {
              // Solo cogemos las imagenes .img, no los .sum
              for (let f = 0; f < partition.cacheContent.files.length; f++) {
                const file = partition.cacheContent.files[f];
                // Si no es un .sum
                if (!file.name.match('.sum')) {
                  this.cacheImages.push(file);
                }
              }
            }
          }
        }
      }
    } else {
      // TODO - dar error?
      this.ogSweetAlert.error(this.translate.instant('opengnsys_error'), this.translate.instant('not_clients_selected'));
      this.router.navigate(['app.ous']);
    }
  }


  sendCommand() {
    this.execution.script = '';
    for (let f = 0; f < this.cacheImages.length; f++) {
      if (this.cacheImages[f].selected === true) {
        if (this.cacheImages[f].type !== 'D') {
          this.execution.script += 'rm -rf $OGCAC/$OGIMG/' + this.cacheImages[f].name.trim() + '*';
        } else {
          this.execution.script += 'rm -rf $OGCAC/$OGIMG/' + this.cacheImages[f].name.trim();
        }
        this.execution.script += '\n';
      }
    }
    this.execution.script = this.execution.script.replace(/\"/g, '\\"').replace(/\$/g, '\\\$');
    this.execution.type = 'RUN_SCRIPT';
    this.execution.sendConfig = true;

    this.commandService.execute(this.execution).subscribe(
        (response)  => {
          this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_executed')});
          this.router.navigate(['/app/ous']);
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
    );
  }

}
