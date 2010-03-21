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
    QApplication a(argc, argv);
    MainWindow w;
    w.show();
    return a.exec();
}
