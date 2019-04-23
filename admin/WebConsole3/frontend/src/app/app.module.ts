import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import {AdminLteConf } from './admin-lte.conf';
import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';
import { CoreModule } from './core/core.module';
import {DropdownModule, LayoutModule, LayoutService, LayoutState, LayoutStore} from 'library/angular-admin-lte/src';
import {environment} from '../environments/environment';
import {AuthModule, TokenInterceptorService} from 'library/globunet-angular/core';
import {HTTP_INTERCEPTORS, HttpClient, HttpClientModule} from '@angular/common/http';
import {LoginComponent} from './pages/login/login.component';
import {ImageComponent} from './pages/image/image.component';
import { LoadingPageModule, MaterialBarModule } from 'angular-loading-page';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {TranslateLoader, TranslateModule, TranslateService} from '@ngx-translate/core';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { ToastrModule } from 'ng6-toastr-notifications';
import { Ng2SmartTableModule } from 'ng2-smart-table';
import { FormsModule } from '@angular/forms';
import {Ng2TableActionComponent} from './pages/common/table-action/ng2-table-action.component';
import {RepositoryComponent} from './pages/repository/repository.component';
import {FormInputComponent} from './pages/common/forms/form-input.component';
import {HardwareComponentComponent} from './pages/hardware-component/hardware-component.component';
import { HardwareComponent } from './pages/hardware/hardware.component';
import { HardwareComponentsComponent } from './pages/hardware/hardware-components/hardware-components.component';
import { HardwareProfilesComponent } from './pages/hardware/hardware-profiles/hardware-profiles.component';
import { HardwareTypesComponent } from './pages/hardware/hardware-types/hardware-types.component';
import {ProfilesTableComponent} from './pages/hardware/hardware-profiles/profiles-table/profiles-table.component';
import {ProfilesGroupComponent} from './pages/hardware/hardware-profiles/profiles-group/profiles-group.component';
import {HardwareComponentsTableComponent} from './pages/hardware/hardware-components/hardware-components-table/hardware-components-table.component';
import {HardwareComponentsGroupComponent} from './pages/hardware/hardware-components/hardware-components-group/hardware-components-group.component';
import {HardwareProfileComponent} from './pages/hardware-profile/hardware-profile.component';
import {IcheckDirective} from './pages/common/directive/icheck.directive';
import {OrganizationalUnitComponent} from './pages/organizational-unit/organizational-unit.component';
import {OuGroupComponent} from './pages/organizational-unit/ou-group/ou-group.component';
import {OuClientComponent} from './pages/organizational-unit/ou-clients/ou-client.component';
import {CommandComponent} from './pages/command/command.component';
import {EditCommandComponent} from './pages/command/edit-command/edit-command.component';
import {FixedToolboxBarDirective} from './pages/common/directive/fixed-toolbox-bar.directive';
import { OgInformationOptionsComponent } from './pages/common/og-options/og-information-options/og-information-options.component';
import { OgCommandsOptionsComponent } from './pages/common/og-options/og-commands-options/og-commands-options.component';
import { OgExecuteCommandOptionsComponent } from './pages/common/og-options/og-execute-command-options/og-execute-command-options.component';
import { OgHardwareComponentsOptionsComponent } from './pages/common/og-options/og-hardware-components-options/og-hardware-components-options.component';
import { OgHardwareProfileOptionsComponent } from './pages/common/og-options/og-hardware-profile-options/og-hardware-profile-options.component';
import { OgHardwareTypesOptionsComponent } from './pages/common/og-options/og-hardware-types-options/og-hardware-types-options.component';
import { OgOuOptionsComponent } from './pages/common/og-options/og-ou-options/og-ou-options.component';
import { OgSelectedClientsComponent } from './pages/common/og-options/og-selected-clients/og-selected-clients.component';
import {OgOuGeneralOptionsComponent} from './pages/common/og-options/og-ou-general-options/og-ou-general-options.component';
import {TraceComponent} from './pages/trace/trace.component';
import {OgCommandsPipe} from './pages/common/pipes/og-commands.pipe';
import {NetbootComponent} from './pages/netboot/netboot.component';
import {NetbootEditComponent} from './pages/netboot/edit/netboot-edit.component';
import {ImageEditComponent} from './pages/image/edit/image-edit.component';
import {ProfileComponent} from './pages/profile/profile.component';
import {layoutProvider} from '../../library/angular-admin-lte/src/lib/layout/layout.provider';



@NgModule({
  declarations: [
    AppComponent,
    LoginComponent,
    ImageComponent,
    ImageEditComponent,
    DashboardComponent,
    RepositoryComponent,
    OrganizationalUnitComponent,
    Ng2TableActionComponent,
    FormInputComponent,
    HardwareComponentComponent,
    HardwareComponent,
    HardwareComponentsComponent,
    HardwareProfilesComponent,
    HardwareProfileComponent,
    HardwareTypesComponent,
    ProfilesTableComponent,
    ProfilesGroupComponent,
    HardwareComponentsTableComponent,
    HardwareComponentsGroupComponent,
    OuGroupComponent,
    OuClientComponent,
    CommandComponent,
    EditCommandComponent,
    IcheckDirective,
    FixedToolboxBarDirective,
    OgInformationOptionsComponent,
    OgCommandsOptionsComponent,
    OgExecuteCommandOptionsComponent,
    OgHardwareComponentsOptionsComponent,
    OgHardwareProfileOptionsComponent,
    OgHardwareTypesOptionsComponent,
    OgOuOptionsComponent,
    OgSelectedClientsComponent,
    OgOuGeneralOptionsComponent,
    TraceComponent,
    NetbootComponent,
    NetbootEditComponent,
    ProfileComponent,
    OgCommandsPipe
  ],
  entryComponents: [
    AppComponent,
    LoginComponent,
    ImageComponent,
    ImageEditComponent,
    OrganizationalUnitComponent,
    Ng2TableActionComponent,
    ProfilesTableComponent,
    ProfilesGroupComponent,
    HardwareComponentsTableComponent,
    HardwareComponentsGroupComponent,
    OuGroupComponent,
    OuClientComponent,
    CommandComponent,
    EditCommandComponent,
    OgOuGeneralOptionsComponent,
    TraceComponent,
    NetbootComponent,
    NetbootEditComponent,
    ProfileComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    CoreModule,
    DropdownModule,
    LayoutModule.forRoot(AdminLteConf.staticConf),
    LoadingPageModule, MaterialBarModule,
    BrowserAnimationsModule,
    HttpClientModule,
    AuthModule.forRoot(environment),
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: createTranslateLoader,
        deps: [HttpClient]
      }
    }),
    ToastrModule.forRoot(),
    Ng2SmartTableModule,
    FormsModule
  ],
  providers: [
    {provide: HTTP_INTERCEPTORS, useClass: TokenInterceptorService, multi: true}
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}


export function createTranslateLoader(http: HttpClient) {
  return new TranslateHttpLoader(http, './assets/i18n/', '.json');
}
