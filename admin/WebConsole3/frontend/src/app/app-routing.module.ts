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
        component: ClientComponent
      },
      {
        path: 'images',
        component: ImageComponent
      },
      {
        path: 'images/create',
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
        path: 'commands',
        component: CommandComponent
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
