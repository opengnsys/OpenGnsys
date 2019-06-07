import {Component, OnInit} from '@angular/core';

import { NetbootService } from 'src/app/api/netboot.service';
import { Netboot } from 'src/app/model/netboot';
import {OgSweetAlertService} from '../../service/og-sweet-alert.service';
import {ToasterService} from '../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {Router} from '@angular/router';

@Component({
  selector: 'app-netboot',
  templateUrl: './netboot.component.html',
  styleUrls: [ './netboot.component.scss' ]
})
export class NetbootComponent implements OnInit {
  searchText: any;
  netboots: any[] = [];
  biosNetboots: any[] = [];
  uefiNetboots: any[] = [];
  tableOptions: any;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public netbootService: NetbootService, private router: Router, private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private  translate: TranslateService) {
    this.tableOptions = {
        override: false,
        buttons: [
          {
            action: 'edit'
          },
          {
            action: 'copy',
            label: 'copy',
            handler: (rowData) => this.goToNetbootCopy(rowData),
            classes: 'btn-default'
          },
          {
            action: 'delete',
          }
        ]
    };
  }

  ngOnInit(): void {
    this.netbootService.list().subscribe(
      response => {
        this.netboots = response;
        const self = this;
        this.netboots.forEach((netboot) => {
          if (netboot.type && netboot.type === 'uefi') {
              self.uefiNetboots.push(netboot);
          } else {
            self.biosNetboots.push(netboot);
          }
        });
      },
      error => {
        this.toaster.pop({type: 'error', title: 'error', body: error});
      }
    );
  }

  deleteNetboot(id) {
    const self = this;
    this.ogSweetAlert.question(this.translate.instant('sure_to_delete') + '?', this.translate.instant('action_cannot_be_undone'), function() {
      self.netbootService.delete(id).subscribe(
        (response) => {
          self.toaster.pop({type: 'success', title: self.translate.instant('success'), body: self.translate.instant('successfully_deleted')});
          const index = self.netboots.findIndex((object) => object.id === id);
          if (index !== -1) {
            self.netboots.splice(index, 1);
          }
        },
        (error) => {
          self.toaster.pop({type: 'error', title: 'error', body: error});
        }
      );
    });
  }

  goToNetbootEdit(id) {
    this.router.navigate(['/app/netboots/', id, 'edit']);
  }

  goToNetbootCopy(template: Netboot) {
    const copy = Object.assign({}, template);
    this.netbootService.create(copy).subscribe(
      data => {
        // Comprobar que en data viene el id
        const newId = data.id;
        this.goToNetbootEdit(newId);
      },
      error => {
        this.toaster.pop({type: 'error', title: 'error', body: error});
      }
    );

  }
}
