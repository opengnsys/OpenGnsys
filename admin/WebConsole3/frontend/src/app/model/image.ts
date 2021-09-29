import { Resource } from 'globunet-angular/core/models/api/resource';
import {Repository} from './repository';
import {Client} from './client';
import {SoftwareProfile} from './software-profile';

export class PartitionInfo {
  diskNumber: number;
  partitionNumber: number;
  partitionCode: string;
  filesystem: string;
  osName: string;
  type: string;
}

export class Image extends Resource {
  public canonicalName = '';
  public repository: Repository = new Repository();
  public description = '';
  public comments = '';
  public revision: string;
  public createdAt: Date;
  public softwareProfile: SoftwareProfile;
  public partitionInfo: PartitionInfo;
  public client?: Client;
}
