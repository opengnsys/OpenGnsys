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
#include <QNetworkReply>
#include <QSslError>
#include <QTimer>
#include <libintl.h>

#include "qtermwidget.h"
#include "digitalclock.h"

#define BUFFERSIZE 2048
#define REGEXP_STRING "^\\[(\\d+)\\]"

#define CURRENT_TIME() QDateTime::currentDateTime().toString("dd/MM/yyyy hh:mm:ss")

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent),m_web(new QWebView()),m_output(new QTextEdit()),
      m_process(new QProcess(this)),
      m_logfile(0),m_logstream(0),m_numberTerminal(0)
{
    // Graphic
    showFullScreen();
    setWindowTitle(tr("OpenGnsys Browser"));
    setCentralWidget(m_web);
    readEnvironmentValues();

    // Open the log file for append
    if(m_env.contains("OGLOGFILE") && m_env["OGLOGFILE"]!="")
    {
        QFile* m_logfile=new QFile(m_env["OGLOGFILE"]);
        if(!m_logfile->open(QIODevice::WriteOnly | QIODevice::Text | QIODevice::Append))
        {
            delete m_logfile;
            print(tr(gettext("El fichero de log no ha podido ser abierto: "))+m_env["OGLOGFILE"]+".");
        }
        else
        {
            m_logstream=new QTextStream(m_logfile);
        }
    }

    // Output
    m_output->setReadOnly(true);
    m_output->setFontPointSize(16);

    // Button Dock
    QDockWidget* dock=new QDockWidget();
    dock->setAllowedAreas(Qt::BottomDockWidgetArea);
    QWidget* dummy=new QWidget();
    dummy->setMaximumHeight(0);
    dock->setTitleBarWidget(dummy);

    // TabWidget
    m_tabs=new QTabWidget(dock);
    QPushButton *button=new QPushButton(tr(gettext("&Nueva Terminal")));
    button->setFocusPolicy(Qt::TabFocus);
    m_tabs->setCornerWidget(button);
    m_tabs->setFocusPolicy(Qt::NoFocus);
    m_tabs->addTab(m_output,tr(gettext("Salida")));
    slotCreateTerminal();
    // Assign tabs to dock
    dock->setWidget(m_tabs);
    // Assign tabs dock to the mainwindow if admin mode is active
    if(m_env.contains("ogactiveadmin") && m_env["ogactiveadmin"] == "true")
        addDockWidget(Qt::BottomDockWidgetArea,dock);

    // Top Dock
    dock=new QDockWidget();
    dock->setAllowedAreas(Qt::TopDockWidgetArea);
    QWidget* dummy2=new QWidget();
    dummy2->setMaximumHeight(0);
    dock->setTitleBarWidget(dummy2);
    // WebBar
    m_webBar=new QLineEdit(dock);
    // WebBar to dock
    dock->setWidget(m_webBar);
    // Assign top dock to the mainwindow if admin mode is active
    if(m_env.contains("ogactiveadmin") && m_env["ogactiveadmin"] == "true")
        addDockWidget(Qt::TopDockWidgetArea,dock);

    // Status bar
    QStatusBar* st=statusBar();
    st->setSizeGripEnabled(false);
    // OpenGnsys logo (or alternate text)
    m_logo=new QLabel();
    QPixmap logo;
    if(logo.load("/opt/opengnsys/lib/pictures/oglogo.png"))
        m_logo->setPixmap(logo);
    else
        m_logo->setText("OG");
    m_logo->setToolTip(tr(gettext("Proyecto OpenGnsys"))+"\nhttps://opengnsys.es");
    // Progress bar
    m_progressBar=new QProgressBar(this);
    m_progressBar->setRange(0,100);
    // Connection speed
    QString speed=readSpeed();
    m_speedInfo=new QLabel(speed);
    m_speedInfo->setAlignment(Qt::AlignCenter);
    if(m_env.contains("DEFAULTSPEED") && m_env["DEFAULTSPEED"]!="")
        if(speed.compare(m_env["DEFAULTSPEED"])!=0)
            m_speedInfo->setStyleSheet("background-color: darkred; color: white; font-weight: bold;");
    // Clock
    m_clock=new DigitalClock(this);

    m_web->page()->setLinkDelegationPolicy(QWebPage::DelegateAllLinks);

    // Web signals
    connect(m_web,SIGNAL(linkClicked(const QUrl&)),this,
            SLOT(slotLinkHandle(const QUrl&)));
    connect(m_web,SIGNAL(loadStarted()),this,SLOT(slotWebLoadStarted()));
    connect(m_web,SIGNAL(loadFinished(bool)),this,SLOT(slotWebLoadFinished(bool)));
    connect(m_web,SIGNAL(loadProgress(int)),this,SLOT(slotWebLoadProgress(int)));
    connect(m_web,SIGNAL(urlChanged(const QUrl&)),this,SLOT(slotUrlChanged(const QUrl&)));
    // Ignore SSL errors.
    connect(m_web->page()->networkAccessManager(),
            SIGNAL(sslErrors(QNetworkReply*, const QList<QSslError> &)), this,
            SLOT(slotSslErrors(QNetworkReply*)));

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
    // Check if it's executing another process
    if(m_process->state() != QProcess::NotRunning)
    {
        print(tr(gettext("Hay otro proceso en ejecución. Por favor espere.")));
        return;
    }
    QString urlString = url.toString();
    QString urlScheme = url.scheme();
    // Clear the output widget for a normal user
    if(! m_env.contains("ogactiveadmin") || m_env["ogactiveadmin"] != "true")
    {
        m_output->clear();
    }
    if(urlScheme == COMMAND_CONFIRM || urlScheme == COMMAND_CONFIRM_OUTPUT ||
       urlScheme == COMMAND_OUTPUT_CONFIRM || urlScheme == COMMAND_WITH_CONFIRMATION)
    {
        // For all command with confirmation links, show a popup box
        QMessageBox msgBox;
        msgBox.setWindowFlags(Qt::CustomizeWindowHint | Qt::WindowTitleHint);
        msgBox.setWindowTitle(tr(gettext("AVISO")));
        msgBox.setIcon(QMessageBox::Question);
        msgBox.setTextFormat(Qt::RichText);
        msgBox.setText(tr(gettext("La siguiente acci&oacute;n puede modificar datos o tardar varios minutos. El equipo no podr&aacute; ser utilizado durante su ejecuci&oacute;n.")));
        QPushButton *execButton = msgBox.addButton(tr(gettext("Ejecutar")), QMessageBox::ActionRole);
        msgBox.addButton(tr(gettext("Cancelar")), QMessageBox::RejectRole);
        msgBox.setDefaultButton(execButton);
        msgBox.exec();
        // Continue if user press the execution button
        if (msgBox.clickedButton() == execButton)
        {
            // For command with confirmation and output link, show an output window to non-admin user
            if((urlScheme == COMMAND_CONFIRM_OUTPUT || urlScheme == COMMAND_OUTPUT_CONFIRM) &&
               (! m_env.contains("ogactiveadmin") || m_env["ogactiveadmin"] != "true"))
            {
                int w=MainWindow::width(), h=MainWindow::height();
                m_output->setWindowFlags(Qt::Window);
                m_output->move(100, 100);
                m_output->setFixedSize(w*0.8-100, h*0.8-100);
                m_output->show();
            }
            // Execute the command
            executeCommand(urlString.remove(0, urlScheme.length()+1));
        }
    }
    else if(urlScheme == COMMAND || urlScheme == COMMAND_OUTPUT)
    {
        // For command with output link, show an output window to non-admin user
        if(urlScheme == COMMAND_OUTPUT &&
           (! m_env.contains("ogactiveadmin") || m_env["ogactiveadmin"] != "true"))
        {
            int w=MainWindow::width(), h=MainWindow::height();
            m_output->setWindowFlags(Qt::Window);
            m_output->move(100, 100);
            m_output->setFixedSize(w*0.8-100, h*0.8-100);
            m_output->show();
        }
        // Execute the command
        executeCommand(urlString.remove(0, urlScheme.length()+1));
    }
    else
    {
        // For other link, load webpage
        m_web->load(url);
    }
}

