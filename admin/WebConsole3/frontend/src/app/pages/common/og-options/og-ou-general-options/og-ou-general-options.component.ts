import {Component, Input, OnInit} from '@angular/core';
import {OgCommonService} from '../../../../service/og-common.service';
import {forkJoin} from 'rxjs';
import {OgSweetAlertService} from '../../../../service/og-sweet-alert.service';
import {TranslateService} from '@ngx-translate/core';
import {ToasterService} from '../../../../service/toaster.service';
import {ClientService} from '../../../../api/client.service';

@Component({
  selector: 'app-og-ou-general-options',
  templateUrl: './og-ou-general-options.component.html',
  styleUrls: ['./og-ou-general-options.component.css']
})
export class OgOuGeneralOptionsComponent implements OnInit {

  constructor(private clientService: ClientService,public ogCommonService: OgCommonService, private ogSweetAlert: OgSweetAlertService, private translate: TranslateService, private toaster: ToasterService) {
  }

  ngOnInit() {
  }

  moveClientsToOu() {
    if (this.ogCommonService.movingClients === true) {
      this.ogCommonService.movingClients = false;
    } else {
      // Si existe una operacion de movimiento de Ou se cancela
      if (this.ogCommonService.movingOu != null) {
        this.ogCommonService.selectForMove(this.ogCommonService.movingOu);
      }
      if (this.ogCommonService.movingClients === false) {
        this.ogCommonService.movingClients = true;
      } else {
        this.ogSweetAlert.info('opengnsys_info', this.translate.instant('you_must_to_select_any_clients'));
      }
    }
  }

  deleteSelectedClients() {
  const self = this;
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
        if (response.value === true) {
          const clientIds = Object.keys(self.ogCommonService.selectedClients);
          let cId: any;
          const promises = [];
          for (let i = 0; i < clientIds.length; i++) {
            cId = clientIds[i];
            promises.push(self.clientService.delete(cId));
          }
          forkJoin(promises).subscribe(
            (success) => {
              /*
              for (let i = 0; i < clientIds.length; i++) {
                cId = clientIds[i];
                self.deleteClientFromOu(self.ous, self.ogCommonService.selectedClients[cId]);
              }
              /**/
              self.toaster.pop({type: 'success', title: 'success', body: 'Successfully deleted'});
              self.ogCommonService.selectedClients = [];
            },
            (error) => {
              self.toaster.pop({type: 'error', title: 'error', body: error});
            }
          );
        }

      },
      function(cancel) {

      }
    );
  }
}
