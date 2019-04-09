import {Component, Input, OnInit} from '@angular/core';

@Component({
  selector: 'app-hardware-components',
  templateUrl: './hardware-components.component.html',
  styleUrls: ['./hardware-components.component.css']
})
export class HardwareComponentsComponent implements OnInit {
  @Input() hardwareTypes;
  @Input() hardwareComponentsGroups;
  constructor() { }

  ngOnInit() {
    console.log(this.hardwareComponentsGroups);
  }

}
