# OpengnsysAngular6

This project was generated with [Angular CLI](https://github.com/angular/angular-cli) version 6.2.3.

## Development server

Run `ng serve --base-href /webconsole/` for a dev server. Navigate to `http://localhost:4200/webconsole`. The app will automatically reload if you change any of the source files.

## Code scaffolding

Run `ng generate component component-name` to generate a new component. You can also use `ng generate directive|pipe|service|class|guard|interface|enum|module`.

## Build

Run `ng build --base-href /webconsole/` to build the project. The build artifacts will be stored in the `dist/` directory. Use the `--prod` flag for a production build.

In some cases its necessary to change the base url in `index.html` file. By default, base url is `"/"`.

The HTML `<base href="..."/>` specifies a base path for resolving relative URLs to assets such as images, scripts, and style sheets. For example, given the `<base href="/my/app/">`, the browser resolves a URL such as `some/place/foo.jpg` into a server request for `my/app/some/place/foo.jpg`. During navigation, the Angular router uses the base href as the base path to component, template, and module files.

Once configured the base href, copy the content of `dist` directory to the desired folder of the server

## Running unit tests

Run `ng test` to execute the unit tests via [Karma](https://karma-runner.github.io).

## Running end-to-end tests

Run `ng e2e` to execute the end-to-end tests via [Protractor](http://www.protractortest.org/).

## Further help

To get more help on the Angular CLI use `ng help` or go check out the [Angular CLI README](https://github.com/angular/angular-cli/blob/master/README.md).
