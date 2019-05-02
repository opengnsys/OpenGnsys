import { Component, OnInit } from '@angular/core';
import {OgCommonService} from '../../../../service/og-common.service';
import {Client} from '../../../../model/client';

@Component({
  selector: 'app-og-selected-clients',
  templateUrl: './og-selected-clients.component.html',
  styleUrls: ['./og-selected-clients.component.css']
})
export class OgSelectedClientsComponent implements OnInit {
  public selectedClients: Client[];

  constructor(public ogCommonService: OgCommonService) {
    this.selectedClients = [];
  }

  ngOnInit() {
    this.selectedClients = this.ogCommonService.selectedClients;
  }

  getClientInfo(c: Client) {
    console.log(c);
  }
}
