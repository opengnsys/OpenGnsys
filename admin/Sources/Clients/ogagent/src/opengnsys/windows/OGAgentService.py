# -*- coding: utf-8 -*-
#
# Copyright (c) 2014 Virtual Cable S.L.
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without modification,
# are permitted provided that the following conditions are met:
#
#    * Redistributions of source code must retain the above copyright notice,
#      this list of conditions and the following disclaimer.
#    * Redistributions in binary form must reproduce the above copyright notice,
#      this list of conditions and the following disclaimer in the documentation
#      and/or other materials provided with the distribution.
#    * Neither the name of Virtual Cable S.L. nor the names of its contributors
#      may be used to endorse or promote products derived from this software
#      without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
# AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
# IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
# FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
# DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
# SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
# CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
# OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

'''
@author: Adolfo Gómez, dkmaster at dkmon dot com
'''
from __future__ import unicode_literals
# pylint: disable=unused-wildcard-import, wildcard-import

import win32serviceutil  # @UnresolvedImport, pylint: disable=import-error
import win32service  # @UnresolvedImport, pylint: disable=import-error
import win32security  # @UnresolvedImport, pylint: disable=import-error
import win32net  # @UnresolvedImport, pylint: disable=import-error
import win32event  # @UnresolvedImport, pylint: disable=import-error
import win32com.client  # @UnresolvedImport,  @UnusedImport, pylint: disable=import-error
import pythoncom  # @UnresolvedImport, pylint: disable=import-error
import servicemanager  # @UnresolvedImport, pylint: disable=import-error
import os

from opengnsys import operations
from opengnsys.service import CommonService

from opengnsys.log import logger

class OGAgentSvc(win32serviceutil.ServiceFramework, CommonService):
    '''
    This class represents a Windows Service for managing actor interactions
    with UDS Broker and Machine
    '''
    _svc_name_ = "OGAgent"
    _svc_display_name_ = "OpenGnSys Agent Service"
    _svc_description_ = "OpenGnSys Agent for machines"
    # 'System Event Notification' is the SENS service
    _svc_deps_ = ['EventLog']

    def __init__(self, args):
        win32serviceutil.ServiceFramework.__init__(self, args)
        CommonService.__init__(self)
        self.hWaitStop = win32event.CreateEvent(None, 1, 0, None)
        self._user = None

    def SvcStop(self):
        self.ReportServiceStatus(win32service.SERVICE_STOP_PENDING)
        self.isAlive = False
        win32event.SetEvent(self.hWaitStop)

    SvcShutdown = SvcStop

    def notifyStop(self):
        servicemanager.LogMsg(servicemanager.EVENTLOG_INFORMATION_TYPE,
                              servicemanager.PYS_SERVICE_STOPPED,
                              (self._svc_name_, ''))

    def doWait(self, miliseconds):
        win32event.WaitForSingleObject(self.hWaitStop, miliseconds)

    def SvcDoRun(self):
        '''
        Main service loop
        '''
        try:
            logger.debug('running SvcDoRun')
            servicemanager.LogMsg(servicemanager.EVENTLOG_INFORMATION_TYPE,
                                  servicemanager.PYS_SERVICE_STARTED,
                                  (self._svc_name_, ''))

            # call the CoInitialize to allow the registration to run in an other
            # thread
            logger.debug('Initializing com...')
            pythoncom.CoInitialize()

            # Initialize remaining service data
            self.initialize()
        except Exception:  # Any init exception wil be caught, service must be then restarted
            logger.exception()
            logger.debug('Exiting service with failure status')
            os._exit(-1)  # pylint: disable=protected-access

        # *********************
        # * Main Service loop *
        # *********************
        try:
            while self.isAlive:
                # Pumps & processes any waiting messages
                pythoncom.PumpWaitingMessages()
                win32event.WaitForSingleObject(self.hWaitStop, 1000)
        except Exception as e:
            logger.error('Caught exception on main loop: {}'.format(e))

        logger.debug('Exited main loop, deregistering SENS')

        self.terminate()  # Ends IPC servers

        self.notifyStop()


if __name__ == '__main__':
    
    win32serviceutil.HandleCommandLine(OGAgentSvc)
