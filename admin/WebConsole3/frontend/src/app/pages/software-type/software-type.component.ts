import { Component } from '@angular/core';

import { SoftwareTypeService } from 'src/app/api/software-type.service';
import { SoftwareType } from 'src/app/model/software-type';

@Component({
  selector: 'software-type',
  templateUrl: './software-type.component.html',
  styleUrls: [ './software-type.component.scss' ]
})
export class SoftwareTypeComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public softwareTypeService: SoftwareTypeService) {
  }
  
}
