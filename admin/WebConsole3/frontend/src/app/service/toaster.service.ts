import {ToastrService } from 'ngx-toastr';
import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ToasterService {
  constructor(private toaster: ToastrService) {}

  pop(options) {
    options.type = options.type || 'success';
    switch (options.type) {
      case 'success':
        this.toaster.success(options.body, options.title);
      break;
      case 'error':
        this.toaster.error(options.body, options.title);
      break;
      case 'info':
        this.toaster.info(options.body, options.title);
      break;

    }
  }
}
