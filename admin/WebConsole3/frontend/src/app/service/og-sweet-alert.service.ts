import {Injectable} from '@angular/core';
import Swal from 'sweetalert2';
import {TranslateService} from '@ngx-translate/core';

@Injectable({
  providedIn: 'root'
})
export class OgSweetAlertService {
  constructor(private translate: TranslateService) {}

  swal(options): Promise<any> {
    return Swal.fire(options);
  }


  success(title, message) {
    Swal.fire( title, message, 'success' );
  }

  error(title, message) {
    Swal.fire( title, message, 'error' );
  }

  warning(title, message) {
    Swal.fire( title, message, 'warning' );
  }

  info(title, message) {
    Swal.fire( title, message, 'info' );
  }

  question(title, message, okcallback?, cancelcallback?) {
    Swal.fire({
      title: title,
      text: message,
      type: 'info',
      showCancelButton: true,
      cancelButtonText: this.translate.instant('no'),
      cancelButtonClass: 'default',
      confirmButtonClass: 'primary',
      confirmButtonText: this.translate.instant('yes')

    }).then((response) => {
      if (response.dismiss) {
        if (typeof cancelcallback === 'function') {
          cancelcallback(response);
        }
      } else {
        okcallback(response);
      }
    });
  }
}
