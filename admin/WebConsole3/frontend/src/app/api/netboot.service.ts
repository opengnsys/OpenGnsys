import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';

import {environment} from '../../environments/environment';
import {Netboot} from '../model/netboot';
import {NetbootSerializer} from '../serializer/netboot.serializer';

import {ResourceService} from 'globunet-angular/core/providers/api/resource.service';
import {Observable} from 'rxjs';


@Injectable({
    providedIn: 'root'
})
export class NetbootService extends ResourceService<Netboot> {

    constructor(http: HttpClient) {
        super(http, environment.API_URL, 'netboots', new NetbootSerializer());
    }

    updateFiles(assignedNetboots: {}): Observable<any> {
        return this.httpClient.post(this.url + '/netboots/clients', JSON.stringify(assignedNetboots));
    }
}
