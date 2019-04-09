import { Component, OnInit } from '@angular/core';
import {OgCommonService} from '../../../../service/og-common.service';
import {OGCommandsService} from '../../../../service/og-commands.service';

@Component({
  selector: 'app-og-information-options',
  templateUrl: './og-information-options.component.html',
  styleUrls: ['./og-information-options.component.css']
})
export class OgInformationOptionsComponent implements OnInit {
  constructor(public ogCommonService: OgCommonService, public ogCommandsService: OGCommandsService) { }

  ngOnInit() {
  }

}
