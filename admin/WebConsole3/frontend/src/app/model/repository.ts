import { Resource } from 'globunet-angular/core/models/api/resource';

export class Repository extends Resource {
  name: string;
  ip: string;
  port: number;
  password: string;
  configurationpath: string;
  adminpath: string;
  pxepath: string;
  description: string;
  info: any;

  constructor() {
    super();
    this.name = '';
    this.ip = '';
    this.port = 0;
    this.password = '';
    this.configurationpath = '';
    this.adminpath = '';
    this.pxepath = '';
    this.description = '';
  }
}
