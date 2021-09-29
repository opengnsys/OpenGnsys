import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';

import {environment} from '../../environments/environment';
import {Command} from '../model/command';
import {CommandSerializer} from '../serializer/command.serializer';

import {ResourceService} from 'globunet-angular/core/providers/api/resource.service';
import {Observable} from 'rxjs';


@Injectable({
    providedIn: 'root'
})
export class CommandService extends ResourceService<Command> {

    constructor(http: HttpClient) {
        super(http, environment.API_URL, 'commands', new CommandSerializer());
    }

    execute(params): Observable<any> {
        const url = this.url + '/commands/execute';
        return this.httpClient.post(url, params);
    }

}
