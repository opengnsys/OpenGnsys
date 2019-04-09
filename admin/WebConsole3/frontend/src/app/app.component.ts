import { Component, OnInit } from '@angular/core';
import { LayoutService } from 'angular-admin-lte';
import {AuthModule, GlobunetUser} from 'globunet-angular/core';
import {TranslateService} from '@ngx-translate/core';
import {OgCommonService} from './service/og-common.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
public customLayout: boolean;

  constructor(private layoutService: LayoutService, private authModule: AuthModule, private translate: TranslateService, private ogCommonService: OgCommonService) {

    translate.setDefaultLang('en');
    translate.use('es');
    this.ogCommonService.loadEngineConfig();
  }

  ngOnInit() {
    this.layoutService.isCustomLayout.subscribe((value: boolean) => {
      this.customLayout = value;
    });
  }

  userIsLogged(): boolean {
    return (this.authModule.getLoggedUser().id != 0);
  }
}
