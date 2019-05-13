import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import {LoginComponent} from './pages/login/login.component';
import {DashboardComponent} from './pages/dashboard/dashboard.component';
import {ImageComponent} from './pages/image/image.component';
import {RepositoryComponent} from './pages/repository/repository.component';
import {HardwareProfileComponent} from './pages/hardware-profile/hardware-profile.component';
import {HardwareComponent} from './pages/hardware/hardware.component';
import {OrganizationalUnitComponent} from './pages/organizational-unit/organizational-unit.component';
import {CommandComponent} from './pages/command/command.component';
import {EditCommandComponent} from './pages/command/edit-command/edit-command.component';
import {TraceComponent} from './pages/trace/trace.component';
import {NetbootComponent} from './pages/netboot/netboot.component';
import {NetbootEditComponent} from './pages/netboot/edit/netboot-edit.component';
import {HardwareComponentComponent} from './pages/hardware-component/hardware-component.component';
import {ImageEditComponent} from './pages/image/edit/image-edit.component';
import {ProfileComponent} from './pages/profile/profile.component';
import {OrganizationalUnitEditComponent} from './pages/organizational-unit/edit/organizational-unit-edit.component';
import {ClientComponent} from './pages/client/client.component';
import {ClientDhcpComponent} from './pages/client/dhcp/client-dhcp.component';
import {DeployImageCommandComponent} from './pages/command/deploy-image-command/deploy-image-command.component';
import {MenuComponent} from './pages/menu/menu.component';
import {MenuEditComponent} from './pages/menu/edit/menu-edit.component';
import {SoftwareProfileComponent} from './pages/software-profile/software-profile.component';
import {SoftwareComponentComponent} from './pages/software-component/software-component.component';
import {SoftwareComponent} from './pages/software/software.component';
import {LoginCommandComponent} from './pages/command/login-command/login-command.component';
import {ExecuteCommandComponent} from './pages/command/execute-command/execute-command.component';
import {CreateImageCommandComponent} from './pages/command/create-image-command/create-image-command.component';
import {DeleteCacheImageCommandComponent} from './pages/command/delete-cache-image-command/delete-cache-image-command.component';
import {FormatCommandComponent} from './pages/command/format-command/format-command.component';
import {PartitionFormatCommandComponent} from './pages/command/partition-format-command/partition-format-command.component';


const routes: Routes = [
  { path: '',   redirectTo: '/login', pathMatch: 'full' },
  {
    path: 'login',
    component: LoginComponent,
    data: {
      customLayout: true
    }
  },
  { path: 'app',
    data: {
      customLayout: false
    },
    children: [
      {
        path: 'dashboard',
        component: DashboardComponent
      },
      {
        path: 'ous',
        component: OrganizationalUnitComponent
      },
      {
        path: 'ous/create',
        component: OrganizationalUnitEditComponent
      },
      {
        path: 'ous/edit/:id',
        component: OrganizationalUnitEditComponent
      },
      {
        path: 'clients/create',
        component: ClientComponent
      },
      {
        path: 'clients/edit/:id',
        component: ClientComponent
      },
      {
        path: 'clients/dhcp',
        component: ClientDhcpComponent
      },
      {
        path: 'images',
        component: ImageComponent
      },
      {
        path: 'images/create/monolithic',
        component: ImageEditComponent
      },
      {
        path: 'images/create/basic',
        component: ImageEditComponent
      },
      {
        path: 'images/edit/:id',
        component: ImageEditComponent
      },
      {
        path: 'repositories',
        component: RepositoryComponent
      },
      {
        path: 'hardware',
        component: HardwareComponent,
      },
      {
        path: 'hardware/profile/create',
        component: HardwareProfileComponent
      },
      {
        path: 'hardware/component/create',
        component: HardwareComponentComponent
      },
      {
        path: 'hardware/profile/:id',
        component: HardwareProfileComponent
      },
      {
        path: 'software',
        component: SoftwareComponent,
      },
      {
        path: 'software/profile/create',
        component: SoftwareProfileComponent
      },
      {
        path: 'software/component/create',
        component: SoftwareComponentComponent
      },
      {
        path: 'software/profile/:id',
        component: SoftwareProfileComponent
      },
      {
        path: 'menus',
        component: MenuComponent
      },
      {
        path: 'menus/create',
        component: MenuEditComponent
      },
      {
        path: 'menus/edit/:id',
        component: MenuEditComponent
      },
      {
        path: 'commands',
        component: CommandComponent
      },
      {
        path: 'commands/partition_format',
        component: PartitionFormatCommandComponent
      },
      {
        path: 'commands/deploy_image',
        component: DeployImageCommandComponent
      },
      {
        path: 'commands/login',
        component: LoginCommandComponent
      },
      {
        path: 'commands/execute',
        component: ExecuteCommandComponent
      },
      {
        path: 'commands/create_image',
        component: CreateImageCommandComponent
      },
      {
        path: 'commands/delete_cache_image',
        component: DeleteCacheImageCommandComponent
      },
      {
        path: 'commands/format',
        component: FormatCommandComponent
      },
      {
        path: 'commands/:id',
        component: EditCommandComponent
      },
      {
        path: 'commands/create',
        component: EditCommandComponent
      },
      {
        path: 'traces',
        component: TraceComponent
      },
      {
        path: 'netboots',
        component: NetbootComponent
      },
      {
        path: 'netboots/:id/edit',
        component: NetbootEditComponent
      },
      {
        path: 'netboots/create',
        component: NetbootEditComponent
      },
      {
        path: 'user/profile',
        component: ProfileComponent
      }

    ]
  },
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { useHash: true })],
  exports: [RouterModule]
})
export class AppRoutingModule { }
