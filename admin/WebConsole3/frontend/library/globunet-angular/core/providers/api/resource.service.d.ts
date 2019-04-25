import { Resource } from "../../models/api/resource";
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Serializer } from "./serializer";
import { QueryOptions } from "./query-options";
export declare abstract class ResourceService<T extends Resource> {
    protected httpClient: HttpClient;
    protected url: string;
    protected endpoint: string;
    protected serializer: Serializer;
    constructor(httpClient: HttpClient, url: string, endpoint: string, serializer: Serializer);
    create(item: T): Observable<T>;
    update(item: T): Observable<T>;
    read(id: number): Observable<T>;
    list(queryOptions?: QueryOptions): Observable<T[]>;
    delete(id: number): Observable<Object>;
    protected convertData(data: any): T[];
}
