(function(){
    'use strict';
    angular.module("globunet.utils")
    .filter('gbnToUTF8', gbnToUTF8);

    function gbnToUTF8(){

        function replaceAll( text, _find, _replace ){
              while (text.toString().indexOf(_find) != -1)
                  text = text.toString().replace(_find, _replace);
              return text;
            }

        return function(text){
            if(text != null){
                text = replaceAll(text, '&#225;', "á");
                text = replaceAll(text, '&#233;', "é");
                text = replaceAll(text, '&#237;', "í");
                text = replaceAll(text, '&#243;', "ó");
                text = replaceAll(text, '&#250;', "ú");
                text = replaceAll(text, '&#252;', "ü");
                text = replaceAll(text, '&#193;', "Á");
                text = replaceAll(text, '&#201;', "É");
                text = replaceAll(text, '&#205;', "Í");
                text = replaceAll(text, '&#211;', "Ó");
                text = replaceAll(text, '&#218;', "Ú");
                text = replaceAll(text, '&#220;', "Ü");
                text = replaceAll(text, '&#241;', "ñ");
                text = replaceAll(text, '&#209;', "Ñ");
                text = replaceAll(text, '&#186;', "º");
                text = replaceAll(text, '&#8364;', "€");
            }
            return text;
        }
    };

})();