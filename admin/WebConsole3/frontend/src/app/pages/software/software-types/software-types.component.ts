import {Component, Input, OnInit} from '@angular/core';

@Component({
  selector: 'app-software-types',
  templateUrl: './software-types.component.html',
  styleUrls: ['./software-types.component.css']
})
export class SoftwareTypesComponent implements OnInit {
  @Input() softwareTypes;

  constructor() { }

  ngOnInit() {
  }

  editSoftwareType(softwareType) {
    softwareType.$$editing = true;
    softwareType.$$tmpName = softwareType.name;
  }
  saveSoftwareType(softwareType) {
    softwareType.$$editing = false;
    softwareType.name = softwareType.$$tmpName;
    // TODO - Llamar al servidor para guardar el cambio
  }

}
