import {Component, OnInit} from '@angular/core';

import { CommandService } from 'src/app/api/command.service';
import { Command } from 'src/app/model/command';
import {ToasterService} from '../../service/toaster.service';
import {Router} from '@angular/router';
import {OgSweetAlertService} from '../../service/og-sweet-alert.service';
import {TranslateService} from '@ngx-translate/core';

@Component({
  selector: 'app-command',
  templateUrl: './command.component.html',
  styleUrls: [ './command.component.scss' ]
})
export class CommandComponent implements OnInit {
  public commands: Command[];
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(private router: Router, public commandService: CommandService, private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private translate: TranslateService) {
  }

  ngOnInit(): void {
    this.commandService.list().subscribe(
      data => {
        this.commands = data;
      },
      error => {
        this.toaster.pop({type: 'error', title: 'error', body: error});
      }
    );
  }

  goToEdit(command: any) {
    this.router.navigate(['/app/commands', command.id]);
  }

  deleteCommand(command: any) {
    this.ogSweetAlert.question(this.translate.instant('opengnsys_question'), this.translate.instant('sure_to_delete') + '?', (yes) => {
        if(yes.value === true) {
          this.commandService.delete(command.id).subscribe(
            (response) => {
              const index = this.commands.indexOf(command);
              if (index !== -1) {
                this.commands.splice(index, 1);
              }
              this.toaster.pop({type: 'success', title: 'success', body: 'Successfully deleted'});
            },
            (error) => {
              this.toaster.pop({type: 'error', title: 'error', body: error});
            }
          );
        }
    });
  }
}
