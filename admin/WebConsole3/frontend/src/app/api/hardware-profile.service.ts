import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { HardwareProfile } from "../model/hardware-profile";
import { HardwareProfileSerializer } from "../serializer/hardware-profile.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class HardwareProfileService extends ResourceService<HardwareProfile> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,'hardware-profiles', new HardwareProfileSerializer());
	}

}
