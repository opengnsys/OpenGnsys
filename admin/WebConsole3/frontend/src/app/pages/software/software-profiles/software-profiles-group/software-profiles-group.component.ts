import {ComponentMetadata} from 'codelyzer/angular/metadata';
import {Component, Input} from '@angular/core';

@Component({
  selector: 'app-software-profiles-group',
  templateUrl: 'software-profiles-group.component.html'
})
export class SoftwareProfilesGroupComponent {
  @Input() content;

  constructor() {
    console.log(this.content);
  }
}
