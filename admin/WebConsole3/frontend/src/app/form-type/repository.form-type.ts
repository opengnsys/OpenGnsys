import {Repository} from '../model/repository';
import {GlobunetFormType} from './globunet.form-type';


export class RepositoryFormType extends GlobunetFormType{
  getForm() {
    const form =  GlobunetFormType.getForm(new Repository());
    this.setFieldType(form, 'description', 'textarea');
    return form;
  }
}
