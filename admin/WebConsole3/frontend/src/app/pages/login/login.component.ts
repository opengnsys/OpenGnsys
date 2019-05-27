import {Component, NgZone, ViewEncapsulation} from '@angular/core';
import {AuthModule, GlobunetUser} from 'globunet-angular/core';
import {Router} from '@angular/router';
import {User} from '../../model/user';
import {OgCommonService} from '../../service/og-common.service';
import {LayoutService, LayoutStore} from 'angular-admin-lte';
import {AdminLteConf} from '../../admin-lte.conf';


@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: [ './login.component.scss' ]
})
export class LoginComponent {
  private user: GlobunetUser;
  login = {
    username: '',
    password: ''
  };
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public authModule: AuthModule, private router: Router, private ogCommonService: OgCommonService, private layoutStore: LayoutStore, private adminLteConfig: AdminLteConf) {
    this.user = new GlobunetUser();
    if (this.authModule.getLoggedUser(new User()).id !== 0) {
      this.goToDashboard();
    }
  }

  goToDashboard() {
    this.router.navigate(['/app/dashboard']).then(
      success => {
        this.ogCommonService.loadEngineConfig().subscribe(
            data => {

              const user = <User>this.authModule.getLoggedUser();
              this.ogCommonService.loadUserConfig();

              this.layoutStore.setSidebarLeftMenu(this.adminLteConfig.get().sidebarLeftMenu);
            }
        );
        console.log(success);
      },
      error => {
        console.log(error);
      }
    );

  }

  signIn() {
    this.authModule.login(this.login.username, this.login.password, this.user).subscribe(
      data => {
          this.user = data;
          this.goToDashboard();
      }
    );
  }
}
