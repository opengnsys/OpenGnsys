import {Component, OnInit} from '@angular/core';

import {ToasterService} from '../../../service/toaster.service';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {OgCommonService} from '../../../service/og-common.service';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {AuthModule} from 'globunet-angular/core';
import {User} from '../../../model/user';
import {ImageService} from '../../../api/image.service';
import {OGCommandsService} from '../../../service/og-commands.service';
import {Client} from '../../../model/client';
import {CommandService} from '../../../api/command.service';

@Component({
  selector: 'app-login-command',
  templateUrl: './login-command.component.html',
  styleUrls: [ './login-command.component.scss' ]
})
export class LoginCommandComponent implements OnInit {
  execution = {clients: '', script: '', type: ''};
  command = {};
  user: User;
  selectedClients: Client[];
  selectedPartition: any;
  client: Client;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public ogCommandsService: OGCommandsService,
              private authModule: AuthModule,
              private router: Router,
              private activatedRoute: ActivatedRoute,
              private ogCommonService: OgCommonService,
              private commandService: CommandService,
              private ogSweetAlert: OgSweetAlertService,
              private toaster: ToasterService,
              private translate: TranslateService) {
    this.user = this.authModule.getLoggedUser();
  }



  ngOnInit() {
    this.selectedClients = this.ogCommonService.selectedClients;
    if (this.user && this.selectedClients) {
      this.execution.clients = Object.keys(this.selectedClients).join(',');
    }
  }


  sendCommand() {
    if (!this.selectedPartition) {
      this.toaster.pop({type: 'error', title: 'error', body: this.translate.instant('you_must_select_partition')});
    } else {
      const disk = this.selectedPartition.numDisk;
      const partition = this.selectedPartition.numPartition;

      this.execution.script = 'bootOs ' + disk + ' ' + partition + ' &';
      this.execution.script = this.execution.script.replace(/\"/g, '\\"').replace(/\$/g, '\\\$');
      this.execution.type = 'RUN_SCRIPT';

      this.commandService.execute(this.execution).subscribe(
          (response) => {
            this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_executed')});
            this.router.navigate(['app.ous']);
          },
          (error) => {
            this.toaster.pop({type: 'error', title: 'error', body: error});
          }
      );
    }
  }


  canLogin(partition) {
    return partition.osName !== '' && partition.osName !== 'DATA';
  }

}
