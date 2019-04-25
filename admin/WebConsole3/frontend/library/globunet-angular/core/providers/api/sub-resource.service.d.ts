import { Resource } from "../../models/api/resource";
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Serializer } from "./serializer";
import { QueryOptions } from "./query-options";
export declare class SubResourceService<T extends Resource> {
    private httpClient;
    private url;
    private parentEndpoint;
    private endpoint;
    private serializer;
    constructor(httpClient: HttpClient, url: string, parentEndpoint: string, endpoint: string, serializer: Serializer);
    create(parentId: number, item: T): Observable<T>;
    update(parentId: number, item: T): Observable<T>;
    read(parentId: number, id: number): Observable<T>;
    list(parentId: number, queryOptions?: QueryOptions): Observable<T[]>;
    delete(parentId: number, id: number): Observable<Object>;
    protected convertData(data: any): T[];
}
