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

import socket
import platform
import fcntl
import os
import ctypes  # @UnusedImport
import ctypes.util
import subprocess
import struct
import array
import six
from opengnsys import utils


def checkLockedPartition(sync=False):
    '''
    Decorator to check if a partition is locked
    '''
    def outer(fnc):
        def wrapper(*args, **kwargs):
            partId = 'None'
            try:
                this, path, getParams, postParams = args  # @UnusedVariable
                partId = postParams['disk'] + postParams['part']
                if this.locked.get(partId, False):
                    this.locked[partId] = True
                    fnc(*args, **kwargs)
                else:
                    return 'partition locked'
            except Exception as e:
                this.locked[partId] = False
                return 'error {}'.format(e)
            finally:
                if sync is True:
                    this.locked[partId] = False
            logger.debug('Lock status: {} {}'.format(fnc, this.locked))
        return wrapper
    return outer


def _getMacAddr(ifname):
    '''
    Returns the mac address of an interface
    Mac is returned as unicode utf-8 encoded
    '''
    if isinstance(ifname, list):
        return dict([(name, _getMacAddr(name)) for name in ifname])
    if isinstance(ifname, six.text_type):
        ifname = ifname.encode('utf-8')  # If unicode, convert to bytes (or str in python 2.7)
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        info = bytearray(fcntl.ioctl(s.fileno(), 0x8927, struct.pack(str('256s'), ifname[:15])))
        return six.text_type(''.join(['%02x:' % char for char in info[18:24]])[:-1])
    except Exception:
        return None


def _getIpAddr(ifname):
    '''
    Returns the ip address of an interface
    Ip is returned as unicode utf-8 encoded
    '''
    if isinstance(ifname, list):
        return dict([(name, _getIpAddr(name)) for name in ifname])
    if isinstance(ifname, six.text_type):
        ifname = ifname.encode('utf-8')  # If unicode, convert to bytes (or str in python 2.7)
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        return six.text_type(socket.inet_ntoa(fcntl.ioctl(
            s.fileno(),
            0x8915,  # SIOCGIFADDR
            struct.pack(str('256s'), ifname[:15])
        )[20:24]))
    except Exception:
        return None


def _getInterfaces():
    '''
    Returns a list of interfaces names coded in utf-8
    '''
    max_possible = 128  # arbitrary. raise if needed.
    space = max_possible * 16
    if platform.architecture()[0] == '32bit':
        offset, length = 32, 32
    elif platform.architecture()[0] == '64bit':
        offset, length = 16, 40
    else:
        raise OSError('Unknown arquitecture {0}'.format(platform.architecture()[0]))

    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    names = array.array(str('B'), b'\0' * space)
    outbytes = struct.unpack(str('iL'), fcntl.ioctl(
        s.fileno(),
        0x8912,  # SIOCGIFCONF
        struct.pack(str('iL'), space, names.buffer_info()[0])
    ))[0]
    namestr = names.tostring()
    # return namestr, outbytes
    return [namestr[i:i + offset].split(b'\0', 1)[0].decode('utf-8') for i in range(0, outbytes, length)]


def _getIpAndMac(ifname):
    ip, mac = _getIpAddr(ifname), _getMacAddr(ifname)
    return (ip, mac)


def _exec_ogcommand(self, ogcmd):
    '''
    Loads OpenGnsys environment variables, executes the command and returns the result
    '''
    ret = subprocess.check_output('source /opt/opengnsys/etc/preinit/loadenviron.sh >/dev/null; {}'.format(ogcmd), shell=True)
    return ret


def getComputerName():
    '''
    Returns computer name, with no domain
    '''
    return socket.gethostname().split('.')[0]


def getNetworkInfo():
    '''
    Obtains a list of network interfaces
    @return: A "generator" of elements, that are dict-as-object, with this elements:
      name: Name of the interface
      mac: mac of the interface
      ip: ip of the interface
    '''
    for ifname in _getInterfaces():
        ip, mac = _getIpAndMac(ifname)
        if mac != '00:00:00:00:00:00':  # Skips local interfaces
            yield utils.Bunch(name=ifname, mac=mac, ip=ip)


def getDomainName():
    return ''


def getOgliveVersion():
    lv = platform.linux_distribution()
    return lv[0] + ', ' + lv[1]


def reboot():
    '''
    Simple reboot using OpenGnsys script
    '''
    # Workaround for dummy thread
    if six.PY3 is False:
        import threading
        threading._DummyThread._Thread__stop = lambda x: 42

    _exec_ogcommand('/opt/opengnsys/scripts/reboot', shell=True)


def poweroff():
    '''
    Simple poweroff using OpenGnsys script
    '''
    # Workaround for dummy thread
    if six.PY3 is False:
        import threading
        threading._DummyThread._Thread__stop = lambda x: 42

    _exec_ogcommand('/opt/opengnsys/scripts/poweroff', shell=True)


def logoff():
    pass


def renameComputer(newName):
    pass


def joinDomain(domain, ou, account, password, executeInOneStep=False):
    pass


def changeUserPassword(user, oldPassword, newPassword):
    pass


def diskconfig():
    '''
    Returns disk configuration.
    Warning: this operation may take some time.
    '''
    try:
        _exec_ogcommand('/opt/opengnsys/interfaceAdm/getConfiguration')
        # Returns content of configuration file.
        cfgdata = open('/tmp/getconfig', 'r').read() 
    except IOError:
        cfgdata = ''
    return cfgdata

