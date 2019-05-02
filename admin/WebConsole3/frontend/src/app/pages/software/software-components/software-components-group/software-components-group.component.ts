import {Component, Input} from '@angular/core';

@Component({
  selector: 'app-software-component-group',
  templateUrl: 'software-components-group.component.html'
})
export class SoftwareComponentsGroupComponent {
  @Input() content;
  @Input() softwareTypes;
}
