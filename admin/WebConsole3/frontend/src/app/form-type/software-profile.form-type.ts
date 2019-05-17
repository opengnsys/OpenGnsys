import {GlobunetFormType} from './globunet.form-type';
import {SoftwareProfile} from '../model/software-profile';


export class SoftwareProfileFormType extends GlobunetFormType {
  getForm() {
    const formType =  GlobunetFormType.getForm(new SoftwareProfile());
    this.setFieldType(formType, 'description', 'textarea');
    this.removeField(formType, 'softwares');
    return formType;
  }
}
