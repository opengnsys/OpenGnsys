import {Component, OnInit} from '@angular/core';

import { NetbootService } from 'src/app/api/netboot.service';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {ToasterService} from '../../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {ActivatedRoute, Router} from '@angular/router';
import {OgCommonService} from '../../../service/og-common.service';
import {User} from '../../../model/user';
import {AuthModule} from 'globunet-angular/core';

@Component({
  selector: 'app-netboot',
  templateUrl: './netboot-clients.component.html',
  styleUrls: [ './netboot-clients.component.scss' ]
})
export class NetbootClientsComponent implements OnInit {
  multiSelection = false;
  netboots = [];
  uefiNetboots = [];
  biosNetboots = [];
  assignedNetboots = {};
  selectionForMove = [];
  rangeSelection = {
    start: -1,
    end: -1
  };
  user: User;
  netbootTypes = [];
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public netbootService: NetbootService, private authModule: AuthModule, public ogCommonService: OgCommonService, private router: Router, private activatedRoute: ActivatedRoute,  private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private  translate: TranslateService) {
    this.user = this.authModule.getLoggedUser();
  }



  ngOnInit() {
    if (this.user && this.ogCommonService.selectedClients) {
      this.netbootService.list().subscribe(
          (result) => {
            this.netboots = result;
            const self = this;
            this.netboots.forEach((netboot) => {
              if (netboot.type && netboot.type === 'uefi') {
                self.uefiNetboots.push(netboot);
              } else {
                self.biosNetboots.push(netboot);
              }
            });
            if (this.biosNetboots.length > 0) {
              this.netbootTypes.push(this.biosNetboots);
            }
            if (this.uefiNetboots.length > 0) {
              this.netbootTypes.push(this.uefiNetboots);
            }

            const clientIds = Object.keys(this.ogCommonService.selectedClients);
            // Recorrer todos los clientes y formar los grupos según el partitionCode de sus particiones, deben coincidir todos
            for (let index = 0; index < clientIds.length; index++) {
              // Generamos una clave usando disco-particion-code para comparar
              const client = this.ogCommonService.selectedClients[clientIds[index]];
              if (!client.netboot) {
                client.netboot = this.netboots[0];
              }
              if (!this.assignedNetboots[client.netboot.id]) {
                this.assignedNetboots[client.netboot.id] = [];
              }
              this.assignedNetboots[client.netboot.id].push(client.id);
            }
          },
          (error) => {
            this.toaster.pop({type: 'error', title: 'error', body: error});
          }
      );
    } else {
      // TODO - dar error?
      this.ogSweetAlert.error(this.translate.instant('opengnsys_error'), this.translate.instant('not_clients_selected'));
      this.router.navigate(['app.ous']);
    }
  }

  moveSelectionToNetboot(id) {
    // La selección está formada por dos parametros separados por "_", netbootId y clientId
    for (let i = 0; i < this.selectionForMove.length; i++) {
      const ids = this.selectionForMove[i].split('_');
      const netbootId = parseInt(ids[0]);
      const clientId = parseInt(ids[1]);
      // Eliminar el id del cliente del netboot origen
      const index = this.assignedNetboots[netbootId].indexOf(clientId);
      this.assignedNetboots[netbootId].splice(index, 1);
      // Se introduce en el netboot seleccionado
      if (!this.assignedNetboots[id]) {
        this.assignedNetboots[id] = [];
      }
      this.assignedNetboots[id].push(clientId);
    }
    // Reiniciar los rangos seleccionados por si los hubiese
    this.rangeSelection.start = -1;
    this.rangeSelection.end = -1;

  }

  checkSelection(netbootId, clientId) {
    if (this.multiSelection === true) {
      if (this.rangeSelection.start === -1) {
        this.rangeSelection.start = clientId;
      } else if (this.rangeSelection.end === -1) {
        this.rangeSelection.end = clientId;
        // Realizar la seleccion
        let start = this.assignedNetboots[netbootId].indexOf(this.rangeSelection.start);
        let end = this.assignedNetboots[netbootId].indexOf(this.rangeSelection.end);
        if (end < start) {
          const tmp = start;
          start = end;
          end = tmp;
        }
        this.selectionForMove = [];
        for (let index = start; index <= end; index++) {
          this.selectionForMove.push(netbootId +  '_' + this.assignedNetboots[netbootId][index]);
        }
      } else {
        this.rangeSelection.start = clientId;
        this.rangeSelection.end = -1;
      }
    } else {
      this.rangeSelection.start = -1;
      this.rangeSelection.end = -1;
    }

  }

  save(Form) {
    this.netbootService.updateFiles(this.assignedNetboots).subscribe(
        (response) => {
          this.toaster.pop({type: 'success', title: this.translate.instant('success'), body: this.translate.instant('successfully_updated')});
          // borrar selección una vez aplicado el netboot
          this.ogCommonService.selectedClients = {};
          this.router.navigate(['/app/ous']);
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
    );
  }
}
