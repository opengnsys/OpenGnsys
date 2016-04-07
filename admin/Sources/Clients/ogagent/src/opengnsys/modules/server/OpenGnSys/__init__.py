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

from opengnsys.workers import ServerWorker

from opengnsys import REST, RESTError
from opengnsys import operations
from opengnsys.log import logger
from opengnsys.scriptThread import ScriptExecutorThread

import subprocess
import threading
import thread
import os
import platform
import time

# Error handler decorator.
def catchBackgroundError(fnc):
    def wrapper(*args, **kwargs):
        this = args[0]
        try:
            fnc(*args, **kwargs)
        except Exception as e:
            this.REST.sendMessage('error?id={}'.format(kwargs.get('requestId', 'error')), {'error': '{}'.format(e)})
    return wrapper

class OpenGnSysWorker(ServerWorker):
    name = 'opengnsys'
    interface = None  # Binded interface for OpenGnsys
    loggedin = False  #
    locked = {}
    
    def onActivation(self):
        self.cmd = None
        # Ensure cfg has required configuration variables or an exception will be thrown
        
        self.REST = REST(self.service.config.get('opengnsys', 'remote'))
        
        # Get network interfaces
        self.interface = list(operations.getNetworkInfo())[0]  # Get first network interface
        
        # Send an initialize message
        #self.REST.sendMessage('initialize/{}/{}'.format(self.interface.mac, self.interface.ip))
        
        # Send an POST message
        self.REST.sendMessage('ogagent/started', {'mac': self.interface.mac, 'ip': self.interface.ip})
        
    def onDeactivation(self):
        #self.REST.sendMessage('deinitialize/{}/{}'.format(self.interface.mac, self.interface.ip))
        logger.debug('onDeactivation')
        self.REST.sendMessage('ogagent/stopped', {'mac': self.interface.mac, 'ip': self.interface.ip})
    
    # Processes message "doit" (sample)    
    #def process_doit(self, path, getParams, postParams):
    #   # Send a sample message to client
    #   logger.debug('Processing doit')
    #   self.sendClientMessage('doit', {'param1': 'test', 'param2': 'test2'})
    #   return 'Processed message for {}, {}, {}'.format(path, getParams, postParams)
    
    def process_script(self, path, getParams, postParams):
        '''
        Processes an script execution (script is encoded in base64)
        '''
        logger.debug('Processing script request')
        script = postParams.get('script')
        if postParams.get('client', 'false') == 'false':
            thr = ScriptExecutorThread(script=script.decode('base64'))
            thr.start()
        else:
            self.sendScriptMessage(script)
            
        return 'ok'
    
    def processClientMessage(self, message, data):
        logger.debug('Got OpenGnsys message from client: {}, data {}'.format(message, data))
    
    def process_client_doit(self, params):
        self.REST.sendMessage('doit_done', params)
    
    def onLogin(self, user):
        logger.debug('Received login for {}'.format(user))
        self.loggedin = True
        self.REST.sendMessage('ogagent/loggedin', {'ip': self.interface.ip, "user": user})
        
    def onLogout(self, user):
        logger.debug('Received logout for {}'.format(user))
        self.loggedin = False
        self.REST.sendMessage('ogagent/loggedout', {'ip': self.interface.ip, "user": user})

    def process_ogclient(self, path, getParams, postParams):
        '''
        This method can be overriden to provide your own message proccessor, or better you can
        implement a method that is called exactly as "process_" + path[0] (module name has been removed from path array) and this default processMessage will invoke it
        * Example:
            Imagine this invocation url (no matter if GET or POST): http://example.com:9999/Sample/mazinger/Z
            The HTTP Server will remove "Sample" from path, parse arguments and invoke this method as this:
            module.processMessage(["mazinger","Z"], getParams, postParams)
            
            This method will process "mazinger", and look for a "self" method that is called "process_mazinger", and invoke it this way:
               return self.process_mazinger(["Z"], getParams, postParams)
               
            In the case path is empty (that is, the path is composed only by the module name, like in "http://example.com/Sample", the "process" method
            will be invoked directly
            
            The methods must return data that can be serialized to json (i.e. Ojects are not serializable to json, basic type are)
        '''
        if len(path) == 0:
            return "ok"
        try:
            operation = getattr(self, 'ogclient_' + path[0])
        except Exception:
            raise Exception('Message processor for "{}" not found'.format(path[0]))
        
        return operation(path[1:], getParams, postParams)
       
    ###### EN PRUEBAS ###### 
    def process_status(self, path, getParams, postParams):
        '''
        Returns client status.
        '''
        res = { 'status': '', 'loggedin': self.loggedin }
        if platform.system() == 'Linux':        # GNU/Linux
            # Check if it's OpenGnsys Client.
            if os.path.exists('/scripts/oginit'):
                # Check if OpenGnsys Client is busy.
                if self.locked:
                    res['status'] = 'BSY'
                else:
                    res['status'] = 'OPG'
            else:
                # Check if there is an active session.
                res['status'] = 'LNX' 
        elif platform.system() == 'Windows':    # Windows
            # Check if there is an active session.
            res['status'] = 'WIN'
        elif platform.system() == 'Darwin':     # Mac OS X  ??
            res['status'] = 'OSX'
        return res
    
    def process_reboot(self, path, getParams, postParams):
        '''
        Launches a system reboot operation.
        '''
        logger.debug('Received reboot operation')
        def rebt():
            operations.reboot()
        threading.Thread(target=rebt).start()
        return {'op': 'launched'}

    def process_poweroff(self, path, getParams, postParams):
        '''
        Launches a system power off operation.
        '''
        logger.debug('Received poweroff operation')
        def pwoff():
            time.sleep(2)
            operations.poweroff()
        threading.Thread(target=pwoff).start()
        return {'op': 'launched'}

    def process_logoff(self, path, getParams, postParams):
        '''
        Closes user session.
        '''
        logger.debug('Received logoff operation')
        self.sendClientMessage('logoff', {})
        return 'Logoff operation was sended to client'

