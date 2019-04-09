import {ComponentMetadata} from 'codelyzer/angular/metadata';
import {Component, Input} from '@angular/core';

@Component({
  selector: 'app-profiles-group',
  templateUrl: 'profiles-group.component.html'
})
export class ProfilesGroupComponent {
  @Input() content;

  constructor() {
    console.log(this.content);
  }
}
