#include "mainwindow.h"
#include "mainwindow.moc"
#include <QtWebKit>
#include <QStringList>
#include <QWebView>
#include <QDockWidget>
#include <QtDebug>
#include <QWebPage>
#include <QProcess>
#include <QTextEdit>
#include <QMessageBox>
#include <QPushButton>
#include <QDateTime>

#define BUFFERSIZE 2048

#define CURRENT_TIME() QDateTime::currentDateTime().toString("dd/MM/yy hh:mm:ss")

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent),web(new QWebView()),text(new QTextEdit()),
      process(new QProcess(this)),
      logfile(NULL),logstream(NULL)
{
    // Graphic
    //layout->addWidget(web);
    //layout->addWidget(text);
    //setLayout(layout);
    setCentralWidget(web);

    dock=new QDockWidget(this);
    dock->setAllowedAreas(Qt::BottomDockWidgetArea);
    dock->setFeatures(QDockWidget::NoDockWidgetFeatures);
    dock->setWidget(text);
    addDockWidget(Qt::BottomDockWidgetArea,dock);


    /*
    QTermWidget *console = new QTermWidget();
    
    QFont font = QApplication::font();
    font.setFamily("Terminus");
    font.setPointSize(12);
    
    console->setTerminalFont(font);
    
    //console->setColorScheme(COLOR_SCHEME_BLACK_ON_LIGHT_YELLOW);
    console->setScrollBarPosition(QTermWidget::ScrollBarRight);
    
    mainWindow->setCentralWidget(console);
    mainWindow->resize(802, 610);
    
    QObject::connect(console, SIGNAL(finished()), mainWindow, SLOT(close()));
    */
    //showFullScreen();

    text->setReadOnly(true);

    web->page()->setLinkDelegationPolicy(QWebPage::DelegateAllLinks);

    // Web signals
    connect(web->page(),SIGNAL(linkClicked(const QUrl&)),this,
            SLOT(slotLinkHandle(const QUrl&)));
    connect(web,SIGNAL(loadStarted()),this,SLOT(slotWebLoadStarted()));
    connect(web,SIGNAL(loadFinished(bool)),this,SLOT(slotWebLoadFinished(bool)));
    connect(web,SIGNAL(loadProgress(int)),this,SLOT(slotWebLoadProgress(int)));

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

    // Set tittle
    setWindowTitle(tr("OpenGNSys Browser"));

    if(!readEnvironmentValues())
        text->insertPlainText(tr("Any environment variable/s didn't be setted\n"));

    if(env.contains("OGLOGFILE") && env["OGLOGFILE"]!="")
    {
        logfile=new QFile(env["OGLOGFILE"]);
        if(!logfile->open(QIODevice::WriteOnly | QIODevice::Text |
                    QIODevice::Append))
        {
            text->insertPlainText(tr("The log file couldn't be opened: ")+logfile->fileName());
            delete logfile;
            logfile=NULL;
        }
        else
            logstream=new QTextStream(logfile);
    }
}

MainWindow::~MainWindow()
{
    if(logfile)
    {
        logfile->close();
        delete logfile;
    }
    if(logstream)
        delete logstream;
}

void MainWindow::slotLinkHandle(const QUrl &url)
{
    QString string = url.toString();
    qDebug() << string;
    // Si es un link del tipo PROTOCOL lo ejecutamos
    if(string.startsWith(PROTOCOL))
    {
        string=string.remove(0,QString(PROTOCOL).length());
        QStringList list=string.split(" ",QString::SkipEmptyParts);
        QString command=list.takeFirst();
        process->setReadChannel(QProcess::StandardOutput);
        // Le ponemos el mismo entorno que tiene el browser ahora mismo
        process->setEnvironment(QProcess::systemEnvironment());
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
    // If any error ocurred, show a pop up
    // Sometimes when the url hasn't got a dot, i.e /var/www/pageweb,
    // the return value is always true so we check the bytes received too
    if(ok == false || web->page()->totalBytes() == 0)
    {
        qDebug()<<"Error accediendo a la web";
        QMessageBox msgBox;
        msgBox.setText(tr("The web page couldn't load. What do you want to do?"));

        QPushButton *reloadButton = msgBox.addButton(tr("Reload"), QMessageBox::ActionRole);
        msgBox.addButton(QMessageBox::Abort);

        msgBox.exec();

        if (msgBox.clickedButton() == reloadButton)
        {
            web->reload();
        }
        else
        {
            close();
        }
    }
    else
        qDebug()<<"Descarga finalizada satisfactoriamente";
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
        if(logstream)
            *logstream<<CURRENT_TIME()<<": "<<buf;
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

int MainWindow::readEnvironmentValues()
{
    // The return value
    int ret=true;

    // Get all environment variables
    QStringList environmentlist=QProcess::systemEnvironment();
    // This is the list of the important variables
    QStringList variablelist=QString(ENVIRONMENT).split(",");

    // This is an auxiliar variable
    QStringList stringlist;

    foreach (QString str,variablelist)
    {
        // Look for the variable in the environment 
        stringlist=environmentlist.filter(str+"=");

        if(stringlist.isEmpty())
        {
            env[str]="";
            ret=false;
        }
        else
        {
            // Get the first element and get the value part
            env[str]=(stringlist.first().split("="))[1];
        }
    }

    return ret;
}

