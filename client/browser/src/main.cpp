#include <QtGui/QApplication>
#include <stdio.h>
#include "mainwindow.h"

int main(int argc, char *argv[])
{
    if(argc<=1)
    {
        printf("Usage: %s http://siteweb.com/\n",argv[0]);
        return -1;
    }
    // Codificación UTF-8.
    QTextCodec::setCodecForTr(QTextCodec::codecForName("UTF-8"));
    QTextCodec::setCodecForCStrings(QTextCodec::codecForName("UTF-8"));
 
    QApplication a(argc, argv);
    MainWindow w;
    w.show();
    return a.exec();
}
