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
import { GlobunetAddress } from '../../models/globunet-address';
import { Component, Input, HostBinding, NgZone, Inject } from '@angular/core';
let GlobunetAddressComponent = class GlobunetAddressComponent {
    constructor(zone) {
        this.zone = zone;
        this.disabled = false;
        this.readOnly = false;
    }
    get opacity() {
        return this.disabled ? 0.25 : 1;
    }
    ngOnInit() {
    }
    setAddress(addrObj) {
        // We are wrapping this in a NgZone to reflect the changes
        // to the object in the DOM.
        this.zone.run(() => {
            Object.assign(this.address, addrObj);
        });
    }
};
__decorate([
    Input(),
    __metadata("design:type", GlobunetAddress)
], GlobunetAddressComponent.prototype, "address", void 0);
__decorate([
    Input(),
    __metadata("design:type", Object)
], GlobunetAddressComponent.prototype, "disabled", void 0);
__decorate([
    Input(),
    __metadata("design:type", Object)
], GlobunetAddressComponent.prototype, "readOnly", void 0);
__decorate([
    HostBinding('style.opacity'),
    __metadata("design:type", Object),
    __metadata("design:paramtypes", [])
], GlobunetAddressComponent.prototype, "opacity", null);
GlobunetAddressComponent = __decorate([
    Component({
        selector: 'app-globunet-address',
        templateUrl: './globunet-address.component.html'
    }),
    __param(0, Inject(NgZone)),
    __metadata("design:paramtypes", [NgZone])
], GlobunetAddressComponent);
export { GlobunetAddressComponent };
let IonGlobunetAddressComponent = class IonGlobunetAddressComponent extends GlobunetAddressComponent {
};
IonGlobunetAddressComponent = __decorate([
    Component({
        selector: 'ion-globunet-address',
        templateUrl: './ion-globunet-address.component.html'
    })
], IonGlobunetAddressComponent);
export { IonGlobunetAddressComponent };
let AppGlobunetAddressComponent = class AppGlobunetAddressComponent extends GlobunetAddressComponent {
};
AppGlobunetAddressComponent = __decorate([
    Component({
        selector: 'app-globunet-address',
        templateUrl: 'app-globunet-address.component.html'
    })
], AppGlobunetAddressComponent);
export { AppGlobunetAddressComponent };
//# sourceMappingURL=globunet-address.component.js.map