import { Component } from '@angular/core';

import { SoftwareComponentService } from 'src/app/api/software-component.service';
import { SoftwareComponent } from 'src/app/model/software-component';
import {Observable} from 'rxjs';
import {ToasterService} from '../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {Router} from '@angular/router';
import {SoftwareType} from '../../model/software-type';
import {OGCommandsService} from '../../service/og-commands.service';
import {OgCommonService} from '../../service/og-common.service';

@Component({
  selector: 'app-software-component',
  templateUrl: './software-component.component.html',
  styleUrls: [ './software-component.component.scss' ]
})
export class SoftwareComponentComponent {
  // this tells the tabs component which Pages
  public softwareComponent: SoftwareComponent;
  formType: any;
  softwareTypes: SoftwareType[] = [];
  // should be each tab's root Page
  constructor(public softwareComponentService: SoftwareComponentService, private ogCommonService: OgCommonService, private toaster: ToasterService, private translate: TranslateService, private router: Router) {
    this.softwareComponent = new SoftwareComponent();
    this.ogCommonService.loadEngineConfig().subscribe(
        (data) => {
          this.formType = [
            {
              field: 'description',
              name: 'description',
              label: 'description',
              type: 'textarea'
            },
            {
              field: 'type',
              name: 'type',
              label: 'type',
              type: 'select',
              options: {
                items: data.constants.softwareTypes,
                label: 'name',
                value: 'id'
              }
            }
          ];
        });
  }

  save() {
    let request: Observable<SoftwareComponent>;
    if (this.softwareComponent.id !== 0) {
      request = this.softwareComponentService.update(this.softwareComponent);
    } else {
      request = this.softwareComponentService.create(this.softwareComponent);
    }

    request.subscribe(
        (response) => {
          this.toaster.pop({type: this.translate.instant('success'), title: this.translate.instant('success'), body: this.translate.instant('successfully_saved')});
          this.router.navigate(['/app/software']);
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
    );
  }
}
