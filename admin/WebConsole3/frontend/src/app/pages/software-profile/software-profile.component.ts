import {Component, OnInit} from '@angular/core';

import { SoftwareProfileService } from 'src/app/api/software-profile.service';
import {ParamMap, ActivatedRoute, Router} from '@angular/router';
import {SoftwareProfile} from '../../model/software-profile';
import {ToasterService} from '../../service/toaster.service';
import {SoftwareComponentService} from '../../api/software-component.service';
import {SoftwareComponent} from '../../model/software-component';
import {SoftwareProfileFormType} from '../../form-type/software-profile.form-type';
import {OgCommonService} from '../../service/og-common.service';

@Component({
  selector: 'app-software-profile',
  templateUrl: './software-profile.component.html',
  styleUrls: [ './software-profile.component.scss' ]
})
export class SoftwareProfileComponent implements OnInit {
  public softwareProfile: SoftwareProfile = new SoftwareProfile();
  public softwareComponents: SoftwareComponent[];
  formType: any;
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public ogCommonService: OgCommonService, public softwareProfileService: SoftwareProfileService,
              public softwareComponentsService: SoftwareComponentService, private routerCtrl: Router,
              private router: ActivatedRoute, private toaster: ToasterService) {
    this.formType = new SoftwareProfileFormType().getForm();
  }

  ngOnInit(): void {
    this.softwareComponentsService.list().subscribe(
        components => {
          this.softwareComponents = components;
          this.router.paramMap.subscribe(
              (params: ParamMap) => {
                if(params.get('id')) {
                  this.softwareProfileService.read(Number(params.get('id'))).subscribe(
                      data => {
                        this.softwareProfile = data;
                        const self = this;
                        // Seleccionar los componentes adecuados
                        this.softwareComponents.forEach(function(component, index, array) {
                          if ( self.softwareProfile.softwares.find((value) => value.id === component.id)) {
                            // @ts-ignore
                            component.$$selected = true;
                          }
                        });
                      },
                      error => {
                        this.toaster.pop({type: 'error', body: 'error_loading_software_profile', title: 'error'});
                      }
                  );
                } else {
                  this.softwareProfile = new SoftwareProfile();
                }
              }
          );
        },
        error => {
          this.toaster.pop({type: 'error', body: 'error_loading_software_components', title: 'error'});
        }
    );

  }

  save() {
    this.softwareProfile.softwares = [];
    const self = this;
    this.softwareComponents.forEach(function(component, index, array) {
      // @ts-ignore
      if (component.$$selected === true) {
        // @ts-ignore
        self.softwareProfile.softwares.push(component.id);
      }
    });
    // Actualizar o guardar
    let request;
    if(this.softwareProfile.id !== 0){
      request =  this.softwareProfileService.update(this.softwareProfile);
    }
    else{
      request =  this.softwareProfileService.create(this.softwareProfile);
    }
    request.subscribe(
        (response) => {
          this.toaster.pop({type: 'success', title: 'success', body: 'successfully_saved'});
          this.routerCtrl.navigate(['/app/software']).then(
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
