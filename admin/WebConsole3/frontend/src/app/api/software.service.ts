import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { Software } from "../model/software";
import { SoftwareSerializer } from "../serializer/software.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class SoftwareService extends ResourceService<Software> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"softwares", new SoftwareSerializer());
	}

}
