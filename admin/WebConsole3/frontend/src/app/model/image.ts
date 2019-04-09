import { Resource } from 'globunet-angular/core/models/api/resource';
import {Repository} from './repository';

export class PartitionInfo {
  numDisk: number;
  numPartition: number;
  partitionCode: string;
  filesystem: string;
  osName: string;
}

export class Image extends Resource {
  public canonicalName: string = '';
  public repository: Repository = new Repository();
  public description: string = '';
  public comments: string = '';
  public revision: string;
  public createdAt: Date;
  public partitionInfo: PartitionInfo;
}
