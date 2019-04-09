import { Component } from '@angular/core';

import { StatusService } from 'src/app/api/status.service';
import { Status } from 'src/app/model/status';

@Component({
  selector: 'status',
  templateUrl: './status.component.html',
  styleUrls: [ './status.component.scss' ]
})
export class StatusComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public statusService: StatusService) {
  }
  
}
