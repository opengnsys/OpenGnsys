import { Injectable } from '@angular/core';
import { HttpClient} from '@angular/common/http';

import { environment } from '../../environments/environment';
import { HardwareType } from '../model/hardware-type';
import { HardwareTypeSerializer } from '../serializer/hardware-type.serializer';

import {ResourceService} from 'globunet-angular/core/providers/api/resource.service';
import {QueryOptions} from 'globunet-angular/core/providers/api/query-options';
import {Observable} from 'rxjs';
import {OgCommonService} from '../service/og-common.service';


@Injectable({
  providedIn: 'root'
})
export class HardwareTypeService extends ResourceService<HardwareType> {

  constructor(http: HttpClient, private ogCommonService: OgCommonService) {
    super(http, environment.API_URL, 'hardwaretypes', new HardwareTypeSerializer());
  }

  list(queryOptions?: QueryOptions): Observable<HardwareType[]> {
    return new Observable<HardwareType[]>((observer) => {
      this.ogCommonService.loadEngineConfig().subscribe(
          data => {
            observer.next(data.constants.hardwaretypes);
          },
        error => {
            observer.error(error);
        }
      );
    });
  }

}
