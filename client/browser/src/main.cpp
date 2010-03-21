#include <QtGui/QApplication>
#include "core.h"
#include <stdio.h>
#include <QDebug>

int main(int argc, char *argv[])
{
  QApplication a(argc, argv);
  Core* core=new Core();
  return a.exec();
}
