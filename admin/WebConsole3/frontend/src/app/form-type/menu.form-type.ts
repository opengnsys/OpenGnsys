import {GlobunetFormType} from './globunet.form-type';
import {Menu} from '../model/menu';


export class MenuFormType extends GlobunetFormType {
  getForm() {
    const form: any[] = GlobunetFormType.getForm(new Menu());
    this.setFieldType(form, 'description', 'textarea');
    this.setFieldType(form, 'resolution', 'select');
    return form;
  }
}
