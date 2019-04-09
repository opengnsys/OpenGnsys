import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { Engine } from "../model/engine";
import { EngineSerializer } from "../serializer/engine.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class EngineService extends ResourceService<Engine> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"core/engine", new EngineSerializer());
	}

}
