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
import { Injectable, Inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { throwError } from 'rxjs';
import { Observable } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
export class AuthConfig {
    constructor(environment) {
        this.BASE_URL = "";
        this.OAUTH_DOMAIN = "";
        this.OAUTH_CLIENT_ID = "";
        this.OAUTH_CLIENT_SECRET = "";
        this.API_URL = "";
        if (environment) {
            this.BASE_URL = environment.BASE_URL || "";
            this.OAUTH_DOMAIN = environment.OAUTH_DOMAIN || "";
            this.OAUTH_CLIENT_ID = environment.OAUTH_CLIENT_ID || "";
            this.OAUTH_CLIENT_SECRET = environment.OAUTH_CLIENT_SECRET || "";
            this.API_URL = environment.API_URL || "";
        }
    }
}
/*
  Generated class for the AuthService provider.

  See https://angular.io/guide/dependency-injection for more info on providers
  and Angular DI.
*/
let AuthService = class AuthService {
    constructor(config, http) {
        this.http = http;
        this.token = "";
        this.refreshToken = "";
        this.tokenType = "";
        this.httpOptions = {
            headers: new HttpHeaders({
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            })
        };
        console.log('Auth Service started');
        this.environment = config;
    }
    setAuthorizationToken(data) {
        data = data || {};
        this.tokenType = data.token_type || "";
        this.token = data.access_token || "";
        this.refreshToken = data.refresh_token || "";
        if (this.tokenType == "bearer") {
            this.httpOptions.headers = this.httpOptions.headers.set("Authorization", "Bearer " + data.access_token);
        }
        else {
            this.httpOptions.headers = new HttpHeaders({
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            });
        }
    }
    getAccessToken(username, password) {
        var url = this.environment.BASE_URL + this.environment.OAUTH_DOMAIN + "?client_id=" + this.environment.OAUTH_CLIENT_ID + "&client_secret=" + this.environment.OAUTH_CLIENT_SECRET + "&grant_type=password&username=" + username + "&password=" + password;
        return this.http.get(url).pipe(map(data => {
            this.setAuthorizationToken(data);
            return data;
        }), catchError((error) => {
            return throwError(error);
        }));
    }
    getRefreshToken() {
        var url = this.environment.BASE_URL + this.environment.OAUTH_DOMAIN + "?client_id=" + this.environment.OAUTH_CLIENT_ID + "&client_secret=" + this.environment.OAUTH_CLIENT_SECRET + "&grant_type=refresh_token&refresh_token=" + this.refreshToken;
        return new Observable((observer => {
            this.http.get(url).subscribe(data => {
                this.setAuthorizationToken(data);
                observer.next(data);
            }, error => {
                this.logout();
            });
        }));
    }
    getAuthorizationToken() {
        return this.httpOptions.headers.get("Authorization");
    }
    me() {
        return this.http.get(this.environment.API_URL + "/user/me");
    }
    logout() {
        // Borrar datos de token
        this.setAuthorizationToken({});
    }
    log(message) {
        console.log('AuthService: ${message}');
    }
    /**
    * Handle Http operation that failed.
    * Let the app continue.
    * @param operation - name of the operation that failed
    * @param result - optional value to return as the observable result
    */
    handleError(error) {
        let msg = 'Something bad happened; please try again later.';
        if (error.error instanceof ErrorEvent) {
            // A client-side or network error occurred. Handle it accordingly.
            msg = 'An error occurred:' + error.error.message;
        }
        else if (error.error instanceof Object) {
            // The backend returned an unsuccessful response code.
            // The response body may contain clues as to what went wrong,
            let errorObject = error.error;
            msg = `Backend returned code ${error.status}, ` + `body was: ${errorObject.error}`;
        }
        console.error(msg);
        // return an observable with a user-facing error message
        return throwError(msg);
    }
    ;
};
AuthService = __decorate([
    Injectable({
        providedIn: 'root'
    }),
    __param(0, Inject(AuthConfig)), __param(1, Inject(HttpClient)),
    __metadata("design:paramtypes", [AuthConfig, HttpClient])
], AuthService);
export { AuthService };
//# sourceMappingURL=auth.service.js.map