#include <QtGui/QApplication>
#include <stdio.h>
#include "mainwindow.h"
#include <Qt/qtextcodec.h>
// Internacionalización con GNU Gettext.
#include <libintl.h>

int main(int argc, char *argv[])
{
    // Preparar internacionalización.
    setlocale (LC_ALL, "");
    bindtextdomain ("browser", "/opt/opengnsys/lib/locale");
    textdomain ("browser");

    if(argc<=1)
    {
        printf(gettext("Uso: %s -qws http://sitioweb.com/\n"),argv[0]);
        return -1;
    }
    // Codificación UTF-8.
    QTextCodec::setCodecForTr(QTextCodec::codecForName("UTF-8"));
    QTextCodec::setCodecForCStrings(QTextCodec::codecForName("UTF-8"));
    QTextCodec::setCodecForLocale(QTextCodec::codecForName("UTF-8"));
 
    QApplication a(argc, argv);
    MainWindow w;
    w.show();
    return a.exec();
}
