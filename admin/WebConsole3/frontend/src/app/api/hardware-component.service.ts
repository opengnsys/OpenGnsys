import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { HardwareComponent } from "../model/hardware-component";
import { HardwareComponentSerializer } from "../serializer/hardware-component.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class HardwareComponentService extends ResourceService<HardwareComponent> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"hardwares", new HardwareComponentSerializer());
	}

}
