import { Component, OnInit } from '@angular/core';
import {AuthModule} from 'globunet-angular/core';
import {SoftwareProfileService} from '../../api/software-profile.service';
import {OgCommonService} from '../../service/og-common.service';
import {TranslateService} from '@ngx-translate/core';
import {SoftwareComponentService} from '../../api/software-component.service';
import {OgSweetAlertService} from '../../service/og-sweet-alert.service';
import {ToasterService} from '../../service/toaster.service';
import {SoftwareTypeService} from '../../api/software-type.service';

@Component({
  selector: 'app-software',
  templateUrl: './software.component.html',
  styleUrls: ['./software.component.scss']
})
export class SoftwareComponent implements OnInit {
  public softwareProfileGroups: any[] = [];
  public softwareComponentsGroups: any[][];
  public softwareComponents: any[] = [];
  public softwareTypes: any[] = [];

  constructor(private authModule: AuthModule,
              private ogSweetAlert: OgSweetAlertService,
              private toaster: ToasterService,
              private softwareComponentService: SoftwareComponentService,
              private softwareTypeService: SoftwareTypeService,
              private softwareProfileService: SoftwareProfileService,
              private OGCommonService: OgCommonService,
              private translate: TranslateService) { }

  ngOnInit() {
      this.softwareProfileService.list().subscribe(
        (response) => {
          this.softwareProfileGroups = [
            this.OGCommonService.createGroups(response, 'profiles')
          ];
          this.softwareProfileGroups[0].name =  this.translate.instant('software_profiles');
        },
        (error) => {
          alert(error);
        }
      );
      this.OGCommonService.loadEngineConfig().subscribe(
        data => {
          this.softwareTypes = data.constants.softwareTypes;
        },
        (error) => {
          alert(error);
        }
      );
      this.softwareComponentService.list().subscribe(
        data => {
            this.softwareComponents = data;
            this.softwareComponentsGroups = [
              this.OGCommonService.createGroups(this.softwareComponents, 'components')
            ];
            // @ts-ignore
            this.softwareComponentsGroups[0].name = this.translate.instant('software_components');
        },
        error => {
          alert(error);
        }
      );
  }
}
