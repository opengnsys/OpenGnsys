export interface QueryBuilder {
    toQueryMap: () => Map<string, string>;
    toQueryString: () => string;
}
export declare class QueryOptions implements QueryBuilder {
    offset: number;
    limit: number;
    private properties;
    constructor(props: any);
    toQueryMap(): Map<string, string>;
    toQueryString(): string;
}
