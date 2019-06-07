import { Resource } from "./api/resource";
export class GlobunetCoordinates extends Resource {
    constructor() {
        super(...arguments);
        this.latitude = 0;
        this.longitude = 0;
        this.radius = 0;
    }
}
export class GlobunetAddress extends Resource {
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
//# sourceMappingURL=globunet-address.js.map