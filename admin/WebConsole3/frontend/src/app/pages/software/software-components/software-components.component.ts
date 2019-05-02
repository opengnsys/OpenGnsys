import {Component, Input, OnInit} from '@angular/core';

@Component({
  selector: 'app-software-components',
  templateUrl: './software-components.component.html',
  styleUrls: ['./software-components.component.css']
})
export class SoftwareComponentsComponent implements OnInit {
  @Input() softwareTypes;
  @Input() softwareComponentsGroups;
  constructor() { }

  ngOnInit() {
    console.log(this.softwareComponentsGroups);
  }

}
