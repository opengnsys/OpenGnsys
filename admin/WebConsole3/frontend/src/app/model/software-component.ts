import { Resource } from 'globunet-angular/core/models/api/resource';
import {SoftwareType} from './software-type';

export class SoftwareComponent extends Resource {
  description: string;
  type: SoftwareType;
}
