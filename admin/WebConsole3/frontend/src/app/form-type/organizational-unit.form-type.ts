import {GlobunetFormType} from './globunet.form-type';
import {OrganizationalUnit} from '../model/organizational-unit';


export class OrganizationalUnitFormType {
  getForm() {
    return GlobunetFormType.getForm(new OrganizationalUnit());
  }
}
