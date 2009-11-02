#include "mainwindow.h"
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
#include <QProgressBar>
#include <QTabWidget>
#include <QLineEdit>

#include "qtermwidget.h"

#include "mainwindow.moc"

#define BUFFERSIZE 2048

#define CURRENT_TIME() QDateTime::currentDateTime().toString("dd/MM/yy hh:mm:ss")

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent),web(new QWebView()),output(new QTextEdit()),
      process(new QProcess(this)),
      logfile(NULL),logstream(NULL),numberTerminal(0)
{
    // Graphic
    //showFullScreen();

    setWindowTitle(tr("OpenGNSys Browser"));

    setCentralWidget(web);

    // Output
    output->setReadOnly(true);

    // Button Dock
    QDockWidget* dock=new QDockWidget();
    dock->setAllowedAreas(Qt::BottomDockWidgetArea);
    QWidget* dummy=new QWidget();
    dummy->setMaximumHeight(0);
    dock->setTitleBarWidget(dummy);

    // TabWidget
    tabs=new QTabWidget(dock);
    QPushButton *button=new QPushButton(tr("&New Term"));
    button->setFocusPolicy(Qt::TabFocus);
    tabs->setCornerWidget(button);
    tabs->setFocusPolicy(Qt::NoFocus);

    tabs->addTab(output,tr("Output"));
    slotCreateTerminal();

    // Las pestanyas al dock
    dock->setWidget(tabs);

    // Y el dock al mainwindow
    addDockWidget(Qt::BottomDockWidgetArea,dock);

    // Top Dock
    dock=new QDockWidget();
    dock->setAllowedAreas(Qt::TopDockWidgetArea);
    QWidget* dummy2=new QWidget();
    dummy2->setMaximumHeight(0);
    dock->setTitleBarWidget(dummy2);

    // WebBar
    webBar=new QLineEdit(dock);

    // WebBar al dock
    dock->setWidget(webBar);

    // dock al mainwindow
    addDockWidget(Qt::TopDockWidgetArea,dock);

    // Status bar
    QStatusBar* st=statusBar();
    st->setSizeGripEnabled(false);
    progressBar=new QProgressBar(this);
    progressBar->setMinimum(0);
    progressBar->setMaximum(100);

    web->page()->setLinkDelegationPolicy(QWebPage::DelegateAllLinks);

    // Web signals
    connect(web,SIGNAL(linkClicked(const QUrl&)),this,
            SLOT(slotLinkHandle(const QUrl&)));
    connect(web,SIGNAL(loadStarted()),this,SLOT(slotWebLoadStarted()));
    connect(web,SIGNAL(loadFinished(bool)),this,SLOT(slotWebLoadFinished(bool)));
    connect(web,SIGNAL(loadProgress(int)),this,SLOT(slotWebLoadProgress(int)));
    connect(web,SIGNAL(urlChanged(const QUrl&)),this,
            SLOT(slotUrlChanged(const QUrl&)));

    // Process signals
    connect(process,SIGNAL(started()),this,SLOT(slotProcessStarted()));
    connect(process,SIGNAL(finished(int,QProcess::ExitStatus)),
            this,SLOT(slotProcessFinished(int,QProcess::ExitStatus)));

    connect(process,SIGNAL(error(QProcess::ProcessError)),
            this,SLOT(slotProcessError(QProcess::ProcessError)));

    connect(process,SIGNAL(readyReadStandardOutput()),this,SLOT(slotProcessOutput()));
    connect(process,SIGNAL(readyReadStandardError()),
            this,SLOT(slotProcessErrorOutput()));

    // Dock signals
    connect(button,SIGNAL(clicked()),this,SLOT(slotCreateTerminal()));
    connect(webBar,SIGNAL(returnPressed()),this,SLOT(slotWebBarReturnPressed()));

    if(!readEnvironmentValues())
        output->insertPlainText(tr("Any environment variable/s didn't be setted\n"));

    if(env.contains("OGLOGFILE") && env["OGLOGFILE"]!="")
    {
        logfile=new QFile(env["OGLOGFILE"]);
        if(!logfile->open(QIODevice::WriteOnly | QIODevice::Text |
                    QIODevice::Append))
        {
            output->insertPlainText(tr("The log file couldn't be opened: ")+logfile->fileName());
            delete logfile;
            logfile=NULL;
        }
        else
            logstream=new QTextStream(logfile);
    }

    QStringList arguments=QCoreApplication::arguments();
    webBar->setText(arguments[1]);
    web->load(QUrl(arguments[1]));
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
    // Si ya hay un proceso ejectuandose
    if(process->state()!=QProcess::NotRunning)
    {
      output->insertPlainText(tr("There is a process running."));
      return;
    }
    
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
    QStatusBar* st=statusBar();
    st->clearMessage();
    st->addWidget(progressBar,100);
    progressBar->show();
    progressBar->setFormat("%p% Loading");

    qDebug()<<"Empieza la carga de la web";
}

void MainWindow::slotWebLoadProgress(int progress)
{
    progressBar->setValue(progress);
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
    {
        qDebug()<<"Descarga finalizada satisfactoriamente";

        QStatusBar* st=statusBar();
        st->removeWidget(progressBar);
        st->showMessage(tr("Ready"));
    }
}


void MainWindow::slotUrlChanged(const QUrl &url)
{
    webBar->setText(url.toString());
    qDebug()<<"Change"<<endl;
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
        output->insertPlainText(buf);
        /*
        QString str="<b>";
        str+=buf;
        str+="</b>";
        output->insertHtml(str);
        */
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
        output->insertPlainText(buf);
        if(logstream)
            *logstream<<CURRENT_TIME()<<": "<<buf;
    }
}

void MainWindow::slotProcessFinished(int code,QProcess::ExitStatus status)
{
    if(status==QProcess::NormalExit)
    {
        qDebug()<<"Finished: "<<code<<" "<<status<<endl;
    }
    else
    {
        qDebug()<<"Ha petado"<<endl;
        qDebug()<<"Finished: "<<code<<" "<<status<<endl;
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

void MainWindow::slotCreateTerminal()
{
    QTermWidget* console = new QTermWidget(1,this);
    QFont font = QApplication::font();
    font.setFamily("Terminus");
    font.setPointSize(12);
    
    console->setTerminalFont(font);
    console->setFocusPolicy(Qt::StrongFocus);
    
    //console->setColorScheme(COLOR_SCHEME_BLACK_ON_LIGHT_YELLOW);
    console->setScrollBarPosition(QTermWidget::ScrollBarRight);

    ++numberTerminal;

    connect(console,SIGNAL(finished()),this,SLOT(slotDeleteTerminal()));

    QString name=tr("Term ")+QString::number(numberTerminal);
    tabs->addTab(console,name);
}

void MainWindow::slotDeleteTerminal()
{
  QWidget *widget = qobject_cast<QWidget *>(sender());
  Q_ASSERT(widget);
  tabs->removeTab(tabs->indexOf(widget));
}

void MainWindow::slotWebBarReturnPressed()
{
  QUrl url(webBar->text());
  if(url.isValid())
    slotLinkHandle(url);
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
