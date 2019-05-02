import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {SoftwareProfile} from '../../../../model/software-profile';
import {SoftwareProfileService} from '../../../../api/software-profile.service';
import {ToasterService} from '../../../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {OgSweetAlertService} from '../../../../service/og-sweet-alert.service';
import {Router} from '@angular/router';
import {environment} from '../../../../../environments/environment';


@Component({
  selector: 'app-software-profiles-table',
  templateUrl: 'software-profiles-table.component.html'
})
export class SoftwareProfilesTableComponent implements OnInit {
  @Input() profiles;
  windowsboots: any;
  tableOptions: any;

  public constructor(private router: Router, private softwareProfileService: SoftwareProfileService, private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private translate: TranslateService) {

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

  editSoftwareProfile(softwareProfile) {
    softwareProfile.$$editing = true;
    softwareProfile.$$tmpName = softwareProfile.name;
    softwareProfile.$$tmpDescription = softwareProfile.description;
    softwareProfile.$$tmpWindowsboot = softwareProfile.windowsboot;
  }

  saveSoftwareProfile(softwareProfile) {
    softwareProfile.$$editing = false;
    softwareProfile.name = softwareProfile.$$tmpName;
    softwareProfile.description = softwareProfile.$$tmpDescription;
    softwareProfile.windowsboot = softwareProfile.$$tmpWindowsboot;
    const hpCopy = Object.assign({}, softwareProfile);
    // TODO - Llamar al servidor para guardar el cambio
    this.softwareProfileService.update(hpCopy).subscribe(
      (response) => {
        this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_saved')});
      },
      (error) => {
        this.toaster.pop({type: 'error', title: 'error', body: error});
      }
    );
  }

  deleteSoftwareProfile(softwareProfile) {
    const self = this;

    this.ogSweetAlert.question(this.translate.instant('sure_to_delete') + '?', this.translate.instant('action_cannot_be_undone'),
      function(result) {
                  if (result.value === true) {
                    self.softwareProfileService.delete(softwareProfile.id).subscribe(
                      (response) => {
                        self.toaster.pop({type: 'success', title: self.translate.instant('success'), body: self.translate.instant('successfully_deleted')});
                        const index = self.profiles.indexOf(softwareProfile);
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

  goToEditProfile(profile: SoftwareProfile) {
    this.router.navigate(['/app/software/profile', profile.id]).then(
      success => {
        console.log(success);
      },
      error => {
        console.log(error);
      }
    );
  }
}
