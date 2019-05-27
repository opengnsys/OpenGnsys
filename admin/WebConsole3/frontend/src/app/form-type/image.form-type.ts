import {GlobunetFormType} from './globunet.form-type';
import {Image, PartitionInfo} from '../model/image';
import {Repository} from '../model/repository';


export class ImageFormType extends GlobunetFormType {
  getForm() {
    const form: any[] = GlobunetFormType.getForm(new Image());
    this.setFieldType(form, 'description', 'textarea');
    this.setFieldType(form, 'comments', 'textarea');
    this.removeField(form, 'revision');
    this.removeField(form, 'createdAt');
    this.removeField(form, 'partitionInfo');
    this.setFieldType(form, 'repository', 'select');
    return form;
  }
}
