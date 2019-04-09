import {Component, Input, OnInit} from '@angular/core';

@Component({
  selector: 'app-hardware-profiles',
  templateUrl: './hardware-profiles.component.html',
  styleUrls: ['./hardware-profiles.component.css']
})
export class HardwareProfilesComponent implements OnInit {

  @Input() hardwareProfileGroups;

  constructor() {
  }

  ngOnInit() {
    console.log(this.hardwareProfileGroups);
  }

}
