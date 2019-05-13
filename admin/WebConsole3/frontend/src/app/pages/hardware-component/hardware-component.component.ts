import { Component } from '@angular/core';

import { HardwareComponentService } from 'src/app/api/hardware-component.service';
import { HardwareComponent } from 'src/app/model/hardware-component';
import {Observable} from 'rxjs';
import {ToasterService} from '../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {Router} from '@angular/router';

@Component({
  selector: 'app-hardware-component',
  templateUrl: './hardware-component.component.html',
  styleUrls: [ './hardware-component.component.scss' ]
})
export class HardwareComponentComponent {
  // this tells the tabs component which Pages
  public hardwareComponent: HardwareComponent;
  formType: any;
  // should be each tab's root Page
  constructor(public hardwareComponentService: HardwareComponentService, private toaster: ToasterService, private translate: TranslateService, private router: Router) {
    this.hardwareComponent = new HardwareComponent();
    this.formType = [{
      field: 'description',
      name: 'description',
      label: 'description',
      type: 'textarea'
    }];
  }

  save() {
    let request: Observable<HardwareComponent>;
    if (this.hardwareComponent.id !== 0) {
      request = this.hardwareComponentService.update(this.hardwareComponent);
    } else {
      request = this.hardwareComponentService.create(this.hardwareComponent);
    }

    request.subscribe(
        (response) => {
          this.toaster.pop({type: this.translate.instant('success'), title: this.translate.instant('success'), body: this.translate.instant('successfully_saved')});
          this.router.navigate(['/app/hardware']);
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
    );
  }
}
