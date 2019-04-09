import { Component } from '@angular/core';

import { HardwareComponentService } from 'src/app/api/hardware-component.service';
import { HardwareComponent } from 'src/app/model/hardware-component';

@Component({
  selector: 'app-hardware-component',
  templateUrl: './hardware-component.component.html',
  styleUrls: [ './hardware-component.component.scss' ]
})
export class HardwareComponentComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public hardwareComponentService: HardwareComponentService) {
  }
  
}
