#include "mainwindow.h"
#include <QtWebKit>
#include <QLayout>
#include <QtDebug>
#include <QWebPage>
#include <QProcess>

#define BUFFERSIZE 2048

MainWindow::MainWindow(QWidget *parent)
    : QWebView(parent),process(new QProcess(this))
{
    load(QUrl("/home/alex/hola.html"));
    page()->setLinkDelegationPolicy(QWebPage::DelegateAllLinks);
    connect(page(),SIGNAL(linkClicked(const QUrl&)),this,SLOT(slotLinkHandle(const QUrl&)));

    connect(process,SIGNAL(started()),this,SLOT(slotStarted()));
    connect(process,SIGNAL(finished(int,QProcess::ExitStatus)),
            this,SLOT(slotFinished(int,QProcess::ExitStatus)));

    connect(process,SIGNAL(error(QProcess::ProcessError)),this,SLOT(slotError(QProcess::ProcessError)));

    connect(process,SIGNAL(readyReadStandardOutput()),this,SLOT(slotOutput()));
    connect(process,SIGNAL(readyReadStandardError()),this,SLOT(slotErrorOutput()));

}

MainWindow::~MainWindow()
{
}

void MainWindow::slotLinkHandle(const QUrl &url)
{
    QString string = url.toString();
    qDebug() << string;
    if(string.startsWith(PROTOCOL))
    {
        string=string.remove(0,QString(PROTOCOL).length());
        QStringList list=string.split(" ",QString::SkipEmptyParts);
        QString command=list.takeFirst();
        process->setReadChannel(QProcess::StandardOutput);
        process->start(command,list);
    }
    else
    {
        qDebug() << "Load URL: " << url <<endl;
        load(url);
    }
}

void MainWindow::slotStarted()
{
    qDebug()<<"Proceso inicializado"<<endl;
}

void MainWindow::slotOutput()
{
    qDebug()<<"Output"<<endl;
    process->setReadChannel(QProcess::StandardOutput);
    char buf[BUFFERSIZE];
    while((process->readLine(buf,BUFFERSIZE) > 0))
    {
        qDebug()<<buf;
        output<<buf;
    }
}

void MainWindow::slotErrorOutput()
{
    qDebug()<<"ErrorOutput"<<endl;
    process->setReadChannel(QProcess::StandardError);
    char buf[BUFFERSIZE];
    while((process->readLine(buf,BUFFERSIZE) > 0))
    {
        errors<<buf;
    }
}

void MainWindow::slotFinished(int code,QProcess::ExitStatus status)
{
    if(status==QProcess::NormalExit)
    {
        qDebug()<<"Finished: "<<code<<" "<<status<<endl;
        qDebug()<<"OUTPUT:"<<endl<<output<<endl<<"ERROR:"<<endl<<errors<<endl;
    }
    else
    {
        qDebug()<<"Ha petado"<<endl;
        qDebug()<<"Finished: "<<code<<" "<<status<<endl;
        qDebug()<<"OUTPUT:"<<endl<<output<<endl<<"ERROR:"<<endl<<errors<<endl;
    }
}

void MainWindow::slotError(QProcess::ProcessError error)
{
    switch(error)
    {
        case QProcess::FailedToStart:
            qDebug()<<"Imposible arrancar el programa"<<endl;
            break;
        // No capturo crashed porque la pillo por finished
        case QProcess::Crashed:
        case QProcess::Timedout:
        case QProcess::WriteError:
        case QProcess::ReadError:
        case QProcess::UnknownError:
        default:
            qDebug()<<"Otro error"<<endl;
            break;
    }
}
