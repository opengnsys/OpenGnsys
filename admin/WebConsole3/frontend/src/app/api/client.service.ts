import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { Client } from '../model/client';
import { ClientSerializer } from '../serializer/client.serializer';

import {ResourceService} from 'globunet-angular/core/providers/api/resource.service';


@Injectable({
  providedIn: 'root'
})
export class ClientService extends ResourceService<Client> {

  constructor(http: HttpClient) {
    super(http, environment.API_URL, 'clients', new ClientSerializer());
  }

  statusAll(ouId) {
    return this.httpClient.get(this.url + '/' + this.endpoint + '/status.json?ou=' + ouId);
  }
}
