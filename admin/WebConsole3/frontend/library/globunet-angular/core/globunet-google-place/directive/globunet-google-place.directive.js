var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __metadata = (this && this.__metadata) || function (k, v) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(k, v);
};
var __param = (this && this.__param) || function (paramIndex, decorator) {
    return function (target, key) { decorator(target, key, paramIndex); }
};
import { Directive, ElementRef, Output, EventEmitter, Inject } from '@angular/core';
import { GlobunetAddress } from '../../models/globunet-address';
let GlobunetGooglePlacesDirective = class GlobunetGooglePlacesDirective {
    constructor(elRef) {
        this.onSelect = new EventEmitter();
        // elRef will get a reference to the element where
        // the directive is placed
        this.element = elRef.nativeElement;
    }
    getFormattedAddress(place) {
        // @params: place - Google Autocomplete place object
        // @returns: address - An address object in human readable format
        const address = new GlobunetAddress();
        for (const i in place.address_components) {
            const item = place.address_components[i];
            address.formattedAddress = place.formatted_address;
            if (item['types'].indexOf('locality') > -1) {
                address.locality = item['long_name'];
            }
            else if (item['types'].indexOf('administrative_area_level_1') > -1) {
                address.province = item['short_name'];
            }
            else if (item['types'].indexOf('street_number') > -1) {
                address.streetNumber = item['short_name'];
            }
            else if (item['types'].indexOf('route') > -1) {
                address.streetName = item['long_name'];
            }
            else if (item['types'].indexOf('country') > -1) {
                address.country = item['long_name'];
            }
            else if (item['types'].indexOf('postal_code') > -1) {
                address.postalCode = item['short_name'];
            }
        }
        return address;
    }
    ngOnInit() {
        const autocomplete = new google.maps.places.Autocomplete(this.element);
        // Event listener to monitor place changes in the input
        google.maps.event.addListener(autocomplete, 'place_changed', () => {
            // Emit the new address object for the updated place
            this.onSelect.emit(this.getFormattedAddress(autocomplete.getPlace()));
        });
    }
};
__decorate([
    Output(),
    __metadata("design:type", EventEmitter)
], GlobunetGooglePlacesDirective.prototype, "onSelect", void 0);
GlobunetGooglePlacesDirective = __decorate([
    Directive({
        selector: '[appGlobunetGooglePlace]'
    }),
    __param(0, Inject(ElementRef)),
    __metadata("design:paramtypes", [ElementRef])
], GlobunetGooglePlacesDirective);
export { GlobunetGooglePlacesDirective };
//# sourceMappingURL=globunet-google-place.directive.js.map