import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { SoftwareComponent } from "../model/software-component";
import { SoftwareComponentSerializer } from "../serializer/software-component.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class SoftwareComponentService extends ResourceService<SoftwareComponent> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"softwares", new SoftwareComponentSerializer());
	}

}
