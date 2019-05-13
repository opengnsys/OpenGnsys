import {Component, OnInit} from '@angular/core';

import { MenuService } from 'src/app/api/menu.service';
import { Menu } from 'src/app/model/menu';
import {PartitionInfo} from '../../model/image';
import {Ng2TableActionComponent} from '../common/table-action/ng2-table-action.component';
import {TranslateService} from '@ngx-translate/core';
import {Router} from '@angular/router';
import {OgSweetAlertService} from '../../service/og-sweet-alert.service';
import {ToasterService} from '../../service/toaster.service';

@Component({
  selector: 'app-menu',
  templateUrl: './menu.component.html',
  styleUrls: [ './menu.component.scss' ]
})
export class MenuComponent implements OnInit {
  public menus: Menu[];
  private tableSettings: any;
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public menuService: MenuService, private router: Router, private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private translate: TranslateService) {
  }

  ngOnInit(): void {
    this.menuService.list().subscribe(
        data => {
          this.menus = data;
        },
        error => {

        }
    );
    const self = this;
    this.tableSettings = {
      columns: {
        title: {
          title: this.translate.instant('title')
        },
        description: {
          title: this.translate.instant('description')
        },
        comments: {
          title: this.translate.instant('comments'),
        },
        resolution: {
          title: this.translate.instant('resolution')
        },
        options: {
          title: 'Options',
          filter: false,
          sort: false,
          type: 'custom',
          renderComponent: Ng2TableActionComponent,
          onComponentInitFunction(instance) {
            instance.edit.subscribe(row => {
              self.router.navigate(['/app/menus/edit/', row.id]);
            });
            instance.delete.subscribe(row => {
              self.deleteMenu(row);
            });
          }
        },
      },
      actions: {
        position: 'right',
        add: false,
        edit: false,
        delete: false
      }
    };
  }

  deleteMenu(menu) {
    const self = this;
    this.ogSweetAlert.swal({
      title: this.translate.instant('sure_to_delete') + '?',
      message: this.translate.instant('action_cannot_be_undone'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3c8dbc',
      confirmButtonText: this.translate.instant('yes_delete'),
      closeOnConfirm: true
    }).then(
        function(result) {
          if (result.value === true) {

            self.menuService.delete(menu.id).subscribe(
                (response) => {
                  self.toaster.pop({type: 'success', title: 'success', body: self.translate.instant('successfully_deleted')});
                  // Buscar el elemento en el array y borrarlo
                  const index = self.menus.indexOf(menu);
                  if (index !== -1) {
                    self.menus.splice(menu, 1);
                  }
                },
                (error) => {
                  self.toaster.pop({type: 'error', title: 'error', body: error});
                }
            );
          }
        });
  }

}
