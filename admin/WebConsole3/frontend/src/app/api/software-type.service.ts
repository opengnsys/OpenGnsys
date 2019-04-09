import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { SoftwareType } from "../model/software-type";
import { SoftwareTypeSerializer } from "../serializer/software-type.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class SoftwareTypeService extends ResourceService<SoftwareType> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"softwareTypes", new SoftwareTypeSerializer());
	}

}
