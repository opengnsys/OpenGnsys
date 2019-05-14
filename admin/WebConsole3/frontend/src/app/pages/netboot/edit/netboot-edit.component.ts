import {Component, OnInit} from '@angular/core';

import { NetbootService } from 'src/app/api/netboot.service';
import { Netboot } from 'src/app/model/netboot';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {ToasterService} from '../../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {ActivatedRoute, Router} from '@angular/router';
import {NetbootFormType} from '../../../form-type/netboot.form-type';

@Component({
  selector: 'app-netboot',
  templateUrl: './netboot-edit.component.html',
  styleUrls: [ './netboot-edit.component.scss' ]
})
export class NetbootEditComponent implements OnInit {
  private form: any[];
  public netboot: Netboot;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public netbootService: NetbootService, private router: Router, private activatedRoute: ActivatedRoute,  private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private  translate: TranslateService) {
    this.form = new NetbootFormType().getForm();
    this.netboot = new Netboot();
  }

  ngOnInit(): void {
    this.activatedRoute.paramMap.subscribe(
      (data: any) => {
        if (data.params.id) {
          this.netbootService.read(data.params.id).subscribe(
            response => {
              this.netboot = response;
            },
            error => {
              this.toaster.pop({type: 'error', title: 'error', body: error});
            }
          );
        } else {
          this.netboot = new Netboot();
        }
      }
    );

  }


  save() {
    let request;
    if (this.netboot.id === 0) {
      request = this.netbootService.create(this.netboot);
    } else {
      request = this.netbootService.update(this.netboot);
    }
    request.subscribe(
      data => {
        this.router.navigate(['/app/netboots']);
      },
      error => {
        this.toaster.pop({type: 'error', title: 'error', body: error});
      }
    );
  }
}
