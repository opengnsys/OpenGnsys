import { Resource } from 'globunet-angular/core/models/api/resource';
import {Repository} from './repository';
import {HardwareProfile} from './hardware-profile';
import {Netboot} from './netboot';

export class Client extends Resource {
  public name = '';
  public mac = '';
  public ip = '';
  public serialno = '';
  public netiface = '';
  public netdriver = '';
  public repository: Repository = null;
  public hardwareProfile: HardwareProfile = null;
  public oglive = null;
  public netboot: Netboot = null;
  public organizationalUnit: number;
  // Variables temporales para la vista, no vienen del servidor
  public status?: string;
  public selected?: boolean;

}
