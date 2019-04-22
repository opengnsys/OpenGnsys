import {GlobunetUser} from 'globunet-angular/core';

export class UserPreferences {
    ous: {showGrid: boolean};
    theme: string;
    language: string;

    constructor() {
        this.ous = {
            showGrid: true
        };
        this.theme = 'skin-blue';
        this.language = 'es';
    }
}


export class User extends GlobunetUser {
    preferences?: UserPreferences;

    constructor() {
        super();
        this.preferences = new UserPreferences();
    }
}
