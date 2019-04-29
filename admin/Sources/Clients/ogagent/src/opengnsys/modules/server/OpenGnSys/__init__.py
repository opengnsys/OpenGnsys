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
"""
@author: Ramón M. Gómez, ramongomez at us dot es
"""
from __future__ import unicode_literals

import os
import random
import shutil
import string
import subprocess
import threading
import time
import urllib

from opengnsys import REST
from opengnsys import operations
from opengnsys.log import logger
from opengnsys.scriptThread import ScriptExecutorThread
from opengnsys.workers import ServerWorker
from six.moves.urllib import parse


# Error handler decorator.
def catch_background_error(fnc):
    def wrapper(*args, **kwargs):
        this = args[0]
        try:
            fnc(*args, **kwargs)
        except Exception as e:
            this.REST.sendMessage('error?id={}'.format(kwargs.get('requestId', 'error')), {'error': '{}'.format(e)})

    return wrapper


def check_locked_partition(sync=False):
    """
    Decorator to check if a partition is locked
    """

    def outer(fnc):
        def wrapper(*args, **kwargs):
            part_id = 'None'
            try:
                this, path, get_params, post_params, server = args  # @UnusedVariable
                part_id = post_params['disk'] + post_params['part']
                if this.locked.get(part_id, False):
                    this.locked[part_id] = True
                    fnc(*args, **kwargs)
                else:
                    return 'partition locked'
            except Exception as e:
                this.locked[part_id] = False
                return 'error {}'.format(e)
            finally:
                if sync is True:
                    this.locked[part_id] = False
            logger.debug('Lock status: {} {}'.format(fnc, this.locked))

        return wrapper

    return outer


