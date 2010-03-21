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

#define BUFFERSIZE 2048
#define EXPREG_RESET "^\\[(\\d+),(\\d+)\\]\\n"
#define EXPREG_A_PASS "^\\[(\\d+)\\]\\s"

#define CURRENT_TIME() QDateTime::currentDateTime().toString("dd/MM/yy hh:mm:ss")

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent),m_web(new QWebView()),m_output(new QTextEdit()),
      m_process(new QProcess(this)),
      m_logfile(0),m_logstream(0),m_numberTerminal(0)
{
    // Graphic
    showFullScreen();

    setWindowTitle(tr("OpenGNSys Browser"));

    setCentralWidget(m_web);

    readEnvironmentValues();

    // Output
    m_output->setReadOnly(true);

    // Button Dock
    QDockWidget* dock=new QDockWidget();
    dock->setAllowedAreas(Qt::BottomDockWidgetArea);
    QWidget* dummy=new QWidget();
    dummy->setMaximumHeight(0);
    dock->setTitleBarWidget(dummy);

    // TabWidget
    m_tabs=new QTabWidget(dock);
    QPushButton *button=new QPushButton(tr("&New Term"));
    button->setFocusPolicy(Qt::TabFocus);
    m_tabs->setCornerWidget(button);
    m_tabs->setFocusPolicy(Qt::NoFocus);

    m_tabs->addTab(m_output,tr("Output"));
    slotCreateTerminal();

    // Las pestanyas al dock
    dock->setWidget(m_tabs);

    // Y el dock al mainwindow
    if(m_env.contains("boot") && m_env["boot"] == "admin")
      addDockWidget(Qt::BottomDockWidgetArea,dock);

    // Top Dock
    dock=new QDockWidget();
    dock->setAllowedAreas(Qt::TopDockWidgetArea);
    QWidget* dummy2=new QWidget();
    dummy2->setMaximumHeight(0);
    dock->setTitleBarWidget(dummy2);

    // WebBar
    m_webBar=new QLineEdit(dock);

    // WebBar al dock
    dock->setWidget(m_webBar);

    // dock al mainwindow
    if(m_env.contains("boot") && m_env["boot"] == "admin")
      addDockWidget(Qt::TopDockWidgetArea,dock);

    // Status bar
    QStatusBar* st=statusBar();
    st->setSizeGripEnabled(false);
    m_progressBar=new QProgressBar(this);

    m_web->page()->setLinkDelegationPolicy(QWebPage::DelegateAllLinks);

    // Web signals
    connect(m_web,SIGNAL(linkClicked(const QUrl&)),this,
            SLOT(slotLinkHandle(const QUrl&)));
    connect(m_web,SIGNAL(loadStarted()),this,SLOT(slotWebLoadStarted()));
    connect(m_web,SIGNAL(loadFinished(bool)),this,SLOT(slotWebLoadFinished(bool)));
    connect(m_web,SIGNAL(loadProgress(int)),this,SLOT(slotWebLoadProgress(int)));
    connect(m_web,SIGNAL(urlChanged(const QUrl&)),this,
            SLOT(slotUrlChanged(const QUrl&)));

    // Process signals
    connect(m_process,SIGNAL(started()),this,SLOT(slotProcessStarted()));
    connect(m_process,SIGNAL(finished(int,QProcess::ExitStatus)),
            this,SLOT(slotProcessFinished(int,QProcess::ExitStatus)));

    connect(m_process,SIGNAL(error(QProcess::ProcessError)),
            this,SLOT(slotProcessError(QProcess::ProcessError)));

    connect(m_process,SIGNAL(readyReadStandardOutput()),this,SLOT(slotProcessOutput()));
    connect(m_process,SIGNAL(readyReadStandardError()),
            this,SLOT(slotProcessErrorOutput()));

    // Dock signals
    connect(button,SIGNAL(clicked()),this,SLOT(slotCreateTerminal()));
    connect(m_webBar,SIGNAL(returnPressed()),this,SLOT(slotWebBarReturnPressed()));

    if(m_env.contains("OGLOGFILE") && m_env["OGLOGFILE"]!="")
    {
        QFile* file=new QFile(m_env["OGLOGFILE"]);
        if(!file->open(QIODevice::WriteOnly | QIODevice::Text |
                    QIODevice::Append))
        {
            delete file;
            print(tr("The log file couldn't be opened: ")+m_env["OGLOGFILE"]+".");
        }
        else
        {
            m_logfile=file;
            m_logstream=new QTextStream(m_logfile);
        }
    }

    QStringList arguments=QCoreApplication::arguments();
    m_webBar->setText(arguments[1]);
    m_web->load(QUrl(arguments[1]));
}

MainWindow::~MainWindow()
{
    if(m_logfile)
    {
        m_logfile->close();
        delete m_logfile;
    }
    if(m_logstream)
        delete m_logstream;
}

void MainWindow::slotLinkHandle(const QUrl &url)
{
    // Si ya hay un proceso ejectuandose
    if(m_process->state()!=QProcess::NotRunning)
    {
      print(tr("There is a process running. Please wait a moment."));
      return;
    }
 
    QString urlString = url.toString();
    // Si es un link del tipo PROTOCOL lo ejecutamos
    if(urlString.startsWith(PROTOCOL))
    {
        urlString=urlString.remove(0,QString(PROTOCOL).length());
        QStringList list=urlString.split(" ",QString::SkipEmptyParts);
        QString program=list.takeFirst();
        m_process->setReadChannel(QProcess::StandardOutput);
        // Le ponemos el mismo entorno que tiene el browser ahora mismo
        m_process->setEnvironment(QProcess::systemEnvironment());
        m_process->start(program,list);
        print(tr("Launching the command: ")+program+" "+list.join(" ")+".");
        startProgressBar();
    }
    else
    {
        m_web->load(url);
    }
}

