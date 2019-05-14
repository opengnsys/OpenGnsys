import { Serializer } from 'globunet-angular/core/providers/api/serializer';
import {Client} from '../model/client';

export class ClientSerializer extends Serializer {

    toJson(client: Client): any {
        // @ts-ignore
        client.repository = (typeof client.repository === 'object') ? client.repository.id : client.repository;
        // @ts-ignore
        client.hardwareProfile = (client.hardwareProfile) ? client.hardwareProfile.id : null;
        // @ts-ignore
        client.netboot = (typeof client.netboot === 'object') ? client.netboot.id : client.netboot;
        return super.toJson(client);
    }
}
