"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const resource_1 = require("./api/resource");
class GlobunetCoordinates extends resource_1.Resource {
    constructor() {
        super(...arguments);
        this.latitude = 0;
        this.longitude = 0;
        this.radius = 0;
    }
}
exports.GlobunetCoordinates = GlobunetCoordinates;
class GlobunetAddress extends resource_1.Resource {
    constructor() {
        super(...arguments);
        this.formattedAddress = "";
        this.streetName = "";
        this.streetNumber = "";
        this.postalCode = "";
        this.locality = "";
        this.province = "";
        this.provinceCode = "";
        this.state = "";
        this.stateCode = "";
        this.country = "";
        this.countryCode = "";
        this.coordinates = new GlobunetCoordinates();
    }
}
exports.GlobunetAddress = GlobunetAddress;
