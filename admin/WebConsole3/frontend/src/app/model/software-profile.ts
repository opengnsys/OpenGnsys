import { Resource } from 'globunet-angular/core/models/api/resource';
import {SoftwareComponent} from './software-component';

export class SoftwareProfile extends Resource {
  description = '';
  comments = '';
  public softwares: SoftwareComponent[] = [];
}
