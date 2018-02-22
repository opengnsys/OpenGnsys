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

class ClientWorker(object):
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
        
    def processMessage(self, message, params):
        '''
        This method can be overriden to provide your own message proccessor, or better you can
        implement a method that is called "process_" + message and this default processMessage will invoke it
        * Example:
            We got a message from OGAgent "Mazinger", with json params
            module.processMessage("mazinger", jsonParams)
            
            This method will process "mazinguer", and look for a "self" method that is called "process_mazinger", and invoke it this way:
               return self.process_mazinger(jsonParams)
               
            The methods must return data that can be serialized to json (i.e. Ojects are not serializable to json, basic type are)
        '''
        try:
            operation = getattr(self, 'process_' + message)
        except Exception:
            raise Exception('Message processor for "{}" not found'.format(message))
        
        return operation(params)
        
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

    # *************************************
    # * Helper, convenient helper methods *
    # *************************************
    def sendServerMessage(self, message, data):
        '''
        Sends a message to connected ipc clients
        By convenience, it uses the "current" moduel name as destination module name also.
        If you need to send a message to a different module, you can use self.service.sendClientMessage(module, message, data) instead
        og this helmer
        '''
        self.service.ipc.sendMessage(self.name, message, data)
    