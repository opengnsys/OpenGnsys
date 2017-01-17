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
@author: Ramón M. Gómez, ramongomez at us dot es
'''
from __future__ import unicode_literals

from opengnsys.workers import ClientWorker

from opengnsys import operations
from opengnsys.log import logger
from opengnsys.scriptThread import ScriptExecutorThread

class OpenGnSysWorker(ClientWorker):
    name = 'opengnsys'

    def onActivation(self):
        logger.debug('Activate invoked')
        
    def onDeactivation(self):
        logger.debug('Deactivate invoked')
    
    # Processes script execution
    def process_script(self, jsonParams):
        logger.debug('Processed message: script({})'.format(jsonParams))
        thr = ScriptExecutorThread(jsonParams['code'])
        thr.start()
        #self.sendServerMessage('script', {'op', 'launched'})

    def process_logoff(self, jsonParams):
        logger.debug('Processed message: logoff({})'.format(jsonParams))
        operations.logoff()

    def process_popup(self, jsonParams):
        logger.debug('Processed message: popup({})'.format(jsonParams))
        ret = operations.showPopup(jsonParams['title'], jsonParams['message'])
        #self.sendServerMessage('popup', {'op', ret})

