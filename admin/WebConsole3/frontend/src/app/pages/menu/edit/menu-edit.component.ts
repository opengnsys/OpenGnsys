import {Component, OnInit} from '@angular/core';


import {ActivatedRoute, Router} from '@angular/router';
import {ToasterService} from '../../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {MenuFormType} from '../../../form-type/menu.form-type';
import {RepositoryService} from '../../../api/repository.service';
import {Observable} from 'rxjs';
import {MenuService} from '../../../api/menu.service';
import {Menu} from '../../../model/menu';
import {OgCommonService} from '../../../service/og-common.service';
import {DomSanitizer} from '@angular/platform-browser';

@Component({
  selector: 'app-menu',
  templateUrl: './menu-edit.component.html',
  styleUrls: [ './menu-edit.component.scss' ]
})
export class MenuEditComponent implements OnInit {
  menu: Menu;
  constants: any;
  private formType =  new MenuFormType();
  public form: any;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public sanitizer: DomSanitizer, private router: Router, private activatedRouter: ActivatedRoute, private ogCommonService: OgCommonService, private menuService: MenuService, private translate: TranslateService, private toaster: ToasterService) {
    this.form = this.formType.getForm();
  }

  ngOnInit(): void {
    this.menu = new Menu();
    this.ogCommonService.loadEngineConfig().subscribe(
        data => {
          this.formType.getField(this.form, 'resolution').options = {
            items: data.constants.menus.resolutions,
            label: 'text',
            value: 'id'
          };
        }
    );
    this.activatedRouter.paramMap.subscribe(
      (data: any) => {
        if (data.params.id) {
          this.menuService.read(data.params.id).subscribe(
            menu => {
              this.menu = menu;
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
    let request: Observable<any>;
    if (this.menu.id !== 0) {
      request = this.menuService.update(this.menu);
    } else {
      request = this.menuService.create(this.menu);
    }
    request.subscribe(
        (response) => {
          this.toaster.pop({type: 'success', title: this.translate.instant('success'), body: this.translate.instant('successfully_saved')});
          this.router.navigate(['/app/menus']);

        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
    );
  }
}
