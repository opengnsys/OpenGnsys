import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { User } from "../model/user";
import { UserSerializer } from "../serializer/user.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class UserService extends ResourceService<User> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"users", new UserSerializer());
	}

}
