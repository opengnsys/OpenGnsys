import { Resource } from 'globunet-angular/core/models/api/resource';
import {Client} from './client';

export class OrganizationalUnit extends Resource {
  name: string;
  capacity: number;
  defclients: number;
  inremotepc: boolean;
  projector: boolean;
  board: boolean;
  description: string;
  routerip: string;
  netmask: string;
  dns: string;
  ntp: string;
  proxyurl: string;
  mcastmode: string[] = ['full-duplex', 'half-duplex'];
  mcastip: string;
  mcastport: number;
  mcastspeed: number;
  p2pmode: string;
  p2ptime: number;
  parent: string;
  clients?: Client[];
}
