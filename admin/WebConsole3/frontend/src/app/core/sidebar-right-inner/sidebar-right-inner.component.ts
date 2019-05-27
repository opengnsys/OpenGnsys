import {ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';

import {Subscriber} from 'rxjs';

import {LayoutStore} from 'angular-admin-lte';
import {AuthModule} from 'globunet-angular/core';
import {User, UserPreferences} from '../../model/user';
import {OgCommonService} from '../../service/og-common.service';

@Component({
  selector: 'app-sidebar-right-inner',
  templateUrl: './sidebar-right-inner.component.html'
})
export class SidebarRightInnerComponent implements OnInit, OnDestroy {

  public layout: string;
  public isSidebarLeftCollapsed: boolean;
  public isSidebarLeftExpandOnOver: boolean;
  public isSidebarLeftMini: boolean;

  private subscriptions = [];
  private preferences: UserPreferences;

  constructor(
    public layoutStore: LayoutStore,
    private changeDetectorRef: ChangeDetectorRef,
    private ogCommonService: OgCommonService
  ) {
    this.preferences = this.ogCommonService.loadUserConfig();
  }

  /**
   * [ngOnInit description]
   * @method ngOnInit
   */
  ngOnInit() {
    this.subscriptions.push(this.layoutStore.isSidebarLeftCollapsed.subscribe((value: boolean) => {
      this.isSidebarLeftCollapsed = value;
      this.changeDetectorRef.detectChanges();
    }));
    this.subscriptions.push(this.layoutStore.isSidebarLeftExpandOnOver.subscribe((value: boolean) => {
      this.isSidebarLeftExpandOnOver = value;
      this.preferences.isSidebarLeftExpandOnOver = value;
      this.ogCommonService.saveUserPreferences(this.preferences);
      this.changeDetectorRef.detectChanges();
    }));
    this.subscriptions.push(this.layoutStore.isSidebarLeftMini.subscribe((value: boolean) => {
      this.isSidebarLeftMini = value;
      this.preferences.isSidebarLeftMini = value;
      this.ogCommonService.saveUserPreferences(this.preferences);
      this.changeDetectorRef.detectChanges();
    }));
  }

  /**
   * @method ngOnDestroy
   */
  ngOnDestroy() {
    this.removeSubscriptions();
  }

  /**
   * [removeListeners description]
   * @method removeListeners
   */
  private removeSubscriptions(): void {
    if (this.subscriptions) {
      this.subscriptions.forEach((subscription: Subscriber<any>) => {
        subscription.unsubscribe();
      });
    }
    this.subscriptions = [];
  }


  /**
   * [onLayoutChange description]
   * @method onLayoutChange
   * @param  {[type]}       event [description]
   */
  public onLayoutChange(event): void {
    this.layout = event.target.checked ? event.target.getAttribute('value') : '';
    this.preferences.layout = this.layout;
    this.ogCommonService.saveUserPreferences(this.preferences);
    this.layoutStore.setLayout(this.layout);
  }

  /**
   * [changeSkin description]
   * @method changeSkin
   * @param  {[type]}   event [description]
   * @param  {string}   color [description]
   */
  public changeSkin(event, color: string): void {
    event.preventDefault();
    this.preferences.theme = color;
    this.ogCommonService.saveUserPreferences(this.preferences);
    this.layoutStore.setSkin(color);
  }

  /**
   * [changeSidebarRightSkin description]
   * @method changeSidebarRightSkin
   * @param  {boolean}              value [description]
   */
  public changeSidebarRightSkin(value: boolean): void {
    if (value) {
      this.layoutStore.setSidebarRightSkin('light');
      this.preferences.sidebarRightSkin = 'light';
    } else {
      this.layoutStore.setSidebarRightSkin('dark');
      this.preferences.sidebarRightSkin = 'dark';
    }
    this.ogCommonService.saveUserPreferences(this.preferences);
  }
}
