"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const resource_1 = require("./api/resource");
class GlobunetMedia extends resource_1.Resource {
    constructor() {
        super(...arguments);
        this.name = "";
        this.description = "";
        this.enabled = true;
        this.contentType = "";
    }
}
exports.GlobunetMedia = GlobunetMedia;
class GlobunetGalleryHasMedia extends resource_1.Resource {
    constructor() {
        super(...arguments);
        this.position = 0;
        this.enabled = false;
        this.media = new GlobunetMedia();
    }
}
exports.GlobunetGalleryHasMedia = GlobunetGalleryHasMedia;
class GlobunetGallery extends resource_1.Resource {
    constructor() {
        super(...arguments);
        this.name = "";
        this.context = "";
        this.defaultFormat = "";
        this.enabled = false;
        this.galleryHasMedias = new GlobunetGalleryHasMedia();
    }
}
exports.GlobunetGallery = GlobunetGallery;
