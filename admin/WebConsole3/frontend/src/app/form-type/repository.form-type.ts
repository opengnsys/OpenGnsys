import {Repository} from '../model/repository';
import {GlobunetFormType} from './globunet.form-type';


export class RepositoryFormType {
  getForm() {
    return GlobunetFormType.getForm(new Repository());
  }
}
