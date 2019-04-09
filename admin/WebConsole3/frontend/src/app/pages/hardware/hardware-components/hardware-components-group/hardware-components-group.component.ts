import {Component, Input} from '@angular/core';

@Component({
  selector: 'app-hardware-component-group',
  templateUrl: 'hardware-components-group.component.html'
})
export class HardwareComponentsGroupComponent {
  @Input() content;
  @Input() hardwareTypes;
}
