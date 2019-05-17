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
import signal

from opengnsys import REST
from opengnsys import operations
from opengnsys.log import logger
from opengnsys.workers import ServerWorker
from six.moves.urllib import parse


# Check authorization header decorator
def check_secret(fnc):
    """
    Decorator to check for received secret key and raise exception if it isn't valid.
    """
    def wrapper(*args, **kwargs):
        try:
            this, path, get_params, post_params, server = args  # @UnusedVariable
            if this.random == server.headers['Authorization']:
                fnc(*args, **kwargs)
            else:
                raise Exception('Unauthorized operation')
        except Exception as e:
            logger.error(e)
            raise Exception(e)

    return wrapper


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
    browser = {}  # Browser info
    commands = []  # Running commands
    random = None  # Random string for secure connections
    length = 32  # Random string length
    access_token = refresh_token = None  # Server authorization tokens
    grant_type = 'http://opengnsys.es/grants/og_client'

    def _launch_browser(self, url):
        """
        Launchs the Browser with specified URL
        :param url: URL to show
        """
        logger.debug('Launching browser with URL: {}'.format(url))
        # Trying to kill an old browser
        try:
            os.kill(self.browser['process'].pid, signal.SIGKILL)
        except OSError:
            logger.warn('Cannot kill the old browser process')
        except KeyError:
            # There is no previous browser
            pass
        self.browser['url'] = url
        self.browser['process'] = subprocess.Popen(['browser', '-qws', url])

    def _task_command(self, route, code, op_id, send_config=False):
        """
        Task to execute a command and return results to a server URI
        :param route: server callback REST route to return results
        :param code: code to execute
        :param op_id: operation id.
        """
        menu_url = ''
        # Show execution tacking log, if OGAgent runs on ogLive
        os_type = operations.os_type.lower()
        if os_type == 'oglive':
            menu_url = self.browser['url']
            self._launch_browser('http://localhost/cgi-bin/httpd-log.sh')
        # Execute the code
        (stat, out, err) = operations.exec_command(code)
        # Remove command from the list
        for c in self.commands:
            if c.getName() == op_id:
                self.commands.remove(c)
        # Remove the REST API prefix, if needed
        if route.startswith(self.REST.endpoint):
            route = route[len(self.REST.endpoint):]
        # Send back exit status and outputs (base64-encoded)
        self.REST.sendMessage(route, {'mac': self.interface.mac, 'ip': self.interface.ip, 'trace': op_id,
                                      'status': stat, 'output': out.encode('utf8').encode('base64'),
                                      'error': err.encode('utf8').encode('base64')})
        # Show latest menu, if OGAgent runs on ogLive
        if os_type == 'oglive':
            # Send configuration data, if needed
            if send_config:
                self.REST.sendMessage('clients/configs', {'mac': self.interface.mac, 'ip': self.interface.ip,
                                                          'config': operations.get_configuration()})
            self._launch_browser(menu_url)

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
        hosts_file = os.path.join(operations.get_etc_path(), 'hosts')
        new_file = hosts_file + '.' + self.interface.ip.split('.')[0]
        if os.path.isfile(new_file):
            shutil.copy2(new_file, hosts_file)
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
            raise Exception('Initialization error: Cannot connect to the server')
        finally:
            if response['access_token'] is None:
                raise Exception('Initialization error: Cannot obtain access token')
        self.access_token = response['access_token']
        self.refresh_token = response['refresh_token']
        # Once authenticated with the server, change the API URL for private request
        self.REST = REST(url + 'api/private')
        # Set authorization tokens in the REST object, so in each request this token will be used
        self.REST.set_authorization_headers(self.access_token, self.refresh_token)
        # Completing ogLive initialization process
        os_type = operations.os_type.lower()
        if os_type == 'oglive':
            # Create HTML file (TEMPORARY)
            message = """
<html>
<head></head>
<body>
<h1 style="margin: 5em; font-size: xx-large;">OpenGnsys 3</h1>
</body>
</html>"""
            f = open('/tmp/init.html', 'w')
            f.write(message)
            f.close()
            # Launching Browser
            self._launch_browser('/tmp/init.html')
            self.REST.sendMessage('clients/statuses', {'mac': self.interface.mac, 'ip': self.interface.ip,
                                                       'status': 'initializing'})
            # Send configuration message
            self.REST.sendMessage('clients/configs', {'mac': self.interface.mac, 'ip': self.interface.ip,
                                                      'config': operations.get_configuration()})
            # Launching new Browser with client's menu
            # menu_url = self.REST.sendMessage('menus?mac' + self.interface.mac + '&ip=' + self.interface.ip)
            menu_url = '/opt/opengnsys/log/' + self.interface.ip + '.info.html'  # TEMPORARY menu
            self._launch_browser(menu_url)
        # Return status message
        self.REST.sendMessage('clients/statuses', {'mac': self.interface.mac, 'ip': self.interface.ip,
                                                   'status': os_type})

    def onDeactivation(self):
        """
        Sends OGAgent stopping notification to OpenGnsys server
        """
        logger.debug('onDeactivation')
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

    def process_ogclient(self, path, get_params, post_params, server):
        """
        This method can be overridden to provide your own message processor, or better you can
        implement a method that is called exactly as "process_" + path[0] (module name has been removed from path
        array) and this default processMessage will invoke it
        * Example:
            Imagine this invocation url (no matter if GET or POST): http://example.com:9999/Sample/mazinger/Z
            The HTTP Server will remove "Sample" from path, parse arguments and invoke this method as this:
            module.processMessage(["mazinger","Z"], get_params, post_params)

            This method will process "mazinger", and look for a "self" method that is called "process_mazinger",
            and invoke it this way:
               return self.process_mazinger(["Z"], get_params, post_params)

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
        return operation(path[1:], get_params, post_params)

    def process_status(self, path, get_params, post_params, server):
        """
        Returns client status (OS type or execution status) and login status.
        :param path:
        :param get_params:
        :param post_params:
        :param server:
        :return: JSON object {"status": "status_code", "loggedin": boolean}
        """
        res = {'loggedin': self.loggedin}
        try:
            res['status'] = operations.os_type.lower()
        except KeyError:
            res['status'] = ''
        # Check if OpenGnsys Client is busy
        if res['status'] == 'oglive' and len(self.commands) > 0:
            res['status'] = 'busy'
        return res

    @check_secret
    def process_reboot(self, path, get_params, post_params, server):
        """
        Launches a system reboot operation.
        :param path:
        :param get_params:
        :param post_params:
        :param server: authorization header
        :return: JSON object {"op": "launched"}
        """
        logger.debug('Received reboot operation')

        # Rebooting thread
        def rebt():
            operations.reboot()

        threading.Thread(target=rebt).start()
        return {'op': 'launched'}

    @check_secret
    def process_poweroff(self, path, get_params, post_params, server):
        """
        Launches a system power off operation.
        :param path:
        :param get_params:
        :param post_params:
        :param server: authorization header
        :return: JSON object {"op": "launched"}
        """
        logger.debug('Received poweroff operation')

        # Powering off thread
        def pwoff():
            time.sleep(2)
            operations.poweroff()

        threading.Thread(target=pwoff).start()
        return {'op': 'launched'}

    @check_secret
    def process_script(self, path, get_params, post_params, server):
        """
        Processes an script execution (script should be encoded in base64)
        :param path:
        :param get_params:
        :param post_params: JSON object {"redirect_uri, "uri", "script": "commands", "id": trace_id}
        :param server: authorization header
        :return: JSON object {"op": "launched"} or {"error": "message"}
        """
        logger.debug('Processing script request')
        # Processing data
        try:
            script = urllib.unquote(post_params.get('script').decode('base64')).decode('utf8')
            op_id = post_params.get('id')
            route = post_params.get('redirectUri')
            send_config = (post_params.get('sendConfig', 'false') == 'true')
            # Check if the thread id. exists
            for c in self.commands:
                if c.getName() == str(op_id):
                    raise Exception('Task id. already exists: {}'.format(op_id))
            if post_params.get('client', 'false') == 'false':
                # Launching a new thread
                thr = threading.Thread(name=op_id, target=self._task_command, args=(route, script, op_id, send_config))
                thr.start()
                self.commands.append(thr)
            else:
                # Executing as normal user
                self.sendClientMessage('script', {'code': script})
        except Exception as e:
            logger.error('Got exception {}'.format(e))
            return {'error': e}
        return {'op': 'launched'}

    @check_secret
    def process_logoff(self, path, get_params, post_params, server):
        """
        Closes user session.
        """
        logger.debug('Received logoff operation')
        # Send log off message to OGAgent client.
        self.sendClientMessage('logoff', {})
        return {'op': 'sent to client'}

    @check_secret
    def process_popup(self, path, get_params, post_params, server):
        """
        Shows a message popup on the user's session.
        """
        logger.debug('Received message operation')
        # Send popup message to OGAgent client.
        self.sendClientMessage('popup', post_params)
        return {'op': 'launched'}

    def process_client_popup(self, params):
        self.REST.sendMessage('popup_done', params)

    @check_secret
    def process_config(self, path, get_params, post_params, server):
        """
        Returns client configuration
        :param path:
        :param get_params:
        :param post_params:
        :param server: authorization header
        :return: JSON object
        """
        serialno = ''  # Serial number
        storage = []  # Storage configuration
        warnings = 0  # Number of warnings
        logger.debug('Received getconfig operation')
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
                # Log warnings
                logger.warn('Configuration data error: {}'.format(cols))
                warnings += 1
        # Return configuration data and count of warnings
        return {'serialno': serialno, 'storage': storage, 'warnings': warnings}

    @check_secret
    def process_execinfo(self, path, get_params, post_params, server):
        """
        Returns running commands information
        :param path:
        :param get_params:
        :param post_params:
        :param server: authorization header
        :return: JSON array: [["callback_url", "commands", trace_id], ...]
        """
        data = []
        logger.debug('Received execinfo operation')
        # Return the arguments of all running threads
        for c in self.commands:
            if c.is_alive():
                data.append(c.__dict__['_Thread__args'])
        return data

    @check_secret
    def process_stopcmd(self, path, get_params, post_params, server):
        """
        Stops a running process identified by its trace id.
        :param path:
        :param get_params:
        :param post_params: JSON object {"trace": trace_id}
        :param server: authorization header
        :return: JSON object: {"stopped": trace_id}
        """
        logger.debug('Received stopcmd operation with params {}:'.format(post_params))
        # Find operation id. and stop the thread
        op_id = post_params.get('trace')
        for c in self.commands:
            if c.is_alive() and c.getName() == str(op_id):
                c._Thread__stop()
                return {"stopped": op_id}
        return {}
