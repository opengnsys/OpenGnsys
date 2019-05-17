import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';

import {environment} from '../../environments/environment';
import {SoftwareType} from '../model/software-type';
import {SoftwareTypeSerializer} from '../serializer/software-type.serializer';

import {ResourceService} from 'globunet-angular/core/providers/api/resource.service';
import {QueryOptions} from 'globunet-angular/core/providers/api/query-options';
import {Observable} from 'rxjs';
import {OgCommonService} from '../service/og-common.service';


@Injectable({
    providedIn: 'root'
})
export class SoftwareTypeService extends ResourceService<SoftwareType> {

    constructor(http: HttpClient, private ogCommonService: OgCommonService) {
        super(http, environment.API_URL, 'softwaretypes', new SoftwareTypeSerializer());
    }

    list(queryOptions?: QueryOptions): Observable<SoftwareType[]> {
        return new Observable<SoftwareType[]>((observer) => {
            this.ogCommonService.loadEngineConfig().subscribe(
                data => {
                    observer.next(data.constants.sofwaretypes);
                },
                error => {
                    observer.error(error);
                }
            );
        });
    }

}
