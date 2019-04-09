import {Component, EventEmitter, Input, Output} from '@angular/core';
import {Client} from '../../../model/client';
import {ClientService} from '../../../api/client.service';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {ToasterService} from '../../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {OrganizationalUnit} from '../../../model/organizational-unit';
import {OgCommonService} from '../../../service/og-common.service';

@Component({
  selector: 'app-ou-client-component',
  templateUrl: 'ou-client.component.html'
})
export class OuClientComponent {
  private _ou: OrganizationalUnit;
  public clients: Client[];
  @Input()
  set ou(ou) {
    this._ou = ou;
    this.clients = ou.clients;
  }
  get ou() {
    return this._ou;
  }
  @Input() selectedStatus;
  @Input() clientStatus;
  @Input() showGrid: boolean;

  @Output() clientSelected = new EventEmitter<Client>();


  constructor( private clientService: ClientService,
               private ogSweetAlert: OgSweetAlertService,
               private toaster: ToasterService,
               private translate: TranslateService,
               private ogCommonService: OgCommonService) {}

  selectClient(client) {
    this.ogCommonService.selectClient(client, this.ou);
    this.clientSelected.emit(client);
  }

  deleteClient(client) {
    this.ogSweetAlert.swal(
      {
        title: this.translate.instant('sure_to_delete') + '?',
        text: this.translate.instant('action_cannot_be_undone'),
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: this.translate.instant('yes_delete')

      }).then(
      function(response) {
        if (response === true) {
          this.clientService.delete(client.id).then(
            function(success) {
              // Lo borramos de la unidad organizativa
              const index = this.ou.clients.indexOf(client);
              if (index !== -1) {
                this.ou.clients.splice(index, 1);
              }
              this.toaster.pop({type: 'success', title: 'success', body: 'Successfully deleted'});
            },
            function(error) {
              this.toaster.pop({type: 'error', title: 'error', body: error});
            }
          );
        }

      },
      function(cancel) {

      }
    );
  }

  mustShow(client) {
    let result = true;
    /*
    const status = this.clientStatus[client.id];
    if (status) {
      result = this.selectedStatus[status];
    }
    /**/

    return result;
  }
}
