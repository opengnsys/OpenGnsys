import {Component, Input, OnInit} from '@angular/core';

@Component({
  selector: 'app-hardware-types',
  templateUrl: './hardware-types.component.html',
  styleUrls: ['./hardware-types.component.css']
})
export class HardwareTypesComponent implements OnInit {
  @Input() hardwareTypes;

  constructor() { }

  ngOnInit() {
  }
}
