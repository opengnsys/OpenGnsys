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

from .log import logger
from .config import readConfig
from .utils import exceptionToMessage

from . import ipc
from . import httpserver
from .loader import loadModules

import socket
import time
import json
import six

IPC_PORT = 10398


class CommonService(object):
    isAlive = True
    ipc = None
    httpServer = None
    modules = None
    
    def __init__(self):
        logger.info('----------------------------------------')
        logger.info('Initializing OpenGnsys Agent')
        
        # Read configuration file before proceding & ensures minimal config is there

        self.config = readConfig()

        # Get opengnsys section as dict        
        cfg = dict(self.config.items('opengnsys'))
    
        # Set up log level
        logger.setLevel(cfg.get('log', 'INFO'))

        
        logger.debug('Loaded configuration from opengnsys.cfg:')
        for section in self.config.sections():
            logger.debug('Section {} = {}'.format(section, self.config.items(section)))
            
    
        if logger.logger.isWindows():
            # Logs will also go to windows event log for services
            logger.logger.serviceLogger = True
            
        self.address = (cfg.get('address', '0.0.0.0'), int(cfg.get('port', '10997')))
        self.ipcport = int(cfg.get('ipc_port', IPC_PORT))
        
        self.timeout = int(cfg.get('timeout', '20'))
        
        logger.debug('Socket timeout: {}'.format(self.timeout))
        socket.setdefaulttimeout(self.timeout)
        
        # Now load modules
        self.modules = loadModules(self)
        logger.debug('Modules: {}'.format(list(v.name for v in self.modules)))
        
    def stop(self):
        '''
        Requests service termination
        '''
        self.isAlive = False
        
    # ********************************
    # * Internal messages processors *
    # ********************************
    def notifyLogin(self, username):
        for v in self.modules:
            try:
                logger.debug('Notifying login of user {} to module {}'.format(username, v.name))
                v.onLogin(username)
            except Exception as e:
                logger.error('Got exception {} processing login message on {}'.format(e, v.name))
    
    def notifyLogout(self, username):
        for v in self.modules:
            try:
                logger.debug('Notifying logout of user {} to module {}'.format(username, v.name))
                v.onLogout(username)
            except Exception as e:
                logger.error('Got exception {} processing logout message on {}'.format(e, v.name))
                
    def notifyMessage(self, data):
        module, message, data = data.split('\0')
        for v in self.modules:
            if v.name == module:  # Case Sensitive!!!!
                try:
                    logger.debug('Notifying message {} to module {} with json data {}'.format(message, v.name, data))
                    v.processClientMessage(message, json.loads(data))
                    return
                except Exception as e:
                    logger.error('Got exception {} processing generic message on {}'.format(e, v.name))

        logger.error('Module {} not found, messsage {} not sent'.format(module, message))
                     

    def clientMessageProcessor(self, msg, data):
        '''
        Callback, invoked from IPC, on its own thread (not the main thread).
        This thread will "block" communication with agent untill finished, but this should be no problem
        '''
        logger.debug('Got message {}'.format(msg))
        
        if msg == ipc.REQ_LOGIN:
            self.notifyLogin(data)
        elif msg == ipc.REQ_LOGOUT:
            self.notifyLogout(data)
        elif msg == ipc.REQ_MESSAGE:
            self.notifyMessage(data)

    def initialize(self):
        # ******************************************
        # * Initialize listeners, modules, etc...
        # ******************************************
        
        if six.PY3 is False:
            import threading
            threading._DummyThread._Thread__stop = lambda x: 42
        
        logger.debug('Starting IPC listener at {}'.format(IPC_PORT))
        self.ipc = ipc.ServerIPC(self.ipcport, clientMessageProcessor=self.clientMessageProcessor)
        self.ipc.start()

        # And http threaded server
        self.httpServer = httpserver.HTTPServerThread(self.address, self)
        self.httpServer.start()
        
        # And lastly invoke modules activation
        validMods = []
        for mod in self.modules:
            try:
                logger.debug('Activating module {}'.format(mod.name))
                mod.activate()
                validMods.append(mod)
            except Exception as e:
                logger.exception()
                logger.error("Activation of {} failed: {}".format(mod.name, exceptionToMessage(e)))
        
        self.modules[:] = validMods  # copy instead of assignment
        
        logger.debug('Modules after activation: {}'.format(list(v.name for v in self.modules)))

    def terminate(self):
        # First invoke deactivate on modules
        for mod in reversed(self.modules):
            try:
                logger.debug('Deactivating module {}'.format(mod.name))
                mod.deactivate()
            except Exception as e:
                logger.exception()
                logger.error("Deactivation of {} failed: {}".format(mod.name, exceptionToMessage(e)))
        
        # Remove IPC threads
        if self.ipc is not None:
            try:
                self.ipc.stop()
            except Exception:
                logger.error('Couln\'t stop ipc server')
                
        if self.httpServer is not None:
            try:
                self.httpServer.stop()
            except Exception:
                logger.error('Couln\'t stop RESTApi server')

        self.notifyStop()

    # ****************************************
    # Methods that CAN BE overridden by agents
    # ****************************************
    def doWait(self, miliseconds):
        '''
        Invoked to wait a bit
        CAN be OVERRIDDEN
        '''
        time.sleep(float(miliseconds) / 1000)

    def notifyStop(self):
        '''
        Overridden to log stop
        '''
        logger.info('Service is being stopped')
        
    # ***************************************************
    # * Helpers, convenient methods to facilitate comms *
    # ***************************************************
    def sendClientMessage(self, toModule, message, data):
        '''
        Sends a message to the clients using IPC
        The data is converted to json, so ensure that it is serializable.
        All IPC is asynchronous, so if you expect a response, this will be sent by client using another message
        
        @param toModule: Module that will receive this message
        @param message: Message to send
        @param data: data to send 
        '''
        self.ipc.sendMessageMessage('\0'.join((toModule, message, json.dumps(data))))
        
    def sendScriptMessage(self, script):
        '''
        Sends an script to be executed by client
        '''
        self.ipc.sendScriptMessage(script)
        
    def sendLogoffMessage(self):
        '''
        Sends a logoff message to client
        '''
        self.ipc.sendLoggofMessage()
        
    def sendPopupMessage(self, title, message):
        '''
        Sends a poup box to be displayed by client
        '''
        self.ipc.sendPopupMessage(title, message)
