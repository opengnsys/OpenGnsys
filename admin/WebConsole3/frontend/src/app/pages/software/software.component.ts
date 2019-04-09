import { Component } from '@angular/core';

import { SoftwareService } from 'src/app/api/software.service';
import { Software } from 'src/app/model/software';

@Component({
  selector: 'software',
  templateUrl: './software.component.html',
  styleUrls: [ './software.component.scss' ]
})
export class SoftwareComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public softwareService: SoftwareService) {
  }
  
}
