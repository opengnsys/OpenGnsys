import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { Partition } from '../model/client';

import {ResourceService} from 'globunet-angular/core/providers/api/resource.service';
import {PartitionSerializer} from '../serializer/partition.serializer';


@Injectable({
  providedIn: 'root'
})
export class PartitionService extends ResourceService<Partition> {

  constructor(http: HttpClient) {
    super(http, environment.API_URL, 'partitions', new PartitionSerializer());
  }
}
