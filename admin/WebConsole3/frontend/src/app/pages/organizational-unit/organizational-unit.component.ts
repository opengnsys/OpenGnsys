import {Component, OnDestroy, OnInit} from '@angular/core';

import { OrganizationalUnitService } from 'src/app/api/organizational-unit.service';
import { OrganizationalUnit } from 'src/app/model/organizational-unit';
import {OgCommonService} from '../../service/og-common.service';
import {AuthModule} from 'globunet-angular/core';
import {ClientService} from '../../api/client.service';
import {forkJoin, Observable} from 'rxjs';
import {OgSweetAlertService} from '../../service/og-sweet-alert.service';
import {ToasterService} from '../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {Router} from '@angular/router';
import * as _ from 'lodash';
import {environment} from '../../../environments/environment';

@Component({
  selector: 'app-organizational-unit',
  templateUrl: './organizational-unit.component.html',
  styleUrls: [ './organizational-unit.component.css' ]
})
export class OrganizationalUnitComponent implements OnInit, OnDestroy {
  public config: any;
  public ous: OrganizationalUnit[];
  public movingClients: boolean;
  public options: { scope: { moveChildren: boolean } };
  public searchText: any;
  public user: any;
  public selectedStatus: any[] = [];
  public clientStatus: any;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(private authModule: AuthModule,
              private ogCommonService: OgCommonService,
              private organizationalUnitService: OrganizationalUnitService,
              private clientService: ClientService,
              private router: Router,
              private translate: TranslateService,
              private ogSweetAlert: OgSweetAlertService,
              private toaster: ToasterService) {
    this.clientStatus = [];
  }

  ngOnDestroy(): void {
    if (this.config.timers && this.config.timers.clientsStatusInterval) {
      clearInterval(this.config.timers.clientsStatusInterval.object);
    }
  }

  ngOnInit(): void {
    this.ogCommonService.loadEngineConfig().subscribe(
        data => {
          this.config = data;
          this.user = this.authModule.getLoggedUser();
          this.user.preferences = this.user.preferences || environment.user.preferences;

          for (let index = 0; index < this.config.constants.clientstatus.length; index++) {
            this.selectedStatus[this.config.constants.clientstatus[index].id] = true;
          }
          let request = null;
          if (this.user.ou && this.user.ou.id) {
            // @ts-ignore
            request = this.organizationalUnitService.read(this.user.ou.id + '?children=1');
          } else {
            request = this.organizationalUnitService.list();
          }

          request.subscribe(
            (response) => {
              this.ous = Array.isArray(response) ? response : [response];
              // La primera vez que entra
              if (this.config.timers.clientsStatusInterval.object == null) {
                this.getClientStatus();
                const self = this;
                this.config.timers.clientsStatusInterval.object = window.setInterval(function() {
                  self.getClientStatus();
                }, this.config.timers.clientsStatusInterval.tick);
              } else {
                this.getClientStatus();
              }

            },
            (error) => {
              // TODO error
              alert(error);
            }
          );

        }
    );



  }

  showGrid(show) {
    this.user.preferences.ous.showGrid = show;
    localStorage.setItem('user', JSON.stringify(this.user));
  }

  getClientStatus() {
    let promises = [];
    for (let index = 0; index < this.ous.length; index++) {
      promises = promises.concat(this.getOuClientStatus(this.ous[index]));
    }

    forkJoin(promises).subscribe(
      response => {
        for (let p = 0; p < response.length; p++) {
          // @ts-ignore
          for (let elem = 0; elem < response[p].length; elem++) {
            this.clientStatus[response[p][elem].id] = response[p][elem].status;
          }
        }
      },
      (error) => {
        // TODO
        console.log(error);
      }
    );
  }

  private getOuClientStatus(ou): Observable<any>[] {
    let promises = [];
    promises.push(this.clientService.statusAll(ou.id));
    if (ou.children ) {
      for (let index = 0; index < ou.children.length; index++) {
        promises = promises.concat(this.getOuClientStatus(ou.children[index]));
      }
    }
    return promises;
  }

  getGroup(classroomGroups, groupId) {
    let found = false;
    let result = null;
    let index = 0;
    while (!found && index < classroomGroups.length) {
      if (classroomGroups[index].id === groupId) {
        found = true;
        result = classroomGroups[index];
      } else if (classroomGroups[index].classroomGroups.length > 0) {
        result = this.getGroup(classroomGroups[index].classroomGroups, groupId);
        if (result != null) {
          found = true;
        }
      }
      index++;
    }
    return result;
  }


  selectClients(clients, selected) {
    if (typeof clients !== 'undefined') {
      for (let index = 0; index < clients.length; index++) {
        clients[index].selected = selected;
      }
    }
  }

  selectGroup(group, selected) {
    group.selected = selected;
    this.selectClients(group.clients, selected);
    for (let index = 0; index < group.classroomGroups.length; index++) {
      this.selectGroup(group.classroomGroups[index], selected);
    }
  }


  editGroupName(group) {
    group.name = group.tmpName;
    delete group.tmpName;
    group.editing = true;
  }


  transformToTree(arr) {
    const nodes = {};
    return arr.filter(function(obj) {
      const id = obj.id;
      const parentId = obj.parent ? obj.parent.id : undefined;

      nodes[id] = _.defaults(obj, nodes[id], { groups: [] });
      if (parentId) {
        (nodes[parentId] = (nodes[parentId] || {groups: []}))['groups'].push(obj);
      }

      return !parentId;
    });
  }

}
