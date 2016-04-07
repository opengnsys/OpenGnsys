# -*- coding: utf-8 -*-

# Form implementation generated from reading ui file 'message-dialog.ui'
#
# Created by: PyQt4 UI code generator 4.11.4
#
# WARNING! All changes made in this file will be lost!

from PyQt4 import QtCore, QtGui

try:
    _fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
    def _fromUtf8(s):
        return s

try:
    _encoding = QtGui.QApplication.UnicodeUTF8
    def _translate(context, text, disambig):
        return QtGui.QApplication.translate(context, text, disambig, _encoding)
except AttributeError:
    def _translate(context, text, disambig):
        return QtGui.QApplication.translate(context, text, disambig)

class Ui_OGAMessageDialog(object):
    def setupUi(self, OGAMessageDialog):
        OGAMessageDialog.setObjectName(_fromUtf8("OGAMessageDialog"))
        OGAMessageDialog.resize(339, 188)
        sizePolicy = QtGui.QSizePolicy(QtGui.QSizePolicy.Expanding, QtGui.QSizePolicy.Expanding)
        sizePolicy.setHorizontalStretch(0)
        sizePolicy.setVerticalStretch(0)
        sizePolicy.setHeightForWidth(OGAMessageDialog.sizePolicy().hasHeightForWidth())
        OGAMessageDialog.setSizePolicy(sizePolicy)
        font = QtGui.QFont()
        font.setFamily(_fromUtf8("Verdana"))
        font.setPointSize(10)
        OGAMessageDialog.setFont(font)
        self.verticalLayoutWidget = QtGui.QWidget(OGAMessageDialog)
        self.verticalLayoutWidget.setGeometry(QtCore.QRect(10, 10, 321, 171))
        self.verticalLayoutWidget.setObjectName(_fromUtf8("verticalLayoutWidget"))
        self.verticalLayout = QtGui.QVBoxLayout(self.verticalLayoutWidget)
        self.verticalLayout.setObjectName(_fromUtf8("verticalLayout"))
        self.message = QtGui.QTextBrowser(self.verticalLayoutWidget)
        self.message.setObjectName(_fromUtf8("message"))
        self.verticalLayout.addWidget(self.message)
        spacerItem = QtGui.QSpacerItem(20, 15, QtGui.QSizePolicy.Minimum, QtGui.QSizePolicy.Fixed)
        self.verticalLayout.addItem(spacerItem)
        self.buttonBox = QtGui.QDialogButtonBox(self.verticalLayoutWidget)
        self.buttonBox.setStandardButtons(QtGui.QDialogButtonBox.Ok)
        self.buttonBox.setObjectName(_fromUtf8("buttonBox"))
        self.verticalLayout.addWidget(self.buttonBox)

        self.retranslateUi(OGAMessageDialog)
        QtCore.QObject.connect(self.buttonBox, QtCore.SIGNAL(_fromUtf8("clicked(QAbstractButton*)")), OGAMessageDialog.closeDialog)
        QtCore.QMetaObject.connectSlotsByName(OGAMessageDialog)

    def retranslateUi(self, OGAMessageDialog):
        OGAMessageDialog.setWindowTitle(_translate("OGAMessageDialog", "UDS Actor", None))


if __name__ == "__main__":
    import sys
    app = QtGui.QApplication(sys.argv)
    OGAMessageDialog = QtGui.QDialog()
    ui = Ui_OGAMessageDialog()
    ui.setupUi(OGAMessageDialog)
    OGAMessageDialog.show()
    sys.exit(app.exec_())

