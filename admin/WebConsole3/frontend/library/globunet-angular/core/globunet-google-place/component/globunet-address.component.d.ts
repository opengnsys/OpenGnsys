import { GlobunetAddress } from '../../models/globunet-address';
import { OnInit, NgZone } from '@angular/core';
export declare class GlobunetAddressComponent implements OnInit {
    private zone;
    address: GlobunetAddress;
    disabled: boolean;
    readOnly: boolean;
    readonly opacity: 1 | 0.25;
    constructor(zone: NgZone);
    ngOnInit(): void;
    setAddress(addrObj: any): void;
}
export declare class IonGlobunetAddressComponent extends GlobunetAddressComponent {
}
export declare class AppGlobunetAddressComponent extends GlobunetAddressComponent {
}
