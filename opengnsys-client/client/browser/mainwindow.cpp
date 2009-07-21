#include "mainwindow.h"
#include <QtWebKit>
#include <QStringList>
#include <QWebView>
#include <QLayout>
#include <QtDebug>
#include <QWebPage>
#include <QProcess>
#include <QTextEdit>

#define BUFFERSIZE 2048

MainWindow::MainWindow(QWidget *parent)
    : QWidget(parent),web(new QWebView()),text(new QTextEdit()),
      layout(new QVBoxLayout()),process(new QProcess(this)),
      dirlog(QString("")),server(QString(""))
{
    // Graphic
    layout->addWidget(web);
    layout->addWidget(text);
    setLayout(layout);

    showFullScreen();

    text->setReadOnly(true);

    web->page()->setLinkDelegationPolicy(QWebPage::DelegateAllLinks);

    // Web signals
    connect(web->page(),SIGNAL(linkClicked(const QUrl&)),this,
            SLOT(slotLinkHandle(const QUrl&)));
    connect(web,SIGNAL(loadStarted()),this,SLOT(slotWebLoadStarted()));
    connect(web,SIGNAL(loadFinished(bool)),this,SLOT(slotWebLoadFinished(bool)));
    connect(web,SIGNAL(loadProgress(int)),this,SLOT(slotWebLoadProgress(int)));

    // Get all environment variables
    QStringList enviroment=QProcess::systemEnvironment();

    // Look for OGLOG 
    QStringList listlog=enviroment.filter("OGLOG=");
    if(listlog.isEmpty())
        dirlog="";
    else
    {
        dirlog=listlog.first();
        dirlog=(dirlog.split("="))[1];
    }

    // Look for SERVER
    QStringList listserver=enviroment.filter("SERVER=");
    if(listserver.isEmpty())
        server="";
    else
    {
        server=listserver.first();
        server=(server.split("="))[1];
    }

    QStringList arguments=QCoreApplication::arguments();
    web->load(QUrl(arguments[1]));


    // Process signals
    connect(process,SIGNAL(started()),this,SLOT(slotProcessStarted()));
    connect(process,SIGNAL(finished(int,QProcess::ExitStatus)),
            this,SLOT(slotProcessFinished(int,QProcess::ExitStatus)));

    connect(process,SIGNAL(error(QProcess::ProcessError)),
            this,SLOT(slotProcessError(QProcess::ProcessError)));

    connect(process,SIGNAL(readyReadStandardOutput()),this,SLOT(slotProcessOutput()));
    connect(process,SIGNAL(readyReadStandardError()),
            this,SLOT(slotProcessErrorOutput()));

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
        web->load(url);
    }
}

void MainWindow::slotWebLoadStarted()
{
    qDebug()<<"Empieza la carga de la web";
}

void MainWindow::slotWebLoadProgress(int progress)
{
    qDebug()<<"Progress "<<progress;
}

void MainWindow::slotWebLoadFinished(bool ok)
{
    if(ok)
        qDebug()<<"Descarga finalizada satisfactoriamente";
    else
        qDebug()<<"Error accediendo a la web";
}

void MainWindow::slotProcessStarted()
{
    qDebug()<<"Proceso inicializado"<<endl;
}

void MainWindow::slotProcessOutput()
{
    qDebug()<<"Output"<<endl;
    process->setReadChannel(QProcess::StandardOutput);
    char buf[BUFFERSIZE];
    while((process->readLine(buf,BUFFERSIZE) > 0))
    {
        text->insertPlainText(buf);
        /*
        QString str="<b>";
        str+=buf;
        str+="</b>";
        text->insertHtml(str);
        */
        output<<buf;
    }
}

void MainWindow::slotProcessErrorOutput()
{
    qDebug()<<"ErrorOutput"<<endl;
    process->setReadChannel(QProcess::StandardError);
    char buf[BUFFERSIZE];
    while((process->readLine(buf,BUFFERSIZE) > 0))
    {
        text->insertPlainText(buf);
        errors<<buf;
    }
}

void MainWindow::slotProcessFinished(int code,QProcess::ExitStatus status)
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

void MainWindow::slotProcessError(QProcess::ProcessError error)
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
