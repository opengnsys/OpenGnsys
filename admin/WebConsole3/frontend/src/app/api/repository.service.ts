import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { Repository } from '../model/repository';
import { RepositorySerializer } from '../serializer/repository.serializer';

import {ResourceService} from 'globunet-angular/core/providers/api/resource.service';
import {Observable} from 'rxjs';


@Injectable({
  providedIn: 'root'
})
export class RepositoryService extends ResourceService<Repository> {

	constructor(http: HttpClient) {
		super(http, environment.API_URL, 'repositories', new RepositorySerializer());
	}

  getInfo(repository: Repository) {
    const url = 'https://' + repository.ip + '/' + environment.BASE_DIR + environment.API_BASE_URL;
    return this.httpClient.get(url, {headers: {
      Authorization: repository.randomId
      }});
  }
}
