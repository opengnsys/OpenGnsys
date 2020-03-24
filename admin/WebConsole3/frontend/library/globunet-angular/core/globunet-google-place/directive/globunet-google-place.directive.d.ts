import { ElementRef, OnInit, EventEmitter } from '@angular/core';
import { GlobunetAddress } from '../../models/globunet-address';
export declare class GlobunetGooglePlacesDirective implements OnInit {
    onSelect: EventEmitter<GlobunetAddress>;
    private readonly element;
    constructor(elRef: ElementRef);
    getFormattedAddress(place: any): GlobunetAddress;
    ngOnInit(): void;
}
