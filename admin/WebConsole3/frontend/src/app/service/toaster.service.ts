import {ToastrManager} from 'ng6-toastr-notifications';
import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ToasterService {
  constructor(private toaster: ToastrManager) {}

  pop(options) {
    options.type = options.type || 'success';
    switch (options.type) {
      case 'success':
        this.toaster.successToastr(options.body, options.title);
      break;
      case 'error':
        this.toaster.errorToastr(options.body, options.title);
      break;
      case 'info':
        this.toaster.infoToastr(options.body, options.title);
      break;

    }
  }
}
