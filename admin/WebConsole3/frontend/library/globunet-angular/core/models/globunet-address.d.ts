import { Resource } from "./api/resource";
export declare class GlobunetCoordinates extends Resource {
    latitude: number;
    longitude: number;
    radius: number;
}
export declare class GlobunetAddress extends Resource {
    formattedAddress: string;
    streetName: string;
    streetNumber: string;
    postalCode: string;
    locality: string;
    province: string;
    provinceCode: string;
    state: string;
    stateCode: string;
    country: string;
    countryCode: string;
    coordinates: GlobunetCoordinates;
}
