#include <QtGui/QApplication>
#include <stdio.h>
#include "mainwindow.h"
#include <Qt/qtextcodec.h>
// Internacionalización con GNU Gettext.
#include <libintl.h>
#define TEXTDOMAIN "browser"
#define LOCALEDIR "/opt/opengnsys/lib/locale"
#define CHARSET "UTF-8"

int main(int argc, char *argv[])
{
    // Preparar internacionalización.
    setlocale (LC_ALL, "");
    bindtextdomain (TEXTDOMAIN, LOCALEDIR);
    textdomain (TEXTDOMAIN);

    if(argc<=1)
    {
        printf(gettext("Uso: %s -qws http://sitioweb.com/\n"),argv[0]);
        return -1;
    }
    // Codificación de caracteres.
    QTextCodec::setCodecForTr(QTextCodec::codecForName(CHARSET));
    QTextCodec::setCodecForCStrings(QTextCodec::codecForName(CHARSET));
    QTextCodec::setCodecForLocale(QTextCodec::codecForName(CHARSET));
 
    QApplication a(argc, argv);
    MainWindow w;
    w.show();
    return a.exec();
}
