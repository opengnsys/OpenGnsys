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

    //Functions
    protected:
        int readEnvironmentValues();


    protected:
        QWebView *web;
        QTextEdit *output;
        QProgressBar *progressBar;
        QTabWidget *tabs;

        QProcess *process;

        QMap<QString,QString> env;
        QFile *logfile;
        QTextStream *logstream;

        int numberTerminal;
};

#endif // MAINWINDOW_H
