#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#define COMMAND "command"
#define COMMAND_CONFIRM "command+confirm"
#define COMMAND_WITH_CONFIRMATION "commandwithconfirmation"  // Backwards compatibility
#define COMMAND_OUTPUT "command+output"
#define COMMAND_CONFIRM_OUTPUT "command+confirm+output"
#define COMMAND_OUTPUT_CONFIRM "command+output+confirm"
#define ENVIRONMENT "OGLOGFILE,ogactiveadmin,DEFAULTSPEED"

#include <QWidget>
#include <QProcess>
#include <QMap>
#include <QMainWindow>
#include <QNetworkReply>
#include <QSslError>
#include "digitalclock.h"

class QWebView;
class QTextEdit;
class QVBoxLayout;
class QProcess;
class QStringList;
class QString;
class QUrl;
class QFile;
class QTextStream;
class QTermWidget;
class QProgressBar;
class QLineEdit;
class QLabel;

class MainWindow : public QMainWindow
{
    Q_OBJECT

    public:
        MainWindow(QWidget *parent = 0);
        ~MainWindow();

    public slots:
        // Funcion que maneja los links
        void slotLinkHandle(const QUrl& url);
        void slotWebLoadStarted();
        void slotWebLoadFinished(bool ok);
        void slotWebLoadProgress(int progress);
	void slotSslErrors(QNetworkReply* reply);

        // Funciones que manejan cada vez que el proceso hace algo
        void slotProcessStarted();
        void slotProcessFinished(int code,QProcess::ExitStatus status);
        void slotProcessError(QProcess::ProcessError error);
        void slotProcessOutput();
        void slotProcessErrorOutput();

        // Funcion para crear terminales
        void slotCreateTerminal();
        void slotDeleteTerminal();

        // Funcion para el webar
        void slotWebBarReturnPressed();
        void slotUrlChanged(const QUrl &url);

    //Functions
    protected:
        int readEnvironmentValues();
        void print(QString s);
        void write(QString s);
        void captureOutputForStatusBar(QString output);
        void startProgressBar();
        void finishProgressBar();
        void executeCommand(QString &string);
        QString readSpeed();
        void showErrorMessage(QString string);

    protected:
        QWebView *m_web;
        QTextEdit *m_output;
        QLabel *m_logo;
        QProgressBar *m_progressBar;
        QLabel *m_speedInfo;
        DigitalClock *m_clock;
        QTabWidget *m_tabs;
        QLineEdit *m_webBar;

        QProcess *m_process;

        QMap<QString,QString> m_env;
        QFile *m_logfile;
        QTextStream *m_logstream;

        int m_numberTerminal;
};

#endif // MAINWINDOW_H
