import {ToasterService} from './toaster.service';
import {OgSweetAlertService} from './og-sweet-alert.service';
import {CommandService} from '../api/command.service';
import {OgCommonService} from './og-common.service';
import {TranslateService} from '@ngx-translate/core';
import {Router} from '@angular/router';
import {Injectable} from '@angular/core';
import * as _ from 'lodash';
import {environment} from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class OGCommandsService {
  public ogInstructions = '';
  public execution: any;
  private commands = [];

  constructor(private router: Router, private ogCommonService: OgCommonService, private toaster: ToasterService, private ogSweetAlert: OgSweetAlertService,  private commandService: CommandService, private translate: TranslateService) {
    this.execution = {};
    this.ogCommonService.loadEngineConfig().subscribe(
      (response) => {
        this.commands = response.constants.commandtypes;
      }
    );
  }

    sendCommand() {
      let result = true;
      // TODO - Comprobar parametros
      if (!this.execution.script) {
        result = false;
        this.toaster.pop({type: 'error', title: 'error', body: this.translate.instant('command_not_valid')});
      } else if (!this.execution.clients) {
        result = false;
        this.toaster.pop({type: 'error', title: 'error', body: this.translate.instant('not_clients_selected')});
      }
      // Si no hubo ningun error
      if (result === true) {
        this.execution.script = this.execution.script.replace(/\"/g, '\\"').replace(/\$/g, '\\\$');
        // Resetar las instrucciones del script opengnsys almacenadas.
        this.ogInstructions = '';
        this.commandService.execute(this.execution).subscribe(
          (response: any[]) => {
            // Buscar en la respuesta si hay algún statuscode diferente de 200
            const errors = response.filter(function(value) {
              return (value.statusCode && value.statusCode !== '!200');
            } );
            let errorStr = '';
            let toasterOpts = {type: 'success', title: 'success', body: this.translate.instant('successfully_executed')};
            if (errors.length > 0) {
              for (let e = 0; e < errors.length; e++) {
                errorStr += this.translate.instant('execution_failed_in') + ' ' + errors[e].name + '\n';
              }

              toasterOpts = {type: 'error', title: 'error', body: errorStr};
            }
            this.toaster.pop(toasterOpts);
            this.router.navigate(['/app/ous']);
          },
          (error)  => {
            this.toaster.pop({type: 'error', title: 'error', body: error});
          }
        );
      }
    }

    execute(command, params?) {

      this.execution.type = command;

      if (command === 'HISTORY_LOG') {
        let clientIp = null;
        // Abrir ventana de log
        if (typeof params === 'undefined' || typeof params.clientIp === 'undefined') {
          const client = this.ogCommonService.selectedClients[Object.keys(this.ogCommonService.selectedClients)[0]];
          if (client) {
            clientIp = client.ip;
          }
        } else {
          clientIp = params.clientIp;
        }

        if (clientIp) {
          const url = 'http://' + clientIp + environment.commands.HISTORY_LOG;
          window.open(url, '', 'resizable=yes,toolbar=no,status=no,location=no,menubar=no,scrollbars=yes');
        }
      } else if (command === 'REALTIME_LOG') {
        let clientIp = null;
        // Abrir ventana de log
        if (typeof params === 'undefined' || typeof params.clientIp === 'undefined') {
          const client = this.ogCommonService.selectedClients[Object.keys(this.ogCommonService.selectedClients)[0]];
          if (client) {
            clientIp = client.ip;
          }
        } else {
          clientIp = params.clientIp;
        }

        if (clientIp) {
          const url = 'http://' + clientIp + environment.commands.REALTIME_LOG;
          window.open(url, '', 'resizable=yes,toolbar=no,status=no,location=no,menubar=no,scrollbars=yes');
        }
      } else if (command === 'SOFTWARE_INVENTORY') {
        const client = this.ogCommonService.selectedClients[Object.keys(this.ogCommonService.selectedClients)[0]];
        // Preparar el scope para el sweet alert
        const options = {
          scope: {
            partitions: [],
            selectedPart: null
          }
        };

        // Comprobar tipo de cada particion para ver si es clonable
        // var parttable = $rootScope.constants.partitiontable[client.partitions[0].partitionCode-1];
        // buscar las particiones que sean clonables
        for (let index = 1; index < client.partitions.length; index++) {
          if (client.partitions[index].osName !== 'DATA' && client.partitions[index].osName !== '') {
            // Crear como nombre para mostrar, el disco y partición del sistema
            const obj = Object.assign({}, client.partitions[index]);
            obj.name = 'disco: ' + obj.numDisk + ', part: ' + obj.numPartition + ', SO: ' + client.partitions[index].osName;
            options.scope.partitions.push(obj);
          }
        }

        this.ogSweetAlert.swal({
            title: this.translate.instant('select_partition_to_inventary'),
            // text: $filter("translate")("action_cannot_be_undone"),
            type: 'info',
            input: 'select',
            inputOptions: options.scope.partitions,
            showCancelButton: true,
            confirmButtonColor: '#3c8dbc',
            confirmButtonText: this.translate.instant('done'),
            closeOnConfirm: true
          }).then(
          function(result) {
            if (result.value) {
              // Montar el script con el disco y partición elegida
              this.execution.script = this.commands.SOFTWARE_INVENTORY + ' ' + result.value;
              this.loadClients();
              this.sendCommand();
            }
          },
          null);
      } else {
        if (command === 'REBOOT') {
          this.execution.script = environment.commands.REBOOT;
        } else if (command === 'POWER_OFF') {
          this.execution.script = environment.commands.POWER_OFF;
        } else if (command === 'POWER_ON') {
          this.execution.script = 'wakeonlan';
        } else if (command === 'HARDWARE_INVENTORY') {
          this.execution.script = environment.commands.HARDWARE_INVENTORY;
        } else if (command === 'RUN_SCRIPT') {
          this.execution.script = params ? (params.script || this.ogInstructions) : this.ogInstructions;
        } else if (command === 'REFRESH_INFO') {
          this.execution.script = environment.commands.REFRESH_INFO;
        }

        // Comprobar si en los parametros viene la opcion de guardar
        if (typeof params !== 'undefined' && params.save === true) {
          const self = this;
          // Mostrar cuadro de dialogo para guardar procedimiento
          this.ogSweetAlert.swal({
              title: this.translate.instant('new_command_name'),
              type: 'info',
              html:
                '<form style="text-align: left; padding-left: 10px">\
                              <div class="form-group">\
                                   <label for="execute">' + this.translate.instant('execute') + '</label>\
                                   <div class="checkbox clip-check check-primary checkbox-inline">\
                                         <input id="execute" icheck checkbox-class="icheckbox_square-blue" radio-class="iradio_square-blue" type="checkbox" class="selection-checkbox" />\
                                     </div>\
                                 </div>\
                               <div class="form-group">\
                                   <label>' + this.translate.instant('title') + '</label>\
                                   <input type="text" class="form-control" id="command.title" />\
                               </div>\
                               <div class="form-group">\
                                   <label for="parameters">' + this.translate.instant('parameters') + '</label>\
                                   <div class="checkbox clip-check check-primary checkbox-inline">\
                                         <input id="parameters" icheck checkbox-class="icheckbox_square-blue" radio-class="iradio_square-blue" type="checkbox" class="selection-checkbox" />\
                                     </div>\
                                     <p class="help-block">' + this.translate.instant('help_command_parameters') + '</p>\
                                 </div>\
                             </form>',
              showCancelButton: true,
              confirmButtonColor: '#3c8dbc',
              confirmButtonText: this.translate.instant('done'),
              closeOnConfirm: true,
              preConfirm: () => {
                  return {
                    execute: (<HTMLInputElement>document.getElementById('execute')).checked,
                    command: {
                      title: (<HTMLInputElement>document.getElementById('command.title')).value,
                      parameters: (<HTMLInputElement>document.getElementById('parameters')).checked
                    }
                  };
              }
            }).then(
            function(response) {
              if (response.value) {
                response.value.command.script = self.execution.script;
                response.value.command.type = self.execution.type;
                self.commandService.create(response.value.command).subscribe(
                  (success) => {
                    // Si se seleccionó continuar con la ejecución
                    if (response.value.execute) {
                      self.loadClients();
                      self.sendCommand();
                    } else {
                      self.router.navigate(['app/commands']);
                    }
                  },
                  (error) => {
                    self.toaster.pop({type: 'error', title: 'error', body: error});
                  }
                );
              }
            });
        } else {
          this.loadClients();
          this.sendCommand();
        }
      }
    }

    loadClients() {
      if (this.ogCommonService.selectedClients) {
        this.execution.clients = _.join(Object.keys(this.ogCommonService.selectedClients));
      }
    }

}
