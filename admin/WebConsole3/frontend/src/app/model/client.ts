import { Resource } from 'globunet-angular/core/models/api/resource';
import {Repository} from './repository';
import {HardwareProfile} from './hardware-profile';

export class Client extends Resource {
  public name: string;
  public mac: string;
  public ip: string;
  public serialno: string;
  public netiface: string;
  public netdriver: string;
  public repository: Repository;
  public hardwareProfile: HardwareProfile;
  public oglive: string;
  public netboot: string;
  public organizationalUnit: any;
  // Variables temporales para la vista, no vienen del servidor
  public status?: string;
  public selected?: boolean;
}
