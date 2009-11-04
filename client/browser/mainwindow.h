#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#define PROTOCOL "command:"
#define ENVIRONMENT "OGLOGFILE"
//#define ENVIRONMENT "OGIP,OGSERVER,OGLOG"

#include <QWidget>
#include <QProcess>
#include <QMap>
#include <QMainWindow>

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
        void print(QString s,bool newLine=true);


    protected:
        QWebView *m_web;
        QTextEdit *m_output;
        QProgressBar *m_progressBar;
        QTabWidget *m_tabs;
        QLineEdit *m_webBar;

        QProcess *m_process;

        QMap<QString,QString> m_env;
        QFile *m_logfile;
        QTextStream *m_logstream;

        int m_numberTerminal;
};

#endif // MAINWINDOW_H
