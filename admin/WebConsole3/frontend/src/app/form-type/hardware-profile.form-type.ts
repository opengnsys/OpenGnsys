import {HardwareProfile} from '../model/hardware-profile';
import {GlobunetFormType} from './globunet.form-type';


export class HardwareProfileFormType extends GlobunetFormType {
  getForm() {
    const formType =  GlobunetFormType.getForm(new HardwareProfile());
    this.setFieldType(formType, 'description', 'textarea');
    this.removeField(formType, 'hardwares');
    return formType;
  }
}
