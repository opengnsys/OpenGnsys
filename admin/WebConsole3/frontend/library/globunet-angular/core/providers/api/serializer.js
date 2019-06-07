export class Serializer {
    fromJson(json) {
        if (typeof json != "object") {
            json = { response: json };
        }
        return Object.assign({}, json);
    }
    toJson(resource) {
        return JSON.parse(JSON.stringify(resource));
    }
}
//# sourceMappingURL=serializer.js.map