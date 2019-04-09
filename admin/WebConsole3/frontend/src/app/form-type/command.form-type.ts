import {GlobunetFormType} from './globunet.form-type';
import {Command} from '../model/command';


export class CommandFormType extends GlobunetFormType {
  getForm() {
    const form: any[] = GlobunetFormType.getForm(new Command());
    this.setFieldType(form, 'script', 'textarea');
    return form;
  }
}
