var gulp = require('gulp');
  contains = require('gulp-contains'),
  gutil = require('gulp-util'),
  find = require('gulp-find'),
  debug = require('gulp-debug'),
  replace = require('gulp-replace'),
  minifycss = require('gulp-minify-css'),
  uncss     = require('gulp-uncss'),
  jshint = require('gulp-jshint'),
  uglify = require('gulp-uglify'),
  //imagemin = require('gulp-imagemin'),
  rename = require('gulp-rename'),
  //clean = require('gulp-clean'),
  concat = require('gulp-concat'),
  notify = require('gulp-notify'),
  gulpif    = require('gulp-if'),
  useref    = require('gulp-useref'),
  templateCache = require('gulp-angular-templatecache'),
  jsonminify = require('gulp-jsonminify'),
  clean = require('gulp-clean'),
  l10n = require('gulp-l10n'),
  inject = require('gulp-inject');
  var replace = require('gulp-string-replace');
  var through = require('through-gulp');
  var fs = require("fs");

var angularTranslate = require('gulp-angular-translate-extract');

var project = "knitink";
var src = {
  styl: ['assets/**/*.styl'],
  css: ['assets/**/*.css'],
  coffee: ['assets/**/*.coffee'],
  js: ['assets/**/*.js'],
  views: ['assets/views/**/*.html'],
  i18n: ["assets/i18n"],
  bower: ['bower.json', '.bowerrc'],
  index: 'index.html',
  img: ['assets/images'],
  fonts: ['assets/fonts/**/*', '../bower_components/font-awesome/fonts/**/*', '../bower_components/themify-icons/fonts/**/*'],
  extras: ['qwebchannel.js', 'dataMock/*'],
  app: "assets/js/app.js"
}

src.styles = src.styl.concat(src.css)
src.scripts = src.coffee.concat(src.js)

var outputdir = '../dist';

var publishdir = outputdir+'/assets';
var dist = {
  all: [publishdir + '/**/*'],
  css: publishdir + '/css/',
  js: publishdir + '/js/',
  views: {
    path: publishdir + '/views/',
    relativePath: 'assets/views/',
    outputFile: 'app.views.js'
  },
  vendor: publishdir + '/vendor/',
  i18n: publishdir + '/i18n',
  img: publishdir + "/images",
  fonts: publishdir + "/fonts",
  extras: publishdir
}    


