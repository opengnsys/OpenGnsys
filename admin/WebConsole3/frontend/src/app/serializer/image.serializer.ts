import { Serializer } from 'globunet-angular/core/providers/api/serializer';
import {Image} from '../model/image';

export class ImageSerializer extends Serializer {

    toJson(resource: Image): any {
        const image: any = Object.assign({}, resource);
        if (image.client && image.client.id) {
            image.client = image.client.id;
        }
        if (image.repository && image.repository.id) {
            image.repository = image.repository.id;
        }
        return super.toJson(image);
    }
}
