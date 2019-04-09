import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { Status } from "../model/status";
import { StatusSerializer } from "../serializer/status.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class StatusService extends ResourceService<Status> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"core/status", new StatusSerializer());
	}

}
