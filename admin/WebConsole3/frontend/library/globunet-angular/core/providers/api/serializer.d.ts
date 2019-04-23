import { Resource } from "../../models/api/resource";
export declare abstract class Serializer {
    fromJson(json: any): Resource;
    toJson(resource: Resource): any;
}
