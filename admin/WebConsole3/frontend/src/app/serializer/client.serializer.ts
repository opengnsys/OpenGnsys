import { Serializer } from 'globunet-angular/core/providers/api/serializer';
import {Client} from '../model/client';

export class ClientSerializer extends Serializer {

    toJson(client: Client): any {
        // @ts-ignore
        client.repository = (client.repository) ? client.repository.id : null;
        // @ts-ignore
        client.hardwareProfile = (client.hardwareProfile) ? client.hardwareProfile.id : null;
        // @ts-ignore
        client.netboot = (client.netboot) ? client.netboot.id : null;
        return super.toJson(client);
    }
}
