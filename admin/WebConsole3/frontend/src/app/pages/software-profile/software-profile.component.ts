import { Component } from '@angular/core';

import { SoftwareProfileService } from 'src/app/api/software-profile.service';
import { SoftwareProfile } from 'src/app/model/software-profile';

@Component({
  selector: 'software-profile',
  templateUrl: './software-profile.component.html',
  styleUrls: [ './software-profile.component.scss' ]
})
export class SoftwareProfileComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public softwareProfileService: SoftwareProfileService) {
  }
  
}
