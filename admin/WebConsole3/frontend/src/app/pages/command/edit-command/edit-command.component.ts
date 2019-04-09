import {Component, OnInit} from '@angular/core';

import { CommandService } from 'src/app/api/command.service';
import { Command } from 'src/app/model/command';
import {ToasterService} from '../../../service/toaster.service';
import {ActivatedRoute, Router} from '@angular/router';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {TranslateService} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {CommandFormType} from '../../../form-type/command.form-type';

@Component({
  selector: 'app-command',
  templateUrl: './edit-command.component.html',
  styleUrls: [ './edit-command.component.scss' ]
})
export class EditCommandComponent implements OnInit {
  public command: Command;
  public formType: any[];
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(private router: Router, private activatedRoute: ActivatedRoute, public commandService: CommandService, private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private translate: TranslateService) {
    this.command = new Command();

    this.formType = new CommandFormType().getForm();
  }

  ngOnInit(): void {
    this.activatedRoute.paramMap.subscribe(
      data => {
        const id = data.get('id');
        if (id !== 'create') {
          this.commandService.read(Number(id)).subscribe(
            command => {
              this.command = command;
            },
            error => {
              this.toaster.pop({type: 'error', title: 'error', body: error});
            }
          );
        }
      }
    );
  }

  save() {
    let observable: Observable<Command>;
    if (this.command.id !== 0) {
      observable = this.commandService.update(this.command);
    } else {
      observable = this.commandService.create(this.command);
    }
    observable.subscribe(
      command => {
        this.toaster.pop({type: 'success', title: 'success', body: 'Successfully saved'});
        this.router.navigate(['app/commands']);
      },
      error => {
        this.toaster.pop({type: 'error', title: 'error', body: error});
      }
    );
  }
}
