import { Resource } from 'globunet-angular/core/models/api/resource';

export class Repository extends Resource {
  name: string;
  ip: string;
  password: string;
  description: string;
  info: any;

  constructor() {
    super();
    this.name = '';
    this.ip = '';
    this.password = '';
    this.description = '';
  }
}
