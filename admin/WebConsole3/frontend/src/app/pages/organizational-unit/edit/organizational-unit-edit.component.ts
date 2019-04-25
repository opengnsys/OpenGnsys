import {Component, OnInit} from '@angular/core';

import { OrganizationalUnitService } from 'src/app/api/organizational-unit.service';
import {NetworkSettings, OrganizationalUnit} from 'src/app/model/organizational-unit';
import {OgCommonService} from '../../../service/og-common.service';
import {AuthModule} from 'globunet-angular/core';
import {ClientService} from '../../../api/client.service';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {ToasterService} from '../../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {ActivatedRoute, Router} from '@angular/router';
import {OrganizationalUnitFormType} from '../../../form-type/organizational-unit.form-type';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-organizational-unit',
  templateUrl: './organizational-unit-edit.component.html',
  styleUrls: [ './organizational-unit-edit.component.css' ]
})
export class OrganizationalUnitEditComponent implements OnInit {
  public formType: any;
  ou: OrganizationalUnit;
  constants: any;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(private authModule: AuthModule,
              private ogCommonService: OgCommonService,
              private organizationalUnitService: OrganizationalUnitService,
              private clientService: ClientService,
              private router: Router,
              private activatedRoute: ActivatedRoute,
              private translate: TranslateService,
              private ogSweetAlert: OgSweetAlertService,
              private toaster: ToasterService) {
      this.ou = new OrganizationalUnit();
  }

  ngOnInit(): void {
      this.ogCommonService.loadEngineConfig().subscribe(
          data => {
              this.constants = data.constants;
              this.formType = new OrganizationalUnitFormType().getForm();
              this.activatedRoute.paramMap.subscribe(
                  (route: any) => {
                      if (route.params.id) {
                          this.organizationalUnitService.read(route.params.id).subscribe(
                              response => {
                                  this.ou = response;
                              },
                              error => {
                                  this.toaster.pop({type: 'error', title: 'error', body: error});
                              }
                          );
                      } else {
                          this.ou = new OrganizationalUnit();
                          this.ou.networkSettings = new NetworkSettings();
                          this.ou.networkSettings.p2pMode = this.constants.ou.options.p2p.modes[0];
                          this.ou.networkSettings.mcastMode = this.constants.ou.options.multicast.modes[0];
                      }
                  }
              );
          }
      );

  }

  save() {
    let request: Observable<OrganizationalUnit>;

    // comprobar si necesita una ou "padre"
      this.activatedRoute.queryParams.subscribe(
          query => {
              if (query.parent) {
                  this.ou.parent = query.parent;
              }
              if (this.ou.id !== 0) {
                  request = this.organizationalUnitService.update(this.ou);
              } else {
                  request = this.organizationalUnitService.create(this.ou);
              }
              request.subscribe(
                  data => {
                      this.router.navigate(['/app/ous']);

                      this.toaster.pop({type: 'success', title: 'success', body: 'Successfully saved'});
                  },
                  error => {
                      this.toaster.pop({type: 'error', title: 'error', body: error});
                  }
              );
          }
      );
  }

    getMcastIpExpample(router: string) {
        let result = '';
        if (router && router.split('.').length > 2) {
            result = router.split('.')[1] + '.' + router.split('.')[2];
        }
        return result;
    }
}
