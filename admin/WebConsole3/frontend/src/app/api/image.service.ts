import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { Image } from "../model/image";
import { ImageSerializer } from "../serializer/image.serializer";

import {ResourceService} from "globunet-angular/core/providers/api/resource.service";


@Injectable({
	providedIn: 'root'
})
export class ImageService extends ResourceService<Image> {

	constructor(http: HttpClient){
		super(http, environment.API_URL,"images", new ImageSerializer());
	}

}
