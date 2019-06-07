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
var AuthModule_1;
import { NgModule, Optional, SkipSelf, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { Observable } from 'rxjs';
import { AuthConfig } from './auth.service';
import { AuthService } from './auth.service';
import { GlobunetUser } from "../../models/globunet-user";
let AuthModule = AuthModule_1 = class AuthModule {
    constructor(authService, parentModule) {
        this.authService = authService;
        this.storageKey = '';
        this.loggedUser = new GlobunetUser();
        if (parentModule) {
            throw new Error('AuthModule is already loaded. Import it in the AppModule only');
        }
        // Comprobar sesion anterior
        if (localStorage.getItem("AuthModule.storageKey")) {
            this.storageKey = localStorage.getItem("AuthModule.storageKey") || '';
            let userSession = JSON.parse(localStorage.getItem(this.storageKey) || '');
            if (userSession) {
                this.authService.setAuthorizationToken(userSession.data);
                this.loggedUser = userSession.user;
            }
        }
    }
    static forRoot(config) {
        return {
            ngModule: AuthModule_1,
            providers: [
                { provide: AuthConfig, useValue: config }
            ]
        };
    }
    login(username, password, user) {
        return new Observable((observer) => {
            this.authService.getAccessToken(username, password).subscribe(data => {
                this.storageKey = username + "_" + btoa(password);
                localStorage.setItem("AuthModule.storageKey", this.storageKey);
                // Obtener los datos del usuario
                this.authService.me().subscribe(userMe => {
                    this.loggedUser = Object.assign(user, userMe);
                    // Guardar todo en sesion
                    let userSession = {
                        data: data,
                        user: this.loggedUser
                    };
                    localStorage.setItem(this.storageKey, JSON.stringify(userSession));
                    observer.next(this.loggedUser);
                });
            }, error => {
                observer.error(error);
            });
        });
    }
    logout() {
        delete this.loggedUser;
        localStorage.removeItem(this.storageKey);
        localStorage.removeItem("AuthModule.storageKey");
        this.authService.logout();
    }
    getLoggedUser(user) {
        if (user) {
            this.loggedUser = Object.assign(user, this.loggedUser);
        }
        return this.loggedUser;
    }
};
AuthModule = AuthModule_1 = __decorate([
    NgModule({
        imports: [CommonModule, HttpClientModule],
        providers: [AuthService]
    }),
    __param(0, Inject(AuthService)), __param(1, Optional()), __param(1, Inject(AuthModule_1)), __param(1, SkipSelf()),
    __metadata("design:paramtypes", [AuthService, AuthModule])
], AuthModule);
export { AuthModule };
//# sourceMappingURL=auth.module.js.map