void MainWindow::slotWebLoadStarted()
{
    startProgressBar();
    m_progressBar->setFormat(gettext("%p% Cargando"));
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
        msgBox.setWindowFlags(Qt::CustomizeWindowHint | Qt::WindowTitleHint);
        msgBox.setWindowTitle(tr(gettext("AVISO")));
        msgBox.setIcon(QMessageBox::Question);
        msgBox.setTextFormat(Qt::RichText);
        msgBox.setText(tr(gettext("La p&aacute;gina no se puede cargar.")));

        QPushButton *reloadButton = msgBox.addButton(tr(gettext("Recargar")), QMessageBox::ActionRole);
        msgBox.addButton(tr(gettext("Abortar")), QMessageBox::RejectRole);
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

void MainWindow::slotSslErrors(QNetworkReply* reply)
{
    reply->ignoreSslErrors();
}

void MainWindow::slotProcessStarted()
{
    startProgressBar();
}

void MainWindow::slotProcessOutput()
{
    m_process->setReadChannel(QProcess::StandardOutput);
    char buf[BUFFERSIZE];
    while((m_process->readLine(buf,BUFFERSIZE) > 0))
    {
        QString s(buf);
        if(m_env.contains("ogactiveadmin") && m_env["ogactiveadmin"] == "true")
        {
            m_output->insertPlainText(tr("Proc. stdout: "));
        }
        print(s);
        captureOutputForStatusBar(s);
    }
}

void MainWindow::slotProcessErrorOutput()
{
    m_process->setReadChannel(QProcess::StandardError);
    char buf[BUFFERSIZE];
    while((m_process->readLine(buf,BUFFERSIZE) > 0))
    {
        QString s(buf);
        if(m_env.contains("ogactiveadmin") && m_env["ogactiveadmin"] == "true")
        {
            m_output->insertPlainText(tr("Proc. stderr: "));
        }
        m_output->setTextColor(QColor(Qt::darkBlue));
        print(s);
        m_output->setTextColor(QColor(Qt::black));
    }
}

void MainWindow::slotProcessFinished(int code, QProcess::ExitStatus status)
{
    if(m_env.contains("ogactiveadmin") && m_env["ogactiveadmin"] == "true")
    {
        // Admin user: show process status
        if(status==QProcess::NormalExit)
        {
            if(code > 0)
            {
                m_output->setTextColor(QColor(Qt::darkRed));
            }
            print("\n"+tr(gettext("Fin del proceso. Valor de retorno: "))+QString::number(code));
        }
        else
        {
            m_output->setTextColor(QColor(Qt::darkRed));
            print("\n"+tr(gettext("El proceso ha fallado inesperadamente. Salida: ")+code));
        }
        m_output->setTextColor(QColor(Qt::black));
    }
    else
    {
        // Non-admin user: show instruction to close the popup window
        write(tr(gettext("Fin del proceso. Valor de retorno: "))+QString::number(code));
        m_output->setFontUnderline(true);
        print("\n\n"+tr(gettext("AVISO: Pulsar el botón superior derecho para cerrar"))+" [X]");
        m_output->setFontUnderline(false);
    }
    // On error, show a message box
    if(code > 0 && ! m_output->isActiveWindow())
    {
        showErrorMessage(gettext("Código de salida: ")+QString::number(code));
    }
    finishProgressBar();
}

void MainWindow::slotProcessError(QProcess::ProcessError error)
{
    QString errorMsg;
    switch(error)
    {
        case QProcess::FailedToStart:
            errorMsg=tr(gettext("Imposible lanzar el proceso."));
            break;
        case QProcess::WriteError:
            errorMsg=tr(gettext("Error de escritura en el proceso."));
            break;
        case QProcess::ReadError:
            errorMsg=tr(gettext("Error de lectura del proceso."));
            break;
        // No capturo crashed porque la pillo por finished
        case QProcess::Crashed:
        case QProcess::Timedout:
            break;
        case QProcess::UnknownError:
        default:
            errorMsg=tr(gettext("Error desconocido."));
            break;
    }
    // Print error and show message box with timeout.
    if(!errorMsg.isNull()) {
        m_output->setTextColor(QColor(Qt::darkRed));
        print(errorMsg);
        m_output->setTextColor(QColor(Qt::black));
        showErrorMessage(errorMsg);
    }
    finishProgressBar();
}

void MainWindow::slotCreateTerminal()
{
    QTermWidget* console = new QTermWidget(1,this);
    QFont font = QApplication::font();
    font.setFamily("DejaVu Sans Mono");
    font.setPointSize(12);
    
    console->setTerminalFont(font);
    console->setFocusPolicy(Qt::StrongFocus);
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

// Write a string to the log file
void MainWindow::write(QString s)
{
    if(! s.endsWith("\n"))
        s+="\n";
    if(m_logstream)
    {
        *m_logstream<<CURRENT_TIME()<<": browser: "<<s;
        m_logstream->flush();
    }
}

// Print and log a string
void MainWindow::print(QString s)
{
    if(! s.endsWith("\n"))
        s+="\n";
    write(s);
    if(m_output)
        m_output->insertPlainText(s);
}

// Show message in status bar
void MainWindow::captureOutputForStatusBar(QString output)
{
    // Modify the status bar
    output=output.trimmed();
    // Get percentage (string starts with "[Number]")
    QRegExp regexp(REGEXP_STRING);
    if(regexp.indexIn(output) != -1)
    {
        int pass=regexp.cap(1).toInt();
        output.replace(regexp,"");
        m_progressBar->setValue(pass);
        m_progressBar->setFormat("%p%"+output);
    }
}

// Init status bar
void MainWindow::startProgressBar()
{
    QStatusBar* st=statusBar();
    st->clearMessage();
    st->addWidget(m_logo);
    st->addWidget(m_progressBar,90);
    st->addWidget(m_speedInfo,5);
    st->addWidget(m_clock,5);
    m_progressBar->show();
    m_clock->show();
    m_web->setEnabled(false);
}

// Reset status bar
void MainWindow::finishProgressBar()
{
    m_progressBar->reset();
    m_web->setEnabled(true);
}

// Execute a command
void MainWindow::executeCommand(QString &string)
{
    QStringList list=string.split(" ",QString::SkipEmptyParts);
    QString program=list.takeFirst();
    m_process->setReadChannel(QProcess::StandardOutput);
    // Assign the same Browser's environment to the process
    m_process->setEnvironment(QProcess::systemEnvironment());
    m_process->start(program,list);
    // Only show the command line to admin user
    if(m_env.contains("ogactiveadmin") && m_env["ogactiveadmin"] == "true")
    {
        m_output->setTextColor(QColor(Qt::darkGreen));
        print(tr(gettext("Lanzando el comando: "))+string);
        m_output->setTextColor(QColor(Qt::black));
    }
    else
    {
        write(tr(gettext("Lanzando el comando: "))+string);
    }
    startProgressBar();
}

// Returns communication speed
QString MainWindow::readSpeed() {
    if(m_env.contains("OGLOGFILE"))
    {
        QString infoFile=m_env["OGLOGFILE"].replace(".log", ".info.html");
        QString command="grep -hoe \"[0-9]*Mb/s\" "+infoFile+" 2>/dev/null";
        QProcess process;
        process.start(command);
        process.waitForFinished();
        QString speed(process.readAllStandardOutput());
        return speed.simplified();
    }
    else
    {
        return QString("");
    }
}

// Show an error box with timeout
void MainWindow::showErrorMessage(QString text)
{
    QMessageBox* msgBox=new QMessageBox();
    msgBox->setWindowFlags(Qt::CustomizeWindowHint | Qt::WindowTitleHint);
    msgBox->setWindowTitle(gettext("ERROR"));
    msgBox->setIcon(QMessageBox::Warning);
    msgBox->setText(text);
    msgBox->show();
    QTimer::singleShot(5000, msgBox, SLOT(close()));
}
