import {Component, OnInit} from '@angular/core';

import { ImageService } from 'src/app/api/image.service';
import {Image, PartitionInfo} from 'src/app/model/image';
import {OgCommonService} from '../../service/og-common.service';
import {TranslateService} from '@ngx-translate/core';
import {ToasterService} from '../../service/toaster.service';
import {OgSweetAlertService} from '../../service/og-sweet-alert.service';
import {Ng2TableActionComponent} from '../common/table-action/ng2-table-action.component';
import {Router} from '@angular/router';

@Component({
  selector: 'app-image',
  templateUrl: './image.component.html',
  styleUrls: [ './image.component.scss' ]
})
export class ImageComponent implements OnInit {
  images: Image[];
  constants: any;
  removeFile = false;
  tableSettings: any;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(private router: Router, public imageService: ImageService, private ogCommonService: OgCommonService, private translate: TranslateService, private toaster: ToasterService, private ogSweetAlert: OgSweetAlertService) {
    this.ogCommonService.loadEngineConfig().subscribe(
      data => {
        this.constants = data.constants;
      }
    );
  }

  ngOnInit(): void {
    this.imageService.list().subscribe(
      data => {
        this.images = data;
      }
    );
    const self = this;
    this.tableSettings = {
      columns: {
        canonicalName: {
          title: this.translate.instant('canonical_name')
        },
        description: {
          title: this.translate.instant('description')
        },
        partitionInfo: {
          title: this.translate.instant('filesystem'),
          valuePrepareFunction: (cell, image) => {
            return this.getImageFileSystem(image);
          },
          filterFunction: (value: PartitionInfo, search: string) => {
            return (value.filesystem) ? value.filesystem.includes(search) : false;
          }

        },
        createdAt: {
          title: this.translate.instant('createdAt')
        },
        options: {
          title: 'Options',
          filter: false,
          sort: false,
          type: 'custom',
          renderComponent: Ng2TableActionComponent,
          onComponentInitFunction(instance) {
            instance.edit.subscribe(row => {
              self.router.navigate(['/app/images/edit/', row.id]);
            });
            instance.delete.subscribe(row => {
              self.deleteImage(row);
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
    let result = this.constants.partitionTypes.filter(function(obj) { return obj.id === partition.id; });
    result = result[0];
    return result.type;
  }

  deleteImage(image) {
    const self = this;
    this.removeFile = false;
    this.ogSweetAlert.swal({
        title: this.translate.instant('sure_to_delete') + '?',
        html: '<form style="text-align: center; padding-left: 10px">\
			   			<div class="form-group" translate="action_cannot_be_undone"></div>\
					   	<div class="form-group">\
	                    	<div class="checkbox clip-check check-primary checkbox-inline">\
	                      		<input id="removeFile" icheck checkbox-class="icheckbox_square-blue" radio-class="iradio_square-blue" type="checkbox" class="selection-checkbox" [(ngModel)]="removeFile" />\
	                      	</div>\
	                      	<label for="removeFile" translate="remove_file">\
	                    	</label>?\
	                  	</div>\
                  	</form>',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3c8dbc',
        confirmButtonText: this.translate.instant('yes_delete'),
        closeOnConfirm: true
      }).then(
        function(result) {
        if (result === true) {
          if (self.removeFile === true) {
            // TODO Borrar fichero físico...
          }
          this.imageService.delete(image.id).then(
            function(response) {
              this.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_deleted')});
              // Buscar el elemento en el array y borrarlo
              const index = this.images.indexOf(image);
              if (index !== -1) {
                this.images.splice(index, 1);
              }
            },
            function(error) {
              this.toaster.pop({type: 'error', title: 'error', body: error});
            }
          );
        }
      });
  }
}
