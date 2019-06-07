import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { GlobunetUser } from "../../models/globunet-user";
export declare class AuthConfig {
    BASE_URL: string;
    OAUTH_DOMAIN: string;
    OAUTH_CLIENT_ID: string;
    OAUTH_CLIENT_SECRET: string;
    API_URL: string;
    constructor(environment: any);
}
export declare class AuthService {
    private http;
    private token;
    private refreshToken;
    private tokenType;
    private environment;
    private httpOptions;
    constructor(config: AuthConfig, http: HttpClient);
    setAuthorizationToken(data: any): void;
    getAccessToken(username: string, password: string): Observable<any>;
    getRefreshToken(): Observable<any>;
    getAuthorizationToken(): string;
    me(): Observable<GlobunetUser>;
    logout(): void;
    private log;
    /**
    * Handle Http operation that failed.
    * Let the app continue.
    * @param operation - name of the operation that failed
    * @param result - optional value to return as the observable result
    */
    private handleError;
}
