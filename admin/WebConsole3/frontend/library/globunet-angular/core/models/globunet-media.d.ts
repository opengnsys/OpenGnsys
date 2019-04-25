import { Resource } from "./api/resource";
export declare class GlobunetMedia extends Resource {
    name: string;
    description: string;
    enabled: boolean;
    file: any;
    contentType: string;
}
export declare class GlobunetGalleryHasMedia extends Resource {
    position: number;
    enabled: boolean;
    media: GlobunetMedia;
}
export declare class GlobunetGallery extends Resource {
    name: string;
    context: string;
    defaultFormat: string;
    enabled: boolean;
    galleryHasMedias: GlobunetGalleryHasMedia;
}
