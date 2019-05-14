import {Component, OnInit} from '@angular/core';

import { RepositoryService } from 'src/app/api/repository.service';
import { Repository } from 'src/app/model/repository';
import {OgCommonService} from '../../service/og-common.service';
import {RepositoryFormType} from '../../form-type/repository.form-type';
import {OgSweetAlertService} from '../../service/og-sweet-alert.service';
import {TranslateService} from '@ngx-translate/core';
import {ToasterService} from '../../service/toaster.service';

@Component({
  selector: 'app-repository',
  templateUrl: './repository.component.html',
  styleUrls: [ './repository.component.scss' ]
})
export class RepositoryComponent implements OnInit {
  repositories: Repository[];
  OGCommonService: any;
  formType: any;
  loaderFlags: object = {};
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public repositoryService: RepositoryService, private ogCommonService: OgCommonService, private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private translate: TranslateService) {
    this.OGCommonService = ogCommonService;
    this.formType = new RepositoryFormType().getForm();
    this.repositories = [];
  }

  ngOnInit(): void {
    this.repositoryService.list().subscribe(
      data => {
        this.repositories = data;
      },
      error => {
        // TODO
        alert(error);
      }
    );
  }

   newRepository() {
    this.repositories.push(new Repository());
  }

   saveRepository(Form, repository) {
    const result = true;

    // Si no hubo ningun error se guardan todas las pgms
    if (result === true) {
      this.loaderFlags[repository.id] = true;

      // Con angular.toJson se eliminan los atributos que empiecen por $$
      let method;
      if (!repository.id) {
        method = this.repositoryService.create(repository);
      } else {
        method = this.repositoryService.update(repository);
      }
      method.subscribe(
        (response) => {
          this.loaderFlags[repository.id] = false;
          repository.id = response.id;
          this.toaster.pop({type: 'success', title: 'success', body: 'Successfully saved'});
        },
        (error) => {
          this.loaderFlags[repository.id] = false;
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
      );
    }
  }

   deleteRepository(repository) {
    this.ogSweetAlert.question(this.translate.instant('opengnsys_question'), this.translate.instant('sure_to_delete') + '?', (yes) => {
      if (repository.id) {
        this.repositoryService.delete(repository.id).subscribe(
          (response) => {
            this.removeRepositoryFromArray(repository);
          },
          (error) => {
            this.toaster.pop({type: 'error', title: 'error', body: error});
          }
        );
      } else {
        this.removeRepositoryFromArray(repository);
      }
    });
  }

   removeRepositoryFromArray(repository) {
    const index = this.repositories.indexOf(repository);
    if (index !== -1) {
      this.repositories.splice(index, 1);
    }
    this.toaster.pop({type: 'success', title: 'success', body: 'Successfully deleted'});
  }

   refreshRepoInfo(repository) {
    this.loaderFlags[repository.id] = true;
    this.repositoryService.getInfo(repository).subscribe(
      (response) => {
        this.loaderFlags[repository.id] = false;
        repository.info = response;
        const fileGroups = {};
        // Agrupamos los ficheros de imágenes según su nombre
        repository.info.files.forEach(function(file, key) {
          let basename = file.name.split('.')[0];
          // Es una imagen de backup
          if (file.name.match(/\.ant$/)) {
            basename += '.ant';
          }

          if (!fileGroups[basename]) {
            fileGroups[basename] = [];
          }
          fileGroups[basename].push(file);

        });

        repository.info.files = fileGroups;
      },
      (error) => {
        this.loaderFlags[repository.id] = false;
      }
    );
  }

   isImageFile(file) {
    return !(file.name.match(/(\.img$)|(\.img.ant$)/) === null);
  }

   deleteImageFile(file) {
    this.ogSweetAlert.question(
      this.translate.instant('opengnsys_question'),
      this.translate.instant('sure_to_delete') + '?',
      (yes) => {
        // TODO - borrar el fichero físico y también la imagen asociada
      }
    );
  }

  isRepositoryLoading(repository): boolean {
    let result = false;
    if (repository && repository.id) {
      if (this.loaderFlags && this.loaderFlags[repository.id]) {
        result = this.loaderFlags[repository.id];
      }
    }
    return result;
  }
}
