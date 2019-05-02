import { Component, OnInit } from '@angular/core';
import {OGCommandsService} from '../../../../service/og-commands.service';

@Component({
  selector: 'app-og-execute-command-options',
  templateUrl: './og-execute-command-options.component.html',
  styleUrls: ['./og-execute-command-options.component.css']
})
export class OgExecuteCommandOptionsComponent implements OnInit {

  constructor(public ogCommandsService: OGCommandsService) { }

  ngOnInit() {
  }

}
