export class QueryOptions {
    constructor(props) {
        this.offset = 0;
        this.limit = 10000;
        this.properties = {};
        Object.assign(this.properties, props);
    }
    toQueryMap() {
        const queryMap = new Map();
        queryMap.set('offset', `${this.offset}`);
        queryMap.set('limit', `${this.limit}`);
        for (var prop in this.properties) {
            if (this.properties.hasOwnProperty(prop)) {
                queryMap.set(prop, this.properties[prop]);
            }
        }
        return queryMap;
    }
    toQueryString() {
        let queryString = '';
        this.toQueryMap().forEach((value, key) => {
            queryString = queryString.concat(`${key}=${value}&`);
        });
        return queryString.substring(0, queryString.length - 1);
    }
}
//# sourceMappingURL=query-options.js.map