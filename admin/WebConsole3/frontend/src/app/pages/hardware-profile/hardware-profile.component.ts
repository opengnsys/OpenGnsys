import {Component, OnInit} from '@angular/core';

import { HardwareProfileService } from 'src/app/api/hardware-profile.service';
import {ParamMap, ActivatedRoute, Router} from '@angular/router';
import {switchMap} from 'rxjs-compat/operator/switchMap';
import {HardwareProfile} from '../../model/hardware-profile';
import {ToasterService} from '../../service/toaster.service';
import {HardwareComponentService} from '../../api/hardware-component.service';
import {HardwareComponent} from '../../model/hardware-component';
import {HardwareProfileFormType} from '../../form-type/hardware-profile.form-type';
import {OgCommonService} from '../../service/og-common.service';

@Component({
  selector: 'app-hardware-profile',
  templateUrl: './hardware-profile.component.html',
  styleUrls: [ './hardware-profile.component.scss' ]
})
export class HardwareProfileComponent implements OnInit {
  public hardwareProfile: HardwareProfile = new HardwareProfile();
  public hardwareComponents: HardwareComponent[];
  formType: any;
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public ogCommonService: OgCommonService, public hardwareProfileService: HardwareProfileService,
              public hardwareComponentsService: HardwareComponentService, private routerCtrl: Router,
              private router: ActivatedRoute, private toaster: ToasterService) {
    this.formType = new HardwareProfileFormType().getForm();
  }

  ngOnInit(): void {
    this.hardwareComponentsService.list().subscribe(
      components => {
        this.hardwareComponents = components;
        this.router.paramMap.subscribe(
          (params: ParamMap) => {
            if(params.get('id')) {
              this.hardwareProfileService.read(Number(params.get('id'))).subscribe(
              data => {
                  this.hardwareProfile = data;
                  const self = this;
                  // Seleccionar los componentes adecuados
                  this.hardwareComponents.forEach(function(component, index, array) {
                    if ( self.hardwareProfile.hardwares.find((value) => value.id === component.id)) {
                      // @ts-ignore
                      component.$$selected = true;
                    }
                  });
                },
                error => {
                  this.toaster.pop({type: 'error', body: 'error_loading_hardware_profile', title: 'error'});
                }
              );
            } else {
              this.hardwareProfile = new HardwareProfile();
            }
          }
        );
      },
      error => {
        this.toaster.pop({type: 'error', body: 'error_loading_hardware_components', title: 'error'});
      }
    );

  }

  save() {
    this.hardwareProfile.hardwares = [];
    const self = this;
    this.hardwareComponents.forEach(function(component, index, array) {
      // @ts-ignore
      if (component.$$selected === true) {
        // @ts-ignore
        self.hardwareProfile.hardwares.push(component.id);
      }
    });
    // Actualizar o guardar
    let request;
    if(this.hardwareProfile.id !== 0){
      request =  this.hardwareProfileService.update(this.hardwareProfile);
    }
    else{
      request =  this.hardwareProfileService.create(this.hardwareProfile);
    }
   request.subscribe(
      (response) => {
        this.toaster.pop({type: 'success', title: 'success', body: 'successfully_saved'});
        this.routerCtrl.navigate(['/app/hardware']).then(
          success => {
            console.log(success);
          },
          error => {
            console.log(error);
          }
        );
      },
      (error) => {
        this.toaster.pop({type: 'error', title: 'error', body: error});
      }
    );
  }
}
