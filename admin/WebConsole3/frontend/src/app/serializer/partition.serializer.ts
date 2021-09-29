import { Serializer } from "globunet-angular/core/providers/api/serializer";
import {Partition} from '../model/client';

export class PartitionSerializer extends Serializer {

    toJson(partition: Partition): any {
        // @ts-ignore
        partition.image = (partition.image && typeof partition.image === 'object') ? partition.image.id : partition.image;
        return super.toJson(partition);
    }

}
