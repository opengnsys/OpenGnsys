import {ComponentMetadata} from 'codelyzer/angular/metadata';
import {Component, Input} from '@angular/core';

@Component({
  selector: 'app-profiles-group',
  templateUrl: 'hardware-profiles-group.component.html'
})
export class HardwareProfilesGroupComponent {
  @Input() content;

  constructor() {
    console.log(this.content);
  }
}
