var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __metadata = (this && this.__metadata) || function (k, v) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(k, v);
};
var __param = (this && this.__param) || function (paramIndex, decorator) {
    return function (target, key) { decorator(target, key, paramIndex); }
};
import { Inject, Injectable } from "@angular/core";
import { throwError, BehaviorSubject } from "rxjs";
import { catchError, filter, take, switchMap, finalize } from "rxjs/operators";
import { AuthService } from "./auth.service";
let TokenInterceptorService = class TokenInterceptorService {
    constructor(auth) {
        this.auth = auth;
        this.AUTH_HEADER = "Authorization";
        this.token = "secrettoken";
        this.refreshTokenInProgress = false;
        this.refreshTokenSubject = new BehaviorSubject(null);
    }
    intercept(req, next) {
        if (!req.headers.has('Content-Type')) {
            req = req.clone({
                headers: req.headers.set('Content-Type', 'application/json')
            });
        }
        req = this.addAuthenticationToken(req);
        return next.handle(req).pipe(catchError((error) => {
            if (error && error.status === 401) {
                // 401 errors are most likely going to be because we have an expired token that we need to refresh.
                if (this.refreshTokenInProgress) {
                    // If refreshTokenInProgress is true, we will wait until refreshTokenSubject has a non-null value
                    // which means the new token is ready and we can retry the request again
                    return this.refreshTokenSubject.pipe(filter(result => result !== null), take(1), switchMap(() => next.handle(this.addAuthenticationToken(req))));
                }
                else {
                    this.refreshTokenInProgress = true;
                    // Set the refreshTokenSubject to null so that subsequent API calls will wait until the new token has been retrieved
                    this.refreshTokenSubject.next(null);
                    return this.refreshAccessToken().pipe(switchMap((success) => {
                        this.refreshTokenSubject.next(success);
                        this.refreshTokenInProgress = false;
                        return next.handle(this.addAuthenticationToken(req));
                    }), 
                    // When the call to refreshToken completes we reset the refreshTokenInProgress to false
                    // for the next time the token needs to be refreshed
                    finalize(() => {
                        this.refreshTokenInProgress = false;
                    }));
                }
            }
            else {
                return throwError(error);
            }
        }));
    }
    refreshAccessToken() {
        return this.auth.getRefreshToken();
    }
    addAuthenticationToken(request) {
        // If we do not have a token yet then we should not set the header.
        // Here we could first retrieve the token from where we store it.
        this.token = this.auth.getAuthorizationToken();
        if (!this.token) {
            return request;
        }
        return request.clone({
            headers: request.headers.set(this.AUTH_HEADER, this.token)
        });
    }
};
TokenInterceptorService = __decorate([
    Injectable(),
    __param(0, Inject(AuthService)),
    __metadata("design:paramtypes", [AuthService])
], TokenInterceptorService);
export { TokenInterceptorService };
//# sourceMappingURL=token-interceptor.service.js.map