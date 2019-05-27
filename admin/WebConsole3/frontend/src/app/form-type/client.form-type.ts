import {GlobunetFormType} from './globunet.form-type';
import {Client} from '../model/client';


export class ClientFormType extends GlobunetFormType {
  getForm() {
    const form =  GlobunetFormType.getForm(new Client());
    this.setFieldType(form, 'repository', 'select');
    this.setFieldType(form, 'hardwareProfile', 'select');
    this.setFieldType(form, 'oglive', 'select');
    this.setFieldType(form, 'netboot', 'select');
    this.getField(form, 'oglive').options = {
      label: 'iso',
      value: 'directory'
    };
    this.getField(form, 'repository').options = {
      label: 'name',
    };
    this.getField(form, 'hardwareProfile').options = {
      label: 'description',
    };
    this.getField(form, 'netboot').options = {
      label: 'name'
    };


    return form;
  }
}
