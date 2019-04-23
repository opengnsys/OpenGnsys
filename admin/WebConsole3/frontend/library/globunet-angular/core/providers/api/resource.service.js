"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const operators_1 = require("rxjs/operators");
class ResourceService {
    constructor(httpClient, url, endpoint, serializer) {
        this.httpClient = httpClient;
        this.url = url;
        this.endpoint = endpoint;
        this.serializer = serializer;
    }
    create(item) {
        return this.httpClient
            .post(`${this.url}/${this.endpoint}`, this.serializer.toJson(item)).pipe(operators_1.map(data => this.serializer.fromJson(data)));
    }
    update(item) {
        return this.httpClient
            .patch(`${this.url}/${this.endpoint}/${item.id}`, this.serializer.toJson(item)).pipe(operators_1.map(data => this.serializer.fromJson(data)));
    }
    read(id) {
        return this.httpClient
            .get(`${this.url}/${this.endpoint}/${id}`).pipe(operators_1.map((data) => this.serializer.fromJson(data)));
    }
    list(queryOptions) {
        const params = queryOptions ? "?" + queryOptions.toQueryString() : "";
        return this.httpClient
            .get(`${this.url}/${this.endpoint}${params}`).pipe(operators_1.map((data) => this.convertData(data)));
    }
    delete(id) {
        return this.httpClient
            .delete(`${this.url}/${this.endpoint}/${id}`);
    }
    convertData(data) {
        if (!Array.isArray(data)) {
            data = [data];
        }
        return data.map((item) => this.serializer.fromJson(item));
    }
}
exports.ResourceService = ResourceService;
