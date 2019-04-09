# -*- coding: utf-8 -*-
#
# Copyright (c) 201 Virtual Cable S.L.
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

"""
@author: Adolfo Gómez, dkmaster at dkmon dot com
"""

# pylint: disable-msg=E1101,W0703

from __future__ import unicode_literals

import requests
import logging
import json
import warnings

from .log import logger

from .utils import exceptionToMessage

VERIFY_CERT = False  # Do not check server certificate
TIMEOUT = 5  # Connection timout, in seconds


class RESTError(Exception):
    ERRCODE = 0


class ConnectionError(RESTError):
    ERRCODE = -1


# Disable warnings log messages
try:
    import urllib3  # @UnusedImport
except Exception:
    from requests.packages import urllib3  # @Reimport

try:
    urllib3.disable_warnings()  # @UndefinedVariable
    warnings.simplefilter("ignore")
except Exception:
    pass  # In fact, isn't too important, but wil log warns to logging file


class REST(object):
    """
    Simple interface to remote REST apis.
    The constructor expects the "base url" as parameter, that is, the url that will be common on all REST requests
    Remember that this is a helper for "easy of use". You can provide your owns using requests lib for example.
    Examples:
       v = REST('https://example.com/rest/v1/') (Can omit trailing / if desired)
       v.sendMessage('hello?param1=1&param2=2')
         This will generate a GET message to https://example.com/rest/v1/hello?param1=1&param2=2, and return the
         deserialized JSON result or an exception
       v.sendMessage('hello?param1=1&param2=2', {'name': 'mario' })
         This will generate a POST message to https://example.com/rest/v1/hello?param1=1&param2=2, with json encoded
         body {'name': 'mario' }, and also returns
         the deserialized JSON result or raises an exception in case of error 
    """

    def __init__(self, url):
        """
        Initializes the REST helper
        url is the full url of the REST API Base, as for example "https://example.com/rest/v1".
        @param url The url of the REST API Base. The trailing '/' can be included or omitted, as desired.
        """
        self.endpoint = url

        if self.endpoint[-1] != '/':
            self.endpoint += '/'

        # Some OSs ships very old python requests lib implementations, workaround them...
        try:
            self.newerRequestLib = requests.__version__.split('.')[0] >= '1'
        except Exception:
            self.newerRequestLib = False  # I no version, guess this must be an old requests

        # Disable logging requests messages except for errors, ...
        logging.getLogger("requests").setLevel(logging.CRITICAL)
        # Tries to disable all warnings
        try:
            warnings.simplefilter("ignore")  # Disables all warnings
        except Exception:
            pass

    def _getUrl(self, method):
        """
        Internal method
        Composes the URL based on "method"
        @param method: Method to append to base url for composition 
        """
        url = self.endpoint + method

        return url

    def _request(self, url, data=None):
        """
        Launches the request
        @param url: The url to obtain
        @param data: if None, the request will be sent as a GET request. If != None, the request will be sent as a POST,
        with data serialized as JSON in the body.
        """
        try:
            if data is None:
                logger.debug('Requesting using GET (no data provided) {}'.format(url))
                # Old requests version does not support verify, but it do not checks ssl certificate by default
                if self.newerRequestLib:
                    r = requests.get(url, verify=VERIFY_CERT, timeout=TIMEOUT)
                else:
                    r = requests.get(url)
            else:  # POST
                logger.debug('Requesting using POST {}, data: {}'.format(url, data))
                if self.newerRequestLib:
                    r = requests.post(url, data=data, headers={'content-type': 'application/json'},
                                      verify=VERIFY_CERT, timeout=TIMEOUT)
                else:
                    r = requests.post(url, data=data, headers={'content-type': 'application/json'})

            r = json.loads(r.content)  # Using instead of r.json() to make compatible with old requests lib versions
        except requests.exceptions.RequestException as e:
            raise ConnectionError(e)
        except Exception as e:
            raise ConnectionError(exceptionToMessage(e))

        return r

    def sendMessage(self, msg, data=None, processData=True):
        """
        Sends a message to remote REST server
        @param data: if None or omitted, message will be a GET, else it will send a POST
        @param processData: if True, data will be serialized to json before sending, else, data will be sent as "raw" 
        """
        logger.debug('Invoking post message {} with data {}'.format(msg, data))

        if processData and data is not None:
            data = json.dumps(data)

        url = self._getUrl(msg)
        logger.debug('Requesting {}'.format(url))

        return self._request(url, data)