void MainWindow::slotWebLoadStarted()
{
    startProgressBar();
    m_progressBar->setFormat("%p% Loading");
    m_progressBar->setMinimum(0);
    m_progressBar->setMaximum(100);
}

void MainWindow::slotWebLoadProgress(int progress)
{
    m_progressBar->setValue(progress);
}

void MainWindow::slotWebLoadFinished(bool ok)
{
    // If any error ocurred, show a pop up
    // Sometimes when the url hasn't got a dot, i.e /var/www/pageweb,
    // the return value is always true so we check the bytes received too
    if(ok == false)
    {
        QMessageBox msgBox;
        msgBox.setText(tr("The web page couldn't load. What do you want to do?"));

        QPushButton *reloadButton = msgBox.addButton(tr("Reload"), QMessageBox::ActionRole);
        msgBox.addButton(QMessageBox::Abort);

        msgBox.exec();

        if (msgBox.clickedButton() == reloadButton)
        {
            m_web->reload();
        }
        else
        {
            close();
        }
    }
    else
    {
        finishProgressBar();
    }
}

void MainWindow::slotUrlChanged(const QUrl &url)
{
    m_webBar->setText(url.toString());
}

void MainWindow::slotProcessStarted()
{
    print(tr("Launched successfully."));
    startProgressBar();
    m_progressBar->setMinimum(0);
    m_progressBar->setMaximum(0);
}

void MainWindow::slotProcessOutput()
{
    m_process->setReadChannel(QProcess::StandardOutput);
    char buf[BUFFERSIZE];
    while((m_process->readLine(buf,BUFFERSIZE) > 0))
    {
        print(tr("Proc. Output: ")+buf);
        QString s(buf);
        captureOutputForStatusBar(s);
    }
}

void MainWindow::slotProcessErrorOutput()
{
    m_process->setReadChannel(QProcess::StandardError);
    char buf[BUFFERSIZE];
    while((m_process->readLine(buf,BUFFERSIZE) > 0))
    {
        print(tr("Proc. Error: ")+buf);
    }
}

void MainWindow::slotProcessFinished(int code,QProcess::ExitStatus status)
{
    if(status==QProcess::NormalExit)
    {
        print(tr("Process fisnished correctly. Return value: ")+QString::number(code));
    }
    else
    {
        print(tr("Process crashed. Output: "+code));
    }
    finishProgressBar();
}

void MainWindow::slotProcessError(QProcess::ProcessError error)
{
    switch(error)
    {
        case QProcess::FailedToStart:
            print(tr("Impossible to launch the process."));
            break;
        case QProcess::WriteError:
            print(tr("Write error happened in the process."));
            break;
        case QProcess::ReadError:
            print(tr("Read error happened in the process."));
            break;
        // No capturo crashed porque la pillo por finished
        case QProcess::Crashed:
        case QProcess::Timedout:
            break;
        case QProcess::UnknownError:
        default:
            print(tr("Unknown error."));
            break;
    }
    finishProgressBar();
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

    ++m_numberTerminal;

    connect(console,SIGNAL(finished()),this,SLOT(slotDeleteTerminal()));

    QString name=tr("Term ")+QString::number(m_numberTerminal);
    m_tabs->addTab(console,name);
}

void MainWindow::slotDeleteTerminal()
{
    QWidget *widget = qobject_cast<QWidget *>(sender());
    Q_ASSERT(widget);
    m_tabs->removeTab(m_tabs->indexOf(widget));
    delete widget;
}

void MainWindow::slotWebBarReturnPressed()
{
    QUrl url(m_webBar->text());
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
            m_env[str]="";
            ret=false;
        }
        else
        {
            // Get the first element and get the value part
            m_env[str]=(stringlist.first().split("="))[1];
        }
    }

    return ret;
}

void MainWindow::print(QString s)
{
  if(!s.endsWith("\n"))
    s+="\n";
  if(m_logstream)
    *m_logstream<<CURRENT_TIME()<<": "<<s;
  if(m_output)
    m_output->insertPlainText(s);
}

void MainWindow::captureOutputForStatusBar(QString output)
{
  // Capturar para modificar status bar
  output=output.trimmed();

  QRegExp rxReset(EXPREG_RESET);
  QRegExp rxPass(EXPREG_A_PASS);
  if(rxReset.exactMatch(output))
  {
    int minimum=rxReset.cap(1).toInt();
    int maximum=rxReset.cap(2).toInt();
    m_progressBar->setMinimum(minimum);
    m_progressBar->setMaximum(maximum);
    m_progressBar->setValue(minimum);
  }
  else if(rxPass.indexIn(output)!=-1)
  {
    int pass=rxPass.cap(1).toInt();
    output.replace(rxPass,"");
    m_progressBar->setValue(pass);
    m_progressBar->setFormat("%p% "+output);
  }
}

void MainWindow::startProgressBar()
{
    QStatusBar* st=statusBar();
    st->clearMessage();
    st->addWidget(m_progressBar,100);
    m_progressBar->show();
    m_web->setEnabled(false);
}

void MainWindow::finishProgressBar()
{
    QStatusBar* st=statusBar();
    st->removeWidget(m_progressBar);
    st->showMessage(tr("Ready"));
    m_web->setEnabled(true);
}
