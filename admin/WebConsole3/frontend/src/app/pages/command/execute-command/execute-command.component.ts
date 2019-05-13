import {Component, OnInit} from '@angular/core';

import {ToasterService} from '../../../service/toaster.service';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {OgCommonService} from '../../../service/og-common.service';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {AuthModule} from 'globunet-angular/core';
import {User} from '../../../model/user';
import {OGCommandsService} from '../../../service/og-commands.service';
import {CommandService} from '../../../api/command.service';
import {CommandFormType} from '../../../form-type/command.form-type';
import {Command} from '../../../model/command';

@Component({
  selector: 'app-execute-command',
  templateUrl: './execute-command.component.html',
  styleUrls: [ './execute-command.component.scss' ]
})
export class ExecuteCommandComponent implements OnInit {
  execution = {script: '', clients: ''};
  selectedCommand = {
    inputs: [],
    script: ''
  };
  newCommand = 'true';
  private user: User;
  private selectedClients =  [];
  private form = [];
  private formType: CommandFormType;
  public commands: Command[] = [];
  editInstructions = false;


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






  ngOnInit(): void {
    this.selectedClients = this.ogCommonService.selectedClients;
    if (this.user && this.selectedClients) {
      this.loadFormOptions();
      this.loadCommands();
    } else {
      // TODO - dar error?
      this.ogSweetAlert.error(this.translate.instant('opengnsys_error'), this.translate.instant('not_clients_selected'));
      this.router.navigate(['/app/ous']);
    }
  }

  sendCommand() {
    let result = true;

    if (!this.execution.script) {
      result = false;
      this.toaster.pop({type: 'error', title: 'error', body: this.translate.instant('command_not_valid')});
    } else if (!this.execution.clients) {
      result = false;
      this.toaster.pop({type: 'error', title: 'error', body: this.translate.instant('not_clients_selected')});
    }
    // Si no hubo ningun error
    if (result === true) {
      this.execution.script = this.execution.script.replace(/\"/g, '\\"').replace(/\$/g, '\\\$');
      // Resetar las instrucciones del script opengnsys almacenadas.
      this.ogCommandsService.ogInstructions = '';
      this.commandService.execute(this.execution).subscribe(
          (response) => {
            this.toaster.pop({type: 'success', title: 'success', body: 'Successfully saved'});
            this.router.navigate(['/app/ous']);
          },
          function(error) {
            this.toaster.pop({type: 'error', title: 'error', body: error});
          }
      );
    }
  }

  loadFormOptions() {
    this.formType = new CommandFormType();
    this.form = this.formType.getForm();
  }

  loadCommands() {
    this.commandService.list().subscribe(
        (result) => {
          this.commands = result;
        }
    );
  }


  executeSelectedCommand() {
    // Ejecuta el contenido de ogInstructions
    this.ogCommandsService.execute('RUN_SCRIPT');
  }

  updateSelectedCommand() {
    this.getParamsNumber(this.selectedCommand);
    this.ogCommandsService.ogInstructions = this.selectedCommand.script;
  }


  updateScript(i, value) {
    this.selectedCommand.inputs[i] = value.target.value;
    let script = this.selectedCommand.script;
    for (let index = 0; index < this.selectedCommand.inputs.length; index++) {
      script = script.replace('@' + (index + 1), this.selectedCommand.inputs[index]);
    }
    this.ogCommandsService.ogInstructions = script;

  }

  getParamsNumber(command) {
    const params = [];
    if (command.parameters === true) {
      const allparams = command.script.match(/@[1-9]+/g)||[];
      for (let index = 0; index < allparams.length; index++) {
        if (params.indexOf(allparams[index]) === -1) {
          params.push(allparams[index]);
        }
      }
      this.selectedCommand.inputs = params;
    }
    return params.length;
  }

}
