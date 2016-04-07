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
# pylint: disable=unused-wildcard-import,wildcard-import
from __future__ import unicode_literals

class ServerWorker(object):
    '''
    A ServerWorker is a server module that "works" for service
    Most method are invoked inside their own thread, except onActivation & onDeactivation. 
    This two methods are invoked inside main service thread, take that into account when creating them
    
    * You must provide a module name (override name on your class), so we can identify the module by a "valid" name.
      A valid name is like a valid python variable (do not use spaces, etc...)
    * The name of the module is used as REST message destination id:
      https://sampleserver:8888/[name]/....
      Remember that module names and REST path are case sensitive!!!
      
    '''
    name = None
    service = None
    locked = False
    
    def __init__(self, service):
        self.service = service
        
    def activate(self):
        '''
        Convenient method to wrap onActivation, so we can include easyly custom common logic for activation in a future
        '''
        self.onActivation()
        
    def deactivate(self):
        '''
        Convenient method to wrap onActivation, so we can include easyly custom common logic for deactivation in a future
        '''
        self.onDeactivation()
        
    def process(self, getParams, postParams):
        '''
        This method is invoked on a message received with an empty path (that means a message with only the module name, like in "http://example.com/Sample"
        Override it if you expect messages with that pattern
        
        Overriden method must return data that can be serialized to json (i.e. Ojects are not serializable to json, basic type are)
        '''
        raise NotImplementedError('Generic message processor is not supported')
        
    def processServerMessage(self, path, getParams, postParams):
        '''
        This method can be overriden to provide your own message proccessor, or better you can
        implement a method that is called exactly as "process_" + path[0] (module name has been removed from path array) and this default processMessage will invoke it
        * Example:
            Imagine this invocation url (no matter if GET or POST): http://example.com:9999/Sample/mazinger/Z
            The HTTP Server will remove "Sample" from path, parse arguments and invoke this method as this:
            module.processMessage(["mazinger","Z"], getParams, postParams)
            
            This method will process "mazinguer", and look for a "self" method that is called "process_mazinger", and invoke it this way:
               return self.process_mazinger(["Z"], getParams, postParams)
               
            In the case path is empty (that is, the path is composed only by the module name, like in "http://example.com/Sample", the "process" method
            will be invoked directly
            
            The methods must return data that can be serialized to json (i.e. Ojects are not serializable to json, basic type are)
        '''
        if self.locked is True:
            raise Exception('system is busy')
        
        if len(path) == 0:
            return self.process(getParams, postParams)
        try:
            operation = getattr(self, 'process_' + path[0])
        except Exception:
            raise Exception('Message processor for "{}" not found'.format(path[0]))
        
        return operation(path[1:], getParams, postParams)
        
        
    def processClientMessage(self, message, data):
        '''
        Invoked by Service when a client message is received (A message from user space Agent)
        
        This method can be overriden to provide your own message proccessor, or better you can
        implement a method that is called exactly "process_client_" + message (module name has been removed from path) and this default processMessage will invoke it
        * Example:
            We got a message from OGAgent "Mazinger", with json params
            module.processClientMessage("mazinger", jsonParams)
            
            This method will process "mazinguer", and look for a "self" method that is called "process_client_mazinger", and invoke it this way:
               self.process_client_mazinger(jsonParams)
               
            The methods returns nothing (client communications are done asynchronously)
        '''
        try:
            operation = getattr(self, 'process_client_' + message)
        except Exception:
            raise Exception('Message processor for "{}" not found'.format(message))
        
        operation(data)
        
        # raise NotImplementedError('Got a client message but no proccessor is implemented')
        
    
    def onActivation(self):
        '''
        Invoked by Service for activation.
        This MUST be overridden by modules!
        This method is invoked inside main thread, so if it "hangs", complete service will hang
        This should be no problem, but be advised about this
        '''
        pass
    
    def onDeactivation(self):
        '''
        Invoked by Service before unloading service
        This MUST be overridden by modules!
        This method is invoked inside main thread, so if it "hangs", complete service will hang
        This should be no problem, but be advised about this
        '''
        pass
    
    
    def onLogin(self, user):
        '''
        Invoked by Service when an user login is detected
        This CAN be overridden by modules
        This method is invoked whenever the client (user space agent) notifies the server (Service) that a user has logged in.
        This method is run on its own thread
        '''
        pass
    
    def onLogout(self, user):
        '''
        Invoked by Service when an user login is detected
        This CAN be overridden by modules
        This method is invoked whenever the client (user space agent) notifies the server (Service) that a user has logged in.
        This method is run on its own thread
        '''
        pass
    
    # *************************************
    # * Helper, convenient helper methods *
    # *************************************
    def sendClientMessage(self, message, data):
        '''
        Sends a message to connected ipc clients
        By convenience, it uses the "current" moduel name as destination module name also.
        If you need to send a message to a different module, you can use self.service.sendClientMessage(module, message, data) instead
        og this helmer
        '''
        self.service.sendClientMessage(self.name, message, data)
        
    def sendScriptMessage(self, script):
        self.service.sendScriptMessage(script)
    
    def sendLogoffMessage(self):
        self.service.sendLogoffMessage()
