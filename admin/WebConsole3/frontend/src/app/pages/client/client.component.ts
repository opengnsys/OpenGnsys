import { Component } from '@angular/core';

import { ClientService } from 'src/app/api/client.service';
import { Client } from 'src/app/model/client';

@Component({
  selector: 'client',
  templateUrl: './client.component.html',
  styleUrls: [ './client.component.scss' ]
})
export class ClientComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public clientService: ClientService) {
  }
  
}
