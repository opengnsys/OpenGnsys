import { Resource } from 'globunet-angular/core/models/api/resource';
import {HardwareComponent} from './hardware-component';

export class HardwareProfile extends Resource {
  public description = '';
  public hardwares: HardwareComponent[] = [];
  /*public comments: string;*/
}
