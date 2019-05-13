import { Resource } from 'globunet-angular/core/models/api/resource';

export class Menu extends Resource {
  public title = '';
  public resolution = '';
  public description = '';
  public comments = '';
  public publicUrl = ''
  public privateUrl = '';
  public publicColumns: number;
  public privateColumns: number;
}
