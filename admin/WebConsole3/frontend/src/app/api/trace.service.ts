import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { Trace } from "../model/trace";
import { TraceSerializer } from "../serializer/trace.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class TraceService extends ResourceService<Trace> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"traces", new TraceSerializer());
	}

}
