"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const operators_1 = require("rxjs/operators");
class SubResourceService {
    constructor(httpClient, url, parentEndpoint, endpoint, serializer) {
        this.httpClient = httpClient;
        this.url = url;
        this.parentEndpoint = parentEndpoint;
        this.endpoint = endpoint;
        this.serializer = serializer;
    }
    create(parentId, item) {
        return this.httpClient
            .post(`${this.url}/${this.parentEndpoint}/${parentId}/${this.endpoint}`, this.serializer.toJson(item)).pipe(operators_1.map((data) => this.serializer.fromJson(data)));
    }
    update(parentId, item) {
        return this.httpClient
            .patch(`${this.url}/${this.parentEndpoint}/${parentId}/${this.endpoint}/${item.id}`, this.serializer.toJson(item)).pipe(operators_1.map((data) => this.serializer.fromJson(data)));
    }
    read(parentId, id) {
        return this.httpClient
            .get(`${this.url}/${this.parentEndpoint}/${parentId}/${this.endpoint}/${id}`).pipe(operators_1.map((data) => this.serializer.fromJson(data)));
    }
    list(parentId, queryOptions) {
        const params = queryOptions ? "?" + queryOptions.toQueryString() : "";
        return this.httpClient
            .get(`${this.url}/${this.parentEndpoint}/${parentId}/${this.endpoint}${params}`).pipe(operators_1.map((data) => this.convertData(data)));
    }
    delete(parentId, id) {
        return this.httpClient
            .delete(`${this.url}/${this.parentEndpoint}/${parentId}/${this.endpoint}/${id}`);
    }
    convertData(data) {
        if (!Array.isArray(data)) {
            data = [data];
        }
        return data.map((item) => this.serializer.fromJson(item));
    }
}
exports.SubResourceService = SubResourceService;
