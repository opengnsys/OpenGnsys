import {Component, OnInit} from '@angular/core';

import {ToasterService} from '../../../service/toaster.service';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {OgCommonService} from '../../../service/og-common.service';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {AuthModule} from 'globunet-angular/core';
import {User} from '../../../model/user';
import {OGCommandsService} from '../../../service/og-commands.service';
import {Client, Partition} from '../../../model/client';
import {CommandService} from '../../../api/command.service';
import {forkJoin} from 'rxjs';
import {Execution} from '../../../model/command';

@Component({
  selector: 'app-format-command',
  templateUrl: './format-command.component.html',
  styleUrls: [ './format-command.component.scss' ]
})
export class FormatCommandComponent implements OnInit {
  execution = new Execution();
  command = {};
  user: User;
  clientGroups = {};
  constants: any;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public ogCommandsService: OGCommandsService,
              private authModule: AuthModule,
              private router: Router,
              private activatedRoute: ActivatedRoute,
              public ogCommonService: OgCommonService,
              private commandService: CommandService,
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
      // Recorrer todos los clientes y formar los grupos según el partitionCode de sus particiones, deben coincidir todos
      for (let index = 0; index < clientIds.length; index++) {
        // Generamos una clave usando disco-particion-code para comparar
        const client = this.ogCommonService.selectedClients[clientIds[index]];
        const key = this.getPartitionsCode(client.partitions);

        if (!this.clientGroups[key]) {
          this.clientGroups[key] = [];
        }
        this.clientGroups[key].push(client);
      }
    } else {
      // TODO - dar error?
      this.ogSweetAlert.error(this.translate.instant('opengnsys_error'), this.translate.instant('not_clients_selected'));
      this.router.navigate(['/app/ous']);
    }
  }

  getPartitionsCode(partitions) {
    let key = '';
    for (let p = 0; p < partitions.length; p++) {
      // Además de calcular la clave, alteramos el partitionCode pasandolo a mayusculas y aplicando padding de "0" a la izquierda si es necesario
      partitions[p].partitionCode = partitions[p].partitionCode.toUpperCase();
      if (partitions[p].partitionCode.length === 1) {
        partitions[p].partitionCode = '0' + partitions[p].partitionCode;
      }
      key += partitions[p].numDisk + partitions[p].numPartition + partitions[p].partitionCode;
    }
    return key;
  }


  sendCommand() {
    // Comrobar qué particiones se han seleccionado de qué grupos
    const executions = {};
    const groups = Object.keys(this.clientGroups);
    for (let g = 0; g < groups.length; g++) {
      if (!executions[g]) {
        executions[g] = new Execution();
      }
      // Recorrer las particiones del primer cliente de la lista y ver si hay alguna seleccionada
      const found = false;
      // La partición 0 no se usa, solo indica las propiedades del disco
      let index = 1;
      const client = this.clientGroups[groups[g]][0];
      for (let c = 0; c < this.clientGroups[groups[g]].length; c++) {
        executions[g].clients += client.id + ',';
      }
      while (!found && index < client.partitions.length) {
        const partition = client.partitions[index];
        if (partition.selected === true) {
          if (executions[g].script === '') {
            executions[g].script = 'ogUnmountAll ' + partition.numDisk + '\n';
          }
          // Si la particion es cache
          if (partition.partitionCode.toUpperCase() === 'CA') {
            executions[g].script += 'ogFormatCache' + '\n';
          } else {
            executions[g].script += 'ogFormat ' + partition.numDisk + ' ' + partition.numPartition + ' ' + partition.filesystem + '\n';
          }
        }
        index++;
      }
    }

    // Creamos tantas promises como diferentes grupos de ejecución haya
    const promises = [];
    const len = Object.keys(executions).length;
    for (let index = 0; index < len; index++) {
      const execution = {
        type: 'RUN_SCRIPT',
        script: executions[index].script,
        clients: executions[index].clients.substring(0, executions[index].clients.length - 1),	// Quitar la ultima ","
        sendConfig: true
      };
      promises.push(this.commandService.execute(execution));
    }
    forkJoin(promises).subscribe(
        (response) => {
          this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_executed')});
          this.router.navigate(['/app/ous']);
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
    );
  }

  getPartitionTypes(partitions) {
    let types = [];
    const infoPart = partitions.filter((partition: Partition) => partition.numPartition === 0);
    if (infoPart.length === 1) {
      const partitionTable = this.ogCommonService.getPartitionTable(infoPart[0]);
      types = partitionTable.partitions;
    }
    return types;
  }

}
