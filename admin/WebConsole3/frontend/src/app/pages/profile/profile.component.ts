import {Component, OnInit} from '@angular/core';

import { UserService } from 'src/app/api/user.service';
import {User, UserPreferences} from 'src/app/model/user';
import {OgCommonService} from '../../service/og-common.service';
import {AuthModule} from 'globunet-angular/core';
import {ToasterService} from '../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {LayoutStore} from 'angular-admin-lte';
import {AdminLteConf} from '../../admin-lte.conf';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: [ './profile.component.scss' ]
})
export class ProfileComponent implements OnInit {
  public user: User;
  public app: any;
  public constants: any;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(private layoutStore: LayoutStore,
              private adminLteConfig: AdminLteConf,
              public userService: UserService,
              public ogCommonService: OgCommonService,
              private authModule: AuthModule,
              private toaster: ToasterService,
              private translate: TranslateService) {
  }

  ngOnInit(): void {
    this.ogCommonService.loadEngineConfig().subscribe(
        data => {
          this.constants = data.constants;
        }
    )
    // @ts-ignore
    this.user  = this.authModule.getLoggedUser();
    this.user.preferences = this.user.preferences || new UserPreferences();
  }

  changeTheme() {
    this.layoutStore.setSkin(this.user.preferences.theme);
  }

   save() {
    this.ogCommonService.saveUserPreferences(this.user.preferences);
    this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_saved')});
  }

}
