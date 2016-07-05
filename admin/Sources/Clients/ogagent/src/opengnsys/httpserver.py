# -*- coding: utf-8 -*-
#
# Copyright (c) 2015 Virtual Cable S.L.
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
from __future__ import unicode_literals, print_function

# Pydev can't parse "six.moves.xxxx" because it is loaded lazy
import six
from six.moves.socketserver import ThreadingMixIn  # @UnresolvedImport
from six.moves.BaseHTTPServer import BaseHTTPRequestHandler  # @UnresolvedImport
from six.moves.BaseHTTPServer import HTTPServer  # @UnresolvedImport
from six.moves.urllib.parse import unquote  # @UnresolvedImport

import json
import threading
import ssl

from .utils import exceptionToMessage
from .certs import createSelfSignedCert
from .log import logger

class HTTPServerHandler(BaseHTTPRequestHandler):
    service = None
    protocol_version = 'HTTP/1.0'
    server_version = 'OpenGnsys Agent Server'
    sys_version = ''
    
    def sendJsonError(self, code, message):
        self.send_response(code)
        self.send_header('Content-type', 'application/json')
        self.end_headers()
        self.wfile.write(json.dumps({'error': message}))
        return

    def sendJsonResponse(self, data):
        self.send_response(200)
        data = json.dumps(data)
        self.send_header('Content-type', 'application/json')
        self.send_header('Content-Length', len(data))
        self.end_headers()
        # Send the html message
        self.wfile.write(data)
        
    
    # parseURL
    def parseUrl(self):
        # Very simple path & params splitter
        path = self.path.split('?')[0][1:].split('/')
        
        try:
            params = dict((v[0], unquote(v[1])) for v in (v.split('=') for v in self.path.split('?')[1].split('&')))
        except Exception:
            params = {}

        for v in self.service.modules:
            if v.name == path[0]:  # Case Sensitive!!!!
                return (v, path[1:], params)
            
        return (None, path, params)
    
    def notifyMessage(self, module, path, getParams, postParams):
        '''
        Locates witch module will process the message based on path (first folder on url path)
        '''
        try:
            data = module.processServerMessage(path, getParams, postParams, self)
            self.sendJsonResponse(data)
        except Exception as e:
            logger.exception()
            self.sendJsonError(500, exceptionToMessage(e))
            
    def do_GET(self):
        module, path, params = self.parseUrl()
        
        self.notifyMessage(module, path, params, None)
        
    def do_POST(self):
        module, path, getParams = self.parseUrl()

        # Tries to get json content
        try:
            length = int(self.headers.getheader('content-length'))
            content = self.rfile.read(length)
            logger.debug('length: {}, content >>{}<<'.format(length, content))
            postParams = json.loads(content)
        except Exception as e:
            self.sendJsonError(500, exceptionToMessage(e))
            
        self.notifyMessage(module, path, getParams, postParams)
            

    def log_error(self, fmt, *args):
        logger.error('HTTP ' + fmt % args)
        
    def log_message(self, fmt, *args):
        logger.info('HTTP ' + fmt % args)
        

class HTTPThreadingServer(ThreadingMixIn, HTTPServer):
    pass

class HTTPServerThread(threading.Thread):
    def __init__(self, address, service):
        super(self.__class__, self).__init__()

        HTTPServerHandler.service = service  # Keep tracking of service so we can intercact with it

        self.certFile = createSelfSignedCert()
        self.server = HTTPThreadingServer(address, HTTPServerHandler)
        self.server.socket = ssl.wrap_socket(self.server.socket, certfile=self.certFile, server_side=True)
        
        logger.debug('Initialized HTTPS Server thread on {}'.format(address))

    def getServerUrl(self):
        return 'https://{}:{}/'.format(self.server.server_address[0], self.server.server_address[1])

    def stop(self):
        self.server.shutdown()

    def run(self):
        self.server.serve_forever()

    