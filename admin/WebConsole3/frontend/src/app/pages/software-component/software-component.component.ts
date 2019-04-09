import { Component } from '@angular/core';

import { SoftwareComponentService } from 'src/app/api/software-component.service';
import { SoftwareComponent } from 'src/app/model/software-component';

@Component({
  selector: 'software-component',
  templateUrl: './software-component.component.html',
  styleUrls: [ './software-component.component.scss' ]
})
export class SoftwareComponentComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public softwareComponentService: SoftwareComponentService) {
  }
  
}
