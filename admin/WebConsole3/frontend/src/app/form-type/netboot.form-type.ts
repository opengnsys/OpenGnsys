import {GlobunetFormType} from './globunet.form-type';
import {Netboot} from '../model/netboot';


export class NetbootFormType extends  GlobunetFormType {
  getForm() {
    const form: any[] = GlobunetFormType.getForm(new Netboot());
    this.setFieldType(form, 'type', 'select');
    this.getField(form, 'type').options = {
      items: ['bios', 'uefi']
    };
    this.setFieldType(form, 'template', 'textarea');

    return form;
  }
}
