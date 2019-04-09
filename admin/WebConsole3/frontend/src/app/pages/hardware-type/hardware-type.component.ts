import { Component } from '@angular/core';

import { HardwareTypeService } from 'src/app/api/hardware-type.service';
import { HardwareType } from 'src/app/model/hardware-type';

@Component({
  selector: 'hardware-type',
  templateUrl: './hardware-type.component.html',
  styleUrls: [ './hardware-type.component.scss' ]
})
export class HardwareTypeComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public hardwareTypeService: HardwareTypeService) {
  }
  
}
