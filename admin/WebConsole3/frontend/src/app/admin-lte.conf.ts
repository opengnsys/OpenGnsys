import {TranslateService} from '@ngx-translate/core';
import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AdminLteConf {
  static staticConf = {
    skin: 'blue',
    // isSidebarLeftCollapsed: false,
    // isSidebarLeftExpandOnOver: false,
    // isSidebarLeftMouseOver: false,
    // isSidebarLeftMini: true,
    // sidebarRightSkin: 'dark',
    // isSidebarRightCollapsed: true,
    // isSidebarRightOverContent: true,
    // layout: 'normal',
    sidebarLeftMenu: []
  };

  constructor(private translate: TranslateService) {
  }



  get() {
    return {
      skin: 'blue',
      // isSidebarLeftCollapsed: false,
      // isSidebarLeftExpandOnOver: false,
      // isSidebarLeftMouseOver: false,
      // isSidebarLeftMini: true,
      // sidebarRightSkin: 'dark',
      // isSidebarRightCollapsed: true,
      // isSidebarRightOverContent: true,
      // layout: 'normal',
      sidebarLeftMenu: [
        {label: 'MAIN NAVIGATION', separator: true},
        {label: this.translate.instant('ous'), route: 'app/ous', iconClasses: 'fa fa-th'},
        {label: this.translate.instant('images'), route: '/app/images', iconClasses: 'fa fa-cubes'},
        {label: this.translate.instant('repositories'), route: 'app/repositories', iconClasses: 'fa fa-database'},
        {label: this.translate.instant('hardware'), route: 'app/hardware', iconClasses: 'fa fa-server'},
        {label: this.translate.instant('software'), route: 'app/software', iconClasses: 'fa fa-archive'},
        {label: this.translate.instant('menus'), route: 'app/menus', iconClasses: 'fa fa-file-text-o',
          pullRights: [{text: 'comming_soon', classes: 'label pull-right bg-green'}]},
        {label: this.translate.instant('commands'), route: 'app/commands', iconClasses: 'fa fa-terminal'},
        {label: this.translate.instant('netboot_templates'), route: 'app/netboots', iconClasses: 'fa fa-book'},
      ]
    };
  }
}
