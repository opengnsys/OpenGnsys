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

from opengnsys.service import CommonService
from opengnsys.service import IPC_PORT
from opengnsys import ipc

from opengnsys.log import logger

from opengnsys.linux.daemon import Daemon

import sys
import signal
import json

try:
    from prctl import set_proctitle  # @UnresolvedImport
except Exception:  # Platform may not include prctl, so in case it's not available, we let the "name" as is
    def set_proctitle(_):
        pass


class OGAgentSvc(Daemon, CommonService):
    def __init__(self, args=None):
        Daemon.__init__(self, '/var/run/opengnsys-agent.pid')
        CommonService.__init__(self)

    def run(self):
        logger.debug('** Running Daemon **')
        set_proctitle('OGAgent')

        self.initialize()

        # Call modules initialization
        # They are called in sequence, no threading is done at this point, so ensure modules onActivate always returns
        

        # *********************
        # * Main Service loop *
        # *********************
        # Counter used to check ip changes only once every 10 seconds, for
        # example
        try:
            while self.isAlive:
                # In milliseconds, will break
                self.doWait(1000)
        except (KeyboardInterrupt, SystemExit) as e:
            logger.error('Requested exit of main loop')
        except Exception as e:
            logger.exception()
            logger.error('Caught exception on main loop: {}'.format(e))

        self.terminate()

        self.notifyStop()
        
    def signal_handler(self, signal, frame):
        self.isAlive = False
        sys.stderr.write("signal handler: {}".format(signal))


def usage():
    sys.stderr.write("usage: {} start|stop|restart|fg|login 'username'|logout 'username'|message 'module' 'message' 'json'\n".format(sys.argv[0]))
    sys.exit(2)

if __name__ == '__main__':
    logger.setLevel('INFO')
    
    if len(sys.argv) == 5 and sys.argv[1] == 'message':
        logger.debug('Running client opengnsys')
        client = None
        try:
            client = ipc.ClientIPC(IPC_PORT)
            client.sendMessage(sys.argv[2], sys.argv[3], json.loads(sys.argv[4]))
            sys.exit(0)
        except Exception as e:
            logger.error(e)
        

    if len(sys.argv) == 3 and sys.argv[1] in ('login', 'logout'):
        logger.debug('Running client opengnsys')
        client = None
        try:
            client = ipc.ClientIPC(IPC_PORT)
            if 'login' == sys.argv[1]:
                client.sendLogin(sys.argv[2])
                sys.exit(0)
            elif 'logout' == sys.argv[1]:
                client.sendLogout(sys.argv[2])
                sys.exit(0)
            else:
                usage()
        except Exception as e:
            logger.error(e)
    elif len(sys.argv) != 2:
        usage()

    logger.debug('Executing actor')
    daemon = OGAgentSvc()
    
    signal.signal(signal.SIGTERM, daemon.signal_handler)
    signal.signal(signal.SIGINT, daemon.signal_handler)

    if len(sys.argv) == 2:
        if 'start' == sys.argv[1]:
            daemon.start()
        elif 'stop' == sys.argv[1]:
            daemon.stop()
        elif 'restart' == sys.argv[1]:
            daemon.restart()
        elif 'fg' == sys.argv[1]:
            daemon.run()
        else:
            usage()
        sys.exit(0)
    else:
        usage()
