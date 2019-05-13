import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {HardwareProfile} from '../../../../model/hardware-profile';
import {HardwareProfileService} from '../../../../api/hardware-profile.service';
import {ToasterService} from '../../../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {OgSweetAlertService} from '../../../../service/og-sweet-alert.service';
import {Router} from '@angular/router';
import {environment} from '../../../../../environments/environment';
import {PartitionInfo} from '../../../../model/image';
import {Ng2TableActionComponent} from '../../../common/table-action/ng2-table-action.component';

@Component({
  selector: 'app-profiles-table',
  templateUrl: 'hardware-profiles-table.component.html'
})
export class HardwareProfilesTableComponent implements OnInit {
  @Input() profiles;
  windowsboots: any;
  tableOptions: any;

  public constructor(private router: Router, private hardwareProfileService: HardwareProfileService, private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private translate: TranslateService) {

  }
  ngOnInit(): void {
    this.windowsboots = environment.windowsboots;
    this.tableOptions = {
      override: false,
      buttons: [
        {
          action: 'edit'
        },
        {
          label: 'configure',
          handler: (profile) => this.goToEditProfile(profile),
          classes: 'btn-default'
        },
        {
          action: 'delete'
        }
      ]
    };
  }

  editHardwareProfile(hardwareProfile) {
    hardwareProfile.$$editing = true;
    hardwareProfile.$$tmpName = hardwareProfile.name;
    hardwareProfile.$$tmpDescription = hardwareProfile.description;
    hardwareProfile.$$tmpWindowsboot = hardwareProfile.windowsboot;
  }

  saveHardwareProfile(hardwareProfile) {
    hardwareProfile.$$editing = false;
    hardwareProfile.name = hardwareProfile.$$tmpName;
    hardwareProfile.description = hardwareProfile.$$tmpDescription;
    hardwareProfile.windowsboot = hardwareProfile.$$tmpWindowsboot;
    const hpCopy = Object.assign({}, hardwareProfile);
    // TODO - Llamar al servidor para guardar el cambio
    this.hardwareProfileService.update(hpCopy).subscribe(
      (response) => {
        this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_saved')});
      },
      (error) => {
        this.toaster.pop({type: 'error', title: 'error', body: error});
      }
    );
  }

  deleteHardwareProfile(hardwareProfile) {
    const self = this;

    this.ogSweetAlert.question(this.translate.instant('sure_to_delete') + '?', this.translate.instant('action_cannot_be_undone'),
      function(result) {
                  if (result.value === true) {
                    self.hardwareProfileService.delete(hardwareProfile.id).subscribe(
                      (response) => {
                        self.toaster.pop({type: 'success', title: self.translate.instant('success'), body: self.translate.instant('successfully_deleted')});
                        const index = self.profiles.indexOf(hardwareProfile);
                        if (index !== -1) {
                          self.profiles.splice(index, 1);
                        }
                      },
                      (error) => {
                        self.toaster.pop({type: 'error', title: self.translate.instant('error'), body: error});
                      }
                    );
                  }
                }
              );
  }

  goToEditProfile(profile: HardwareProfile) {
    this.router.navigate(['/app/hardware/profile', profile.id]).then(
      success => {
        console.log(success);
      },
      error => {
        console.log(error);
      }
    );
  }
}
