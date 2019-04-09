import { Resource } from 'globunet-angular/core/models/api/resource';

export class Menu extends Resource {
  public title: string;
  public resolution: string;
  public description: string;
  public comments: string;
  public publicColumns: number;
  public publicUrl: string;
  public privateColumns: number;
  public privateUrl: string;
}
