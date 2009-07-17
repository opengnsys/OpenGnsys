#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#define PROTOCOL "command://"

#include <QWebView>
#include <QObject>
#include <QProcess>
#include <QStringList>


class MainWindow : public QWebView
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
    QProcess *process;
    QStringList output;
    QStringList errors;
};

#endif // MAINWINDOW_H