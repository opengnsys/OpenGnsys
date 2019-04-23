"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
class Serializer {
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
exports.Serializer = Serializer;
