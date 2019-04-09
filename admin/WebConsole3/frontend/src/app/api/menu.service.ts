import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { Menu } from "../model/menu";
import { MenuSerializer } from "../serializer/menu.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class MenuService extends ResourceService<Menu> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"menus", new MenuSerializer());
	}

}
