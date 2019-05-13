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
import {Command, Excecution} from '../../../model/command';
import {Client, Partition} from '../../../model/client';
import {Repository} from '../../../model/repository';
import {RepositoryService} from '../../../api/repository.service';
import {ImageService} from '../../../api/image.service';
import {forkJoin} from 'rxjs';

@Component({
  selector: 'app-create-image-command',
  templateUrl: './create-image-command.component.html',
  styleUrls: [ './create-image-command.component.scss' ]
})
export class CreateImageCommandComponent implements OnInit {
  private readonly user: User;
  private constants: any;
  public repositories: Repository[];
  public execution = new Excecution();
  public commands: Command[] = [];
  public client: Client;
  public images = [];
  public command = {canonicalName: '', image: new Image()};
  public selectedPartition: number;


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
      const clientId = Object.keys(this.ogCommonService.selectedClients)[0];
      this.client = this.ogCommonService.selectedClients[clientId];
      this.execution.clients = clientId;
      this.ogCommonService.loadEngineConfig().subscribe(
          data => {
            this.constants = data.constants;
          }
      );
      this.loadRepositories();
      this.loadImages();
    } else {
      // TODO - dar error?
      this.ogSweetAlert.error(this.translate.instant('opengnsys_error'), this.translate.instant('not_clients_selected'));
      this.router.navigate(['app.ous']);
    }
  }


  sendCommand() {
    if (!this.selectedPartition) {
      this.toaster.pop({type: 'error', title: 'error', body: this.translate.instant('you_must_select_partition')});
    } else {
      const disk = this.client.partitions[this.selectedPartition].numDisk;
      const partition = this.client.partitions[this.selectedPartition].numPartition;
      // Al crear la imagen, le asociamos un perfil software
      // @ts-ignore
      this.execution.script	 = this.constants.commands.SOFTWARE_INVENTORY + ' ' + disk + ' ' + partition + '\n';
      this.execution.script += this.constants.commands.CREATE_IMAGE + ' ' + disk + ' ' + partition + ' ' + this.command.canonicalName + ' REPO ';
      // @ts-ignore
      this.execution.script = this.execution.script.replace(/\"/g, '\\"').replace(/\$/g, '\\\$');

      let image: Image = this.command.image;
      let newImage = false;

      let result = true;
      // Crear la imagen si no existe
      if (!image) {
        newImage = true;
        // Comprobar que exista el repositorio, sino no podemos crear la nueva imagen
        if (!this.repositories) {
          result = false;
          this.toaster.pop({type: 'error', title: 'error', body: this.translate.instant('no_repository_exist')});
        } else {
          // Usar el repositorio por defecto
          const repository = this.repositories[0];
          image = new Image();
          image.canonicalName =  this.command.canonicalName;
          image.description =  this.translate.instant('image_created_automatically');
          image.repository =  repository;
        }
      }

      // Asignar a la imagen los atributos del sistema operativo elegido
      image.client = this.client;

      // Si no hubo ningun error se guardan todas las pgms
      if (result === true) {
        const promises = [];
        if (newImage === true) {
          promises.push(this.imageService.create(image));
        } else {
          const imageCopy = Object.assign({}, image);
          delete imageCopy.id;
          delete imageCopy.softwareProfile;
          promises.push(this.imageService.update(imageCopy));
        }
        this.execution.type = 'CREATE_IMAGE';
        promises.push(this.commandService.execute(this.execution));
        forkJoin(promises).subscribe(
            (response) => {
              this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_executed')});
              this.router.navigate(['app.ous']);
            },
            (error) => {
              this.toaster.pop({type: 'error', title: 'error', body: error});
            }
        );
      }
    }
  }

  setCanonicalName() {
    if (this.command.image !== null) {
      this.command.canonicalName = this.command.image.canonicalName;
    } else {
      this.command.canonicalName = '';
    }
  }

  private loadImages() {
    this.imageService.list().subscribe(
        (response) => {
          this.images = response;
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
    );
  }
  private loadRepositories() {
    this.repositoryService.list().subscribe(
        (response) => {
          this.repositories = response;
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
    );
  }

  isClonable(partition) {
    let clonable = false;
    let index = 0;
    const code = partition.partitionCode;

    if (partition.numPartition !== 0) {
      // Buscar el codigo entre las constantes
      while (index < this.constants.partitiontable.length && !clonable) {
        // para cada tabla de particiones, buscamos el codigo de la particion
        const elements = this.constants.partitiontable[index].partitions.filter(function(part) {
            return (part.id === partition.partitionCode.padStart(2, '0'));
          }
        );
        clonable = (elements.length > 0 && elements[0].clonable === true);
        index++;
      }
    }

    return clonable;
  }

}
