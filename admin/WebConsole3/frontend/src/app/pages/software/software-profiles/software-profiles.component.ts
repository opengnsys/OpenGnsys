import {Component, Input, OnInit} from '@angular/core';

@Component({
  selector: 'app-software-profiles',
  templateUrl: './software-profiles.component.html',
  styleUrls: ['./software-profiles.component.css']
})
export class SoftwareProfilesComponent implements OnInit {

  @Input() softwareProfileGroups;

  constructor() {
  }

  ngOnInit() {
    console.log(this.softwareProfileGroups);
  }

}
