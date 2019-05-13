import { Resource } from 'globunet-angular/core/models/api/resource';

export class Excecution {
  script = '';
  clients = '';
  type = '';
}

export class Command extends Resource {
  public title = '';
  public script = '';
  public parameters = false;

}
