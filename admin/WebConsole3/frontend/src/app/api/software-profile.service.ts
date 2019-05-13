import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { SoftwareProfile } from "../model/software-profile";
import { SoftwareProfileSerializer } from "../serializer/software-profile.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class SoftwareProfileService extends ResourceService<SoftwareProfile> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"softwareprofiles", new SoftwareProfileSerializer());
	}

}