class OpenGnSysWorker(ServerWorker):
    name = 'opengnsys'
    interface = None  # Bound interface for OpenGnsys
    REST = None  # REST object
    loggedin = False  # User session flag
    locked = {}  # Locked partitions
    commands = []  # Running commands
    random = None  # Random string for secure connections
    length = 32  # Random string length
    access_token = refresh_token = None  # Server authorization tokens
    grant_type = 'http://opengnsys.es/grants/og_client'

    def checkSecret(self, server):
        """
        Checks for received secret key and raise exception if it isn't valid.
        """
        try:
            if self.random != server.headers['Authorization']:
                raise Exception('Unauthorized operation')
        except Exception as e:
            logger.error(e)
            raise Exception(e)

    def onActivation(self):
        """
        Sends OGAgent activation notification to OpenGnsys server
        """
        # Ensure cfg has required configuration variables or an exception will be thrown
        url = self.service.config.get('opengnsys', 'remote')
        server_client = self.service.config.get('opengnsys', 'client')
        server_secret = self.service.config.get('opengnsys', 'secret')
        if operations.os_type == 'ogLive' and 'oglive' in os.environ:
            # Replacing server IP if its running on ogLive client
            logger.debug('Activating on ogLive client, new server is {}'.format(os.environ['oglive']))
            url = parse.urlsplit(url)._replace(netloc=os.environ['oglive']).geturl()
        if not url.endswith(os.path.sep):
            url += os.path.sep
        self.REST = REST(url)
        # Get network interfaces until they are active or timeout (5 minutes)
        for t in range(0, 300):
            try:
                self.interface = list(operations.getNetworkInfo())[0]  # Get first network interface
            except Exception as e:
                # Wait 1 sec. and retry
                time.sleep(1)
            finally:
                # Exit loop if interface is active
                if self.interface:
                    if t > 0:
                        logger.debug("Fetch connection data after {} tries".format(t))
                    break
        # Raise error after timeout
        if not self.interface:
            raise e
        # Delete marking files
        for f in ['ogboot.me', 'ogboot.firstboot', 'ogboot.secondboot']:
            try:
                os.remove(os.sep + f)
            except OSError:
                pass
        # Copy file "HostsFile.FirstOctetOfIPAddress" to "HostsFile", if it exists
        # (used in "exam mode" from the University of Seville)
        # hostsFile = os.path.join(operations.get_etc_path(), 'hosts')
        # newHostsFile = hostsFile + '.' + self.interface.ip.split('.')[0]
        # if os.path.isfile(newHostsFile):
        #    shutil.copyfile(newHostsFile, hostsFile)
        # Generate random secret to send on activation
        self.random = ''.join(random.choice(string.ascii_lowercase + string.digits) for _ in range(self.length))
        # Compose login route
        login_route = 'oauth/v2/token?client_id=' + server_client + '&client_secret=' + server_secret + \
                      '&grant_type=' + self.grant_type + '&ip=' + self.interface.ip + '&mac=' + self.interface.mac + \
                      '&token=' + self.random
        # Send initialization login message
        response = None
        try:
            try:
                # New web compatibility.
                response = self.REST.sendMessage(login_route)
            except:
                # Trying to initialize on alternative server, if defined
                # (used in "exam mode" from the University of Seville)
                self.REST = REST(self.service.config.get('opengnsys', 'altremote'))
                response = self.REST.sendMessage(login_route)
        except:
            logger.error('Initialization error')
        finally:
            if response['access_token'] is not None:
                self.access_token = response['access_token']
                self.refresh_token = response['refresh_token']
                # Once authenticated with the server, change the API URL for private request
                self.REST = REST(url + 'api/private')
                # Set authorization tokens in the REST object, so in each request this token will be used
                self.REST.set_authorization_headers(self.access_token, self.refresh_token)
                # Create HTML file (TEMPORARY)
                message = """
<html>
<head></head>
<body>
<h1>Initializing...</h1>
</body>
</html>
"""
                f = open('/tmp/init.html', 'w')
                f.write(message)
                f.close()
                # Launch browser
                subprocess.Popen(['browser', '-qws', '/tmp/init.html'])
                self.REST.sendMessage('clients/statuses', {'mac': self.interface.mac, 'ip': self.interface.ip,
                                                           'status': 'initializing'})

                def cfg():
                    """
                    Subprocess to send configuration and status
                    """
                    config = operations.get_configuration()
                    self.REST.sendMessage('clients/configs', {'mac': self.interface.mac, 'ip': self.interface.ip,
                                                              'config': config})
                    self.REST.sendMessage('clients/statuses', {'mac': self.interface.mac, 'ip': self.interface.ip,
                                                               'status': 'oglive'})

                threading.Thread(target=cfg()).start()
            else:
                logger.error("Initialization error: Can't obtain access token")

    def onDeactivation(self):
        """
        Sends OGAgent stopping notification to OpenGnsys server
        """
        logger.debug('onDeactivation')
        # self.REST.sendMessage('ogagent/stopped', {'mac': self.interface.mac, 'ip': self.interface.ip,
        #                                          'ostype': operations.os_type, 'osversion': operations.os_version})
        self.REST.sendMessage('clients/statuses', {'mac': self.interface.mac, 'ip': self.interface.ip,
                                                   'ostype': operations.os_type, 'osversion': operations.os_version,
                                                   'status': 'off'})

    def processClientMessage(self, message, data):
        logger.debug('Got OpenGnsys message from client: {}, data {}'.format(message, data))

    def onLogin(self, data):
        """
        Sends session login notification to OpenGnsys server
        """
        user, sep, language = data.partition(',')
        logger.debug('Received login for {} with language {}'.format(user, language))
        self.loggedin = True
        self.REST.sendMessage('ogagent/loggedin', {'ip': self.interface.ip, 'user': user, 'language': language,
                                                   'ostype': operations.os_type, 'osversion': operations.os_version})

    def onLogout(self, user):
        """
        Sends session logout notification to OpenGnsys server
        """
        logger.debug('Received logout for {}'.format(user))
        self.loggedin = False
        self.REST.sendMessage('ogagent/loggedout', {'ip': self.interface.ip, 'user': user})

    def process_ogclient(self, path, getParams, postParams, server):
        """
        This method can be overridden to provide your own message processor, or better you can
        implement a method that is called exactly as "process_" + path[0] (module name has been removed from path
        array) and this default processMessage will invoke it
        * Example:
            Imagine this invocation url (no matter if GET or POST): http://example.com:9999/Sample/mazinger/Z
            The HTTP Server will remove "Sample" from path, parse arguments and invoke this method as this:
            module.processMessage(["mazinger","Z"], getParams, postParams)

            This method will process "mazinger", and look for a "self" method that is called "process_mazinger",
            and invoke it this way:
               return self.process_mazinger(["Z"], getParams, postParams)

            In the case path is empty (that is, the path is composed only by the module name, like in
            "http://example.com/Sample", the "process" method will be invoked directly

            The methods must return data that can be serialized to json (i.e. Objects are not serializable to json,
            basic type are)
        """
        if not path:
            return "ok"
        try:
            operation = getattr(self, 'ogclient_' + path[0])
        except Exception:
            raise Exception('Message processor for "{}" not found'.format(path[0]))
        return operation(path[1:], getParams, postParams)

    def process_status(self, path, getParams, postParams, server):
        """
        Returns client status (OS type and login status).
        """
        res = {'loggedin': self.loggedin}
        try:
            res['status'] = operations.os_type.lower()
        except KeyError:
            res['status'] = ''
        # Check if OpenGnsys Client is busy
        if res['status'] == 'oglive' and self.locked:
            res['status'] = 'busy'
        return res

    def process_reboot(self, path, getParams, postParams, server):
        """
        Launches a system reboot operation.
        """
        logger.debug('Received reboot operation')
        self.checkSecret(server)

        # Rebooting thread.
        def rebt():
            operations.reboot()

        threading.Thread(target=rebt).start()
        return {'op': 'launched'}

    def process_poweroff(self, path, getParams, postParams, server):
        """
        Launches a system power off operation.
        """
        logger.debug('Received poweroff operation')
        self.checkSecret(server)

        # Powering off thread.
        def pwoff():
            time.sleep(2)
            operations.poweroff()

        threading.Thread(target=pwoff).start()
        return {'op': 'launched'}

    def process_script(self, path, getParams, postParams, server):
        """
        Processes an script execution (script should be encoded in base64)
        """
        logger.debug('Processing script request')
        self.checkSecret(server)
        # Decoding script.
        script = urllib.unquote(postParams.get('script').decode('base64')).decode('utf8')
        script = 'import subprocess; subprocess.check_output("""{}""",shell=True)'.format(script)
        # Executing script.
        if postParams.get('client', 'false') == 'false':
            thr = ScriptExecutorThread(script)
            thr.start()
        else:
            self.sendClientMessage('script', {'code': script})
        return {'op': 'launched'}

    def process_logoff(self, path, getParams, postParams, server):
        """
        Closes user session.
        """
        logger.debug('Received logoff operation')
        self.checkSecret(server)
        # Sending log off message to OGAgent client.
        self.sendClientMessage('logoff', {})
        return {'op': 'sent to client'}

    def process_popup(self, path, getParams, postParams, server):
        """
        Shows a message popup on the user's session.
        """
        logger.debug('Received message operation')
        self.checkSecret(server)
        # Sending popup message to OGAgent client.
        self.sendClientMessage('popup', postParams)
        return {'op': 'launched'}

    def process_client_popup(self, params):
        self.REST.sendMessage('popup_done', params)

    def process_config(self, path, get_params, post_params, server):
        """
        Returns client configuration
        :param path:
        :param get_params:
        :param post_params:
        :param server:
        :return: object
        """
        serialno = ''  # Serial number
        storage = []  # Storage configuration
        warnings = 0  # Number of warnings
        logger.debug('Received getconfig operation')
        # self.checkSecret(server)
        # Processing data
        for row in operations.get_configuration().split(';'):
            cols = row.split(':')
            if len(cols) == 1:
                if cols[0] != '':
                    # Serial number
                    serialno = cols[0]
                else:
                    # Skip blank rows
                    pass
            elif len(cols) == 7:
                disk, npart, tpart, fs, opsys, size, usage = cols
                try:
                    if int(npart) == 0:
                        # Disk information
                        storage.append({'disk': int(disk), 'parttable': int(tpart), 'size': int(size)})
                    else:
                        # Partition information
                        storage.append({'disk': int(disk), 'partition': int(npart), 'parttype': tpart,
                                        'filesystem': fs, 'operatingsystem': opsys, 'size': int(size),
                                        'usage': int(usage)})
                except ValueError:
                    logger.warn('Configuration parameter error: {}'.format(cols))
                    warnings += 1
            else:
                # Logging warnings
                logger.warn('Configuration data error: {}'.format(cols))
                warnings += 1
        # Returning configuration data and count of warnings
        return {'serialno': serialno, 'storage': storage, 'warnings': warnings}

    def task_command(self, code, route, op_id):
        """
        Task to execute a command
        :param code: Code to execute
        :param route: server callback REST route to return results
        :param op_id: operation id.
        """
        # Executing command
        (stat, out, err) = operations.exec_command(code)
        # Removing command from the list
        for c in self.commands:
            if c.getName() == op_id:
                self.commands.remove(c)
        # Sending results
        self.REST.sendMessage(route, {'client': self.interface.ip, 'trace': op_id, 'status': stat, 'output': out,
                                      'error': err})

    def process_command(self, path, get_params, post_params, server):
        """
        Launches a thread to executing a command
        :param path: ignored
        :param get_params: ignored
        :param post_params: object with format:
            id: operation id.
            script: command code
            redirect_url: callback REST route
        :param server: headers data
        :rtype: object with launching status
        """
        logger.debug('Received command operation with params: {}'.format(post_params))
        self.checkSecret(server)
        # Processing data
        try:
            script = post_params.get('script')
            op_id = post_params.get('id')
            route = post_params.get('redirect_url')
            # Checking if the thread id. exists
            for c in self.commands:
                if c.getName() == str(op_id):
                    raise Exception('Task id. already exists: {}'.format(op_id))
            # Launching a new thread
            thr = threading.Thread(name=op_id, target=self.task_command, args=(script, route, op_id))
            thr.start()
            self.commands.append(thr)
        except Exception as e:
            logger.error('Got exception {}'.format(e))
            return {'error': e}
        return {'op': 'launched'}

    def process_execinfo(self, path, get_params, post_params, server):
        """
        Returns running commands information
        :param path:
        :param get_params:
        :param post_params:
        :param server:
        :return: object
        """
        data = []
        logger.debug('Received execinfo operation')
        self.checkSecret(server)
        # Returning the arguments of all running threads
        for c in self.commands:
            if c.is_alive():
                data.append(c.__dict__['_Thread__args'])
        return data

    def process_stopcmd(self, path, get_params, post_params, server):
        logger.debug('Received stopcmd operation with params {}:'.format(post_params))
        self.checkSecret(server)
        op_id = post_params.get('trace')
        for c in self.commands:
            if c.is_alive() and c.getName() == str(op_id):
                c._Thread__stop()
                return {"stopped": op_id}
        return {}

    def process_hardware(self, path, get_params, post_params, server):
        """
        Returns client's hardware profile
        :param path:
        :param get_params:
        :param post_params:
        :param server:
        """
        data = []
        logger.debug('Received hardware operation')
        self.checkSecret(server)
        # Processing data
        try:
            for comp in operations.get_hardware():
                data.append({'component': comp.split('=')[0], 'value': comp.split('=')[1]})
        except:
            pass
        # Return list of hardware components
        return data

    def process_software(self, path, get_params, post_params, server):
        """
        Returns software profile installed on an operating system
        :param path:
        :param get_params:
        :param post_params:
        :param server:
        :return:
        """
        logger.debug('Received software operation with params: {}'.format(post_params))
        return operations.get_software(post_params.get('disk'), post_params.get('part'))
