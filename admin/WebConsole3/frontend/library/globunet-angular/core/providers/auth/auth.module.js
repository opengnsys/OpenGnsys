"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __param = (this && this.__param) || function (paramIndex, decorator) {
    return function (target, key) { decorator(target, key, paramIndex); }
};
Object.defineProperty(exports, "__esModule", { value: true });
var AuthModule_1;
"use strict";
const core_1 = require("@angular/core");
const common_1 = require("@angular/common");
const http_1 = require("@angular/common/http");
const rxjs_1 = require("rxjs");
const auth_service_1 = require("./auth.service");
const auth_service_2 = require("./auth.service");
const globunet_user_1 = require("../../models/globunet-user");
let AuthModule = AuthModule_1 = class AuthModule {
    constructor(authService, parentModule) {
        this.authService = authService;
        this.storageKey = '';
        this.loggedUser = new globunet_user_1.GlobunetUser();
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
                { provide: auth_service_1.AuthConfig, useValue: config }
            ]
        };
    }
    login(username, password, user) {
        return new rxjs_1.Observable((observer) => {
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
    core_1.NgModule({
        imports: [common_1.CommonModule, http_1.HttpClientModule],
        providers: [auth_service_2.AuthService]
    }),
    __param(0, core_1.Inject(auth_service_2.AuthService)), __param(1, core_1.Optional()), __param(1, core_1.Inject(AuthModule_1)), __param(1, core_1.SkipSelf())
], AuthModule);
exports.AuthModule = AuthModule;
