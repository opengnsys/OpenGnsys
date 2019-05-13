import { Component } from '@angular/core';
import {AuthModule} from 'globunet-angular/core';

@Component({
  selector: 'app-sidebar-left-inner',
  templateUrl: './sidebar-left-inner.component.html'
})
export class SidebarLeftInnerComponent {
    user: any;

    constructor(private authModule: AuthModule) {
        this.user = this.authModule.getLoggedUser();
    }

}
