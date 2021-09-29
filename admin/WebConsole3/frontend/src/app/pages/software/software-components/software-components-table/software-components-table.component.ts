import {Component, Input, OnInit} from '@angular/core';
import {ToasterService} from '../../../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {OgSweetAlertService} from '../../../../service/og-sweet-alert.service';
import {Router} from '@angular/router';
import {SoftwareComponent} from '../../../../model/software-component';
import {SoftwareComponentService} from '../../../../api/software-component.service';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'app-software-components-table',
  templateUrl: 'software-components-table.component.html'
})
export class SoftwareComponentsTableComponent implements OnInit {
  @Input() components;
  @Input() softwareTypes;
  public tableOptions: { buttons: ({ action: string } | { handler: (profile) => any; classes: string; label: string })[]; override: boolean };

  public constructor(private router: Router, private softwareComponentService: SoftwareComponentService, private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private translate: TranslateService) {
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

  editSoftwareComponent(softwareComponent) {
    softwareComponent.$$editing = true;
    softwareComponent.$$tmpDescription = softwareComponent.description;
    softwareComponent.$$tmpType = softwareComponent.type;
  }
  saveSoftwareComponent(softwareComponent: any) {
    softwareComponent.$$editing = false;
    softwareComponent.description = softwareComponent.$$tmpDescription;
    softwareComponent.type = softwareComponent.$$tmpType;
    const hcCopy = Object.assign({}, softwareComponent);

    // TODO - Llamar al servidor para guardar el cambio
    this.softwareComponentService.update(hcCopy).subscribe(
      (response) => {
        this.toaster.pop({type: 'success', title: this.translate.instant('success'), body: this.translate.instant('successfully_saved')});
      },
      (error) => {
        this.toaster.pop({type: 'error', title: this.translate.instant('error'), body: error});
      }
    );
  }

  deleteSoftwareComponent(softwareComponent: SoftwareComponent) {
    var self = this;
    this.ogSweetAlert.swal({
      title: this.translate.instant('sure_to_delete') + '?',
      text: this.translate.instant('action_cannot_be_undone'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3c8dbc',
      confirmButtonText: this.translate.instant('yes_delete'),
      closeOnConfirm: true}).then(
      function(result) {
        if (result.value === true) {
          self.softwareComponentService.delete(softwareComponent.id).subscribe(
            (response) => {
              self.toaster.pop({type: 'success', title: self.translate.instant('success'), body: self.translate.instant('successfully_deleted')});
              const index = self.components.indexOf(softwareComponent);
              if (index !== -1) {
                self.components.splice(index, 1);
              }
            },
            (error) => {
              self.toaster.pop({type: 'error', title: self.translate.instant('error'), body: error});
            }
          );
        }
      });
  }
}
