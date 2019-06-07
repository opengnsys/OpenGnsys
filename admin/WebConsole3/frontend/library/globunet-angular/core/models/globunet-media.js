import { Resource } from "./api/resource";
export class GlobunetMedia extends Resource {
    constructor() {
        super(...arguments);
        this.name = "";
        this.description = "";
        this.enabled = true;
        this.contentType = "";
    }
}
export class GlobunetGalleryHasMedia extends Resource {
    constructor() {
        super(...arguments);
        this.position = 0;
        this.enabled = false;
        this.media = new GlobunetMedia();
    }
}
export class GlobunetGallery extends Resource {
    constructor() {
        super(...arguments);
        this.name = "";
        this.context = "";
        this.defaultFormat = "";
        this.enabled = false;
        this.galleryHasMedias = new GlobunetGalleryHasMedia();
    }
}
//# sourceMappingURL=globunet-media.js.map