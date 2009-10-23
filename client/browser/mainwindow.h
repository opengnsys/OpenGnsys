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
class QDockWidget;
class QTermWidget;

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

    //Functions
    protected:
        int readEnvironmentValues();


    protected:
        QWebView *web;
        QTextEdit *text;
        QDockWidget *dock;
        QTermWidget *console;

        QProcess *process;
        QStringList output;
        QStringList errors;

        QMap<QString,QString> env;
        QFile *logfile;
        QTextStream *logstream;
};

#endif // MAINWINDOW_H
