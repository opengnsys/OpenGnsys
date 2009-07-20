#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#define PROTOCOL "command://"

#include <QWidget>
#include <QProcess>

class QWebView;
class QTextEdit;
class QVBoxLayout;
class QProcess;
class QStringList;
class QString;
class QUrl;

class MainWindow : public QWidget
{
    Q_OBJECT

    public:
        MainWindow(QWidget *parent = 0);
        ~MainWindow();

    public slots:
        // Funcion que maneja los links
        void slotLinkHandle(const QUrl& url);

        // Funciones que manejan cada vez que el proceso hace algo
        void slotStarted();
        void slotFinished(int code,QProcess::ExitStatus status);
        void slotError(QProcess::ProcessError error);
        void slotOutput();
        void slotErrorOutput();

    protected:
        QWebView *web;
        QTextEdit *text;
        QVBoxLayout *layout;

        QProcess *process;
        QStringList output;
        QStringList errors;

        QString dirlog;
        QString server;
};

#endif // MAINWINDOW_H
