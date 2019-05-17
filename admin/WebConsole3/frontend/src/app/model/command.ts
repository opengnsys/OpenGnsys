import { Resource } from 'globunet-angular/core/models/api/resource';

export class Execution {
  script = '';
  clients = '';
  type = '';
  sendConfig =  false;
}

export class Command extends Resource {
  public title = '';
  public script = '';
  public parameters = false;

}
