import {Component, OnInit} from '@angular/core';

import { ImageService } from 'src/app/api/image.service';
import {Image, PartitionInfo} from 'src/app/model/image';

import {ActivatedRoute, Router} from '@angular/router';
import {ToasterService} from '../../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';
import {ImageFormType} from '../../../form-type/image.form-type';
import {RepositoryService} from '../../../api/repository.service';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-image',
  templateUrl: './image-edit.component.html',
  styleUrls: [ './image-edit.component.scss' ]
})
export class ImageEditComponent implements OnInit {
  image: Image;
  constants: any;
  private formType =  new ImageFormType();
  public form: any;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(private router: Router, private activatedRouter: ActivatedRoute, private imageService: ImageService, private repositoryService: RepositoryService, private translate: TranslateService, private toaster: ToasterService) {
    this.form = this.formType.getForm();
    this.formType.getField(this.form, 'repository').options = {
      items: [],
      label: 'name'
    };
  }

  ngOnInit(): void {
    this.image = new Image();
    this.activatedRouter.paramMap.subscribe(
      (data: any) => {
        if (data.params.id) {
          this.imageService.read(data.params.id).subscribe(
            image => {
              this.image = image;
              if (typeof this.image.partitionInfo === 'string') {
                this.image.partitionInfo = JSON.parse(this.image.partitionInfo);
              } else if (!this.image.partitionInfo) {
                this.image.partitionInfo = new PartitionInfo();
              }
            },
            error => {
              this.toaster.pop({type: 'error', title: 'error', body: error});
            }
          );
        }
      }
    );
    this.repositoryService.list().subscribe(
      data => {
        this.formType.getField(this.form, 'repository').options = {
          items: data,
          label: 'name'
        };
      }
    );
  }

  getImageFileSystem(image) {
    const result = '';
    if (typeof image.partitionInfo === 'string') {
      image.partitionInfo = JSON.parse(image.partitionInfo);
    } else if (!image.partitionInfo) {
      image.partitionInfo = {};
    }
    return image.partitionInfo.filesystem;
  }


  getPartitionType(partition) {
    // buscar la particion en el array global
    let result = this.constants.partitionTypes.filter(function (obj) {
      return obj.id === partition.id;
    });
    result = result[0];
    return result.type;
  }

  save() {
    let request: Observable<any>;
    if (this.image.id !== 0) {
      request = this.imageService.update(this.image);
    } else {
      request = this.imageService.create(this.image);
    }
    request.subscribe(
        (response) => {
          this.toaster.pop({type: 'success', title: this.translate.instant('success'), body: this.translate.instant('successfully_saved')});
          this.router.navigate(['/app/images']);

        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
    );
  }
}
