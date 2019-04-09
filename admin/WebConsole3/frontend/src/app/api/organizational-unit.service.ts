import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { OrganizationalUnit } from "../model/organizational-unit";
import { OrganizationalUnitSerializer } from "../serializer/organizational-unit.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class OrganizationalUnitService extends ResourceService<OrganizationalUnit> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"organizationalunits", new OrganizationalUnitSerializer());
	}

}
