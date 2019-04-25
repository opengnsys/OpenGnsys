import { ModuleWithProviders } from '@angular/core';
import { Observable } from 'rxjs';
import { AuthConfig } from './auth.service';
import { AuthService } from './auth.service';
import { GlobunetUser } from "../../models/globunet-user";
export declare class AuthModule {
    private authService;
    private storageKey;
    private loggedUser;
    constructor(authService: AuthService, parentModule: AuthModule);
    static forRoot(config: AuthConfig): ModuleWithProviders;
    login(username: string, password: string, user: GlobunetUser): Observable<GlobunetUser>;
    logout(): void;
    getLoggedUser(user?: GlobunetUser): GlobunetUser;
}
