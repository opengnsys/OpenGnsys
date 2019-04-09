import { Component, OnInit } from '@angular/core';
import {OgCommonService} from '../../../../service/og-common.service';
import {OGCommandsService} from '../../../../service/og-commands.service';

@Component({
  selector: 'app-og-commands-options',
  templateUrl: './og-commands-options.component.html',
  styleUrls: ['./og-commands-options.component.css']
})
export class OgCommandsOptionsComponent implements OnInit {

  constructor(public ogCommonService: OgCommonService, public ogCommandsService: OGCommandsService) { }

  ngOnInit() {
  }

}
