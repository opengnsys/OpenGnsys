import {GlobunetUser} from 'globunet-angular/core';

export class UserPreferences {
    ous: {showGrid: boolean};
    theme: string;
    language: string;
    layout: string;
    isSidebarLeftExpandOnOver: boolean;
    isSidebarLeftMini: boolean;
    sidebarRightSkin: string;

    constructor() {
        this.ous = {
            showGrid: true
        };
        this.theme = 'blue';
        this.language = 'es';
        this.layout = '';
        this.isSidebarLeftExpandOnOver =  false;
        this.isSidebarLeftMini = true;
        this.sidebarRightSkin = 'dark';
    }
}


export class User extends GlobunetUser {
    preferences?: UserPreferences;

    constructor() {
        super();
        this.preferences = new UserPreferences();
    }
}