/**
 * Busca en entre todos los ficheros js y html cadenas de angular translate
 * y genera un fichero de traduccion en assets/i18n con dichas cadenas encontradas
*/
gulp.task('localize', function () {
    var dest = "./assets/i18n";
    var assetsPath = 'assets/**/*.{html,js}';
    var defaultLang = "es";
    gutil.log("comienza el proceso");
    return gulp.src(assetsPath)
        .pipe(angularTranslate({
            defaultLang: defaultLang,
            lang: ['en','es','cat'],
            safeMode: true,
            stringifyOptions: true,
            customRegex: [
              'translate="\'((?:\\\\.|[^\'\\\\])*)\'\\|translate"',
              'data-title="\'((?:\\\\.|[^\'\\\\])*)\'\\|translate"',
            ],
            dest: "./assets"
        }))
        // file es de tipo Vinyl object
        .pipe(through(function(file, encoding,callback) {

          // do whatever necessary to process the file 
          if (file.isNull()) {
            gutil.log("es null");
          }
          if (file.isBuffer()) {
            // comprobar si existe el fichero de destino para evitar sobreescribir
            var path = dest + "/"+file.basename;
            var isDefaultLanguage = (file.basename == defaultLang+".json");
            if(isDefaultLanguage){
              gutil.log("Idioma por defecto: "+path);
            }
            var destContent = {};
            if (fs.existsSync(path)) {
              gutil.log("existe el fichero "+path);
              destContent = fs.readFileSync(path, "utf8");
              // Si tiene una "," al final, se quita
              var lastColonIndex = destContent.lastIndexOf(",");
              var lastWord = destContent.substr(lastColonIndex).replace(" ","").replace(/\n/g,"").trim();
              lastWord = lastWord.split(",")[1];
              // Si no está en formato json correcto, se quita la ultima ","
              if(lastWord == "}"){
                destContent = destContent.slice(0, lastColonIndex) + destContent.slice(lastColonIndex+1);
              }
              destContent = JSON.parse(destContent);
            }

            // Obtener el contenido del fichero pasado por translate
            var buff = file.contents;
            var string_lines = buff.toString(encoding).split("\n");
            var output = "";
            var matches = 0;
            var news = 0;
            var total = 0;
            // separar en líneas
            for(var i = 0; i < string_lines.length; i++){
              // Si la linea actual es una cadena con sintaxis angular, no se añade al fichero de traducciones Ej. {{variable.name}}
              if(string_lines[i] == "{" || string_lines[i] == "}"){
                output += string_lines[i]+'\n';
              }
              else if(!/[{}]|([ ]+\"[A-Z _-]+\": \"[A-Z _-]*\",)/.test(string_lines[i])){
                  total++;
                  // Comprobar si la propiedad ya existe en el fichero original
                  var key = string_lines[i].split(":")[0].replace(/\"/g,"").trim();
                  if(destContent.hasOwnProperty(key)){
                    // Se deja tal cual el original
                    output += '\t"'+key+'": "'+destContent[key]+'",\n';
                    matches++;
                  }
                  else{
                    // Si no es el idioma por defecto, se pone como traducción la misma clave
                    if(isDefaultLanguage){
                      output += string_lines[i]+'\n';
                    }
                    else{
                      var keyValue = string_lines[i].split(":");
                      var value = keyValue[0].trim();
                      output += "\t"+keyValue[0]+": "+value+',\n'; 
                    }
                    news++;
                  }
              }
            }
            gutil.log("Total: "+total+" / "+"Matches: "+matches+" / "+"New: "+news);
            file.contents = new Buffer(output, encoding);

          }
          if (file.isStream()) {
            gutil.log("es stream");
          }
          // just pipe data next, or just do nothing to process file later in flushFunction 
          // never forget callback to indicate that the file has been processed. 
            this.push(file);
            callback();
          }, function(callback) {
            // just pipe data next, just callback to indicate that the stream's over 
            callback();
          })
        )
        .pipe(gulp.dest(dest));
        //.. 
});

// Scripts
/**
 * Comprime todos los css y por cada fichero genera un .min.css
 */
gulp.task('compress-css', function() {
  return gulp.src(dist.css+"/*.css")
    .pipe(rename({ suffix: '.min' }))
    .pipe(minifycss())
    .pipe(gulp.dest(dist.css))
})
/**
 * Comprime todos los js y por cada fichero genera un .min.js
 */
gulp.task('compress-js', function() {
  return gulp.src(dist.js+"/*.js")
    .pipe(rename({ suffix: '.min' }))
    .pipe(uglify({mangle: false}))
    .pipe(gulp.dest(dist.js))
})

/// Crear directorio .tmp con los assets
gulp.task('assets-tmp',function(){
  // Crear directorio .tmp con los assets
 return gulp.src("./assets/**/*")
        .pipe(gulp.dest("./.tmp/assets"));
});

/**
 * Busca en index.html las etiquetas build:css y build:js y genera un index en el cual 
 * se referencia un fichero css y un fichero js por cada bloque build:css y build:js respectivamente
 * Además, todos los css y js encerrados en bloques build los minifica.
 * También inyecta la referencia al fichero de templates js previamente creado en el proceso "templates"
 */
gulp.task('compress-index',['assets-tmp'], function() {
    gulp.src(["./"+src.index])
    //.pipe(replace("<!-- script:"+dist.views.outputFile+" -->","<script src='"+dist.views.relativePath+dist.views.outputFile+"'></script>"))
    .pipe(useref())
    .pipe(gulpif('*.js', uglify({mangle: false }).on('error', gutil.log)))
    .pipe(gulpif('*.css',uncss({html: ["./"+src.index, src.views]}).pipe(minifycss({ keepSpecialComments: 1, processImport: false }))))
    .pipe(gulp.dest("./"+outputdir));
});


/**
 * Evalua todos los ficheros js y devuelve un informe de warnings y errores
 * Unifica todos los js en un solo fichero con el nombre indicado la variable "project"
 */
gulp.task("build-js",function(){
    return gulp.src(src.js)
    .pipe(jshint('.jshintrc'))
    .pipe(jshint.reporter('default'))
    .pipe(concat(project+'.js'))
    .pipe(gulp.dest(dist.js))
    .pipe(notify({ message: 'Scripts task complete' }));
});

/**
 * Unifica todos los css en un solo fichero con el nombre indicado la variable "project"
 */
gulp.task("build-css",function(){
    return gulp.src(src.css)
    .pipe(concat(project+'.css'))
    .pipe(gulp.dest(dist.css))
    .pipe(notify({ message: 'Scripts task complete' }));
});

// Compila las plantillas HTML parciales a JavaScript
// para ser inyectadas por AngularJS y minificar el código
gulp.task('templates', function() {
  gulp.src(src.views)
    .pipe(templateCache(dist.views.outputFile,{
      root: "assets/views/",
      module: 'app.views',
      standalone: true
    }))
    .pipe(uglify({mangle: false}))
    .pipe(gulp.dest(dist.views.path));
});

// Copia el contenido de los estáticos e index.html al directorio
// de producción sin tags de comentarios
gulp.task('copy', function() {
  // Copiar las imagenes
  gulp.src(src.img+"/**/*")
  .pipe(gulp.dest(dist.img));
  // Copiar las fuentes
  gulp.src(src.fonts)
  .pipe(gulp.dest(dist.fonts));
  // Copiar las traducciones
  gulp.src(src.i18n + '/*')
  .pipe(jsonminify())
  .pipe(gulp.dest(dist.i18n));
  // Copiar ficheros extras si los hay
  if(src.extras){
    gulp.src(src.extras,{ "base" : "./assets" })
    .pipe(gulp.dest(dist.extras));
  }
});

/* TAREAS GENERALES GULP */

gulp.task('build', function() {
  gulp.run('templates', 'compress-index', 'copy');
});
/**
 * Comprime los css y js individualmente, por cada fichero genera uno minificado
 */
gulp.task('compress', ['compress-css', 'compress-js'])