import { HttpEvent, HttpInterceptor, HttpHandler, HttpRequest } from "@angular/common/http";
import { Observable } from "rxjs";
import { AuthService } from "./auth.service";
export declare class TokenInterceptorService implements HttpInterceptor {
    auth: AuthService;
    private AUTH_HEADER;
    private token;
    private refreshTokenInProgress;
    private refreshTokenSubject;
    constructor(auth: AuthService);
    intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>>;
    private refreshAccessToken;
    private addAuthenticationToken;
}
