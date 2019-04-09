import { Component, OnInit } from '@angular/core';
import {AuthModule} from 'globunet-angular/core';
import {HardwareProfileService} from '../../api/hardware-profile.service';
import {OgCommonService} from '../../service/og-common.service';
import {TranslateService} from '@ngx-translate/core';
import {HardwareComponentService} from '../../api/hardware-component.service';
import {OgSweetAlertService} from '../../service/og-sweet-alert.service';
import {ToasterService} from '../../service/toaster.service';
import {HardwareTypeService} from '../../api/hardware-type.service';

@Component({
  selector: 'app-hardware',
  templateUrl: './hardware.component.html',
  styleUrls: ['./hardware.component.css']
})
export class HardwareComponent implements OnInit {
  public hardwareProfileGroups: any[] = [];
  public hardwareComponentsGroups: any[][];
  public hardwareComponents: any[] = [];
  public hardwareTypes: any[] = [];

  constructor(private authModule: AuthModule,
              private ogSweetAlert: OgSweetAlertService,
              private toaster: ToasterService,
              private hardwareComponentService: HardwareComponentService,
              private hardwareTypeService: HardwareTypeService,
              private hardwareProfileService: HardwareProfileService,
              private OGCommonService: OgCommonService,
              private translate: TranslateService) { }

  ngOnInit() {
    if (this.authModule.getLoggedUser().id !== 0) {
      this.hardwareProfileService.list().subscribe(
        (response) => {
          this.hardwareProfileGroups = [
            this.OGCommonService.createGroups(response, 'profiles')
          ];
          this.hardwareProfileGroups[0].name =  this.translate.instant('hardware_profiles');
        },
        (error) => {
          alert(error);
        }
      );
      this.hardwareTypeService.list().subscribe(
        data => {
          this.hardwareTypes = data;
        },
        (error) => {
          alert(error);
        }
      );
      this.hardwareComponentService.list().subscribe(
        data => {
            this.hardwareComponents = data;
            this.hardwareComponentsGroups = [
              this.OGCommonService.createGroups(this.hardwareComponents, 'components')
            ];
            // @ts-ignore
            this.hardwareComponentsGroups[0].name = this.translate.instant('hardware_components');
        },
        error => {
          alert(error);
        }
      );
    }
  }
}
