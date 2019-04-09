import {Component, Input, OnInit} from '@angular/core';
import {ToasterService} from '../../../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {OgSweetAlertService} from '../../../../service/og-sweet-alert.service';
import {Router} from '@angular/router';
import {HardwareComponent} from '../../../../model/hardware-component';
import {HardwareComponentService} from '../../../../api/hardware-component.service';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'app-hardware-components-table',
  templateUrl: 'hardware-components-table.component.html'
})
export class HardwareComponentsTableComponent implements OnInit {
  @Input() components;
  @Input() hardwareTypes;
  public tableOptions: { buttons: ({ action: string } | { handler: (profile) => any; classes: string; label: string })[]; override: boolean };

  public constructor(private router: Router, private hardwareComponentService: HardwareComponentService, private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private translate: TranslateService) {
  }

  ngOnInit(): void {
    this.tableOptions = {
      override: false,
      buttons: [
        {
          action: 'edit'
        },
        {
          action: 'delete'
        }
      ]
    };
  }

  editHardwareComponent(hardwareComponent) {
    hardwareComponent.$$editing = true;
    hardwareComponent.$$tmpDescription = hardwareComponent.description;
    hardwareComponent.$$tmpType = hardwareComponent.type;
  }
  saveHardwareComponent(hardwareComponent: any) {
    hardwareComponent.$$editing = false;
    hardwareComponent.description = hardwareComponent.$$tmpDescription;
    hardwareComponent.type = hardwareComponent.$$tmpType;
    const hcCopy = Object.assign({}, hardwareComponent);

    // TODO - Llamar al servidor para guardar el cambio
    this.hardwareComponentService.update(hcCopy).subscribe(
      (response) => {
        this.toaster.pop({type: 'success', title: this.translate.instant('success'), body: this.translate.instant('successfully_saved')});
      },
      (error) => {
        this.toaster.pop({type: 'error', title: this.translate.instant('error'), body: error});
      }
    );
  }

  deleteHardwareComponent(hardwareComponent: HardwareComponent) {
    this.ogSweetAlert.swal({
      title: this.translate.instant('sure_to_delete') + '?',
      text: this.translate.instant('action_cannot_be_undone'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3c8dbc',
      confirmButtonText: this.translate.instant('yes_delete'),
      closeOnConfirm: true}).then(
      function(result) {
        if (result === true) {
          this.hardwareComponentService.delete(hardwareComponent.id).subscribe(
            (response) => {
              this.toaster.pop({type: 'success', title: this.translate.instant('success'), body: this.translate.instant('successfully_deleted')});
              const index = this.hardwareComponentsGroups[0].components.indexOf(hardwareComponent);
              if (index !== -1) {
                this.hardwareComponentsGroups[0].components.splice(index, 1);
              }
            },
            (error) => {
              this.toaster.pop({type: 'error', title: this.translate.instant('error'), body: error});
            }
          );
        }
      });
  }
}
