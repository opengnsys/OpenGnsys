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
@author: Adolfo Gómez, dkmaster at dkmon dot com
@author: Ramón M. Gómez, ramongomez at us dot es
"""

# ModuleFinder can't handle runtime changes to __path__, but win32com uses them
try:
    # py2exe 0.6.4 introduced a replacement modulefinder.
    # This means we have to add package paths there, not to the built-in
    # one.  If this new modulefinder gets integrated into Python, then
    # we might be able to revert this some day.
    # if this doesn't work, try import modulefinder
    try:
        import py2exe.mf as modulefinder
    except ImportError:
        import modulefinder
    import win32com
    import sys
    for p in win32com.__path__[1:]:
        modulefinder.AddPackagePath("win32com", p)
    for extra in ["win32com.shell"]:  # ,"win32com.mapi"
        __import__(extra)
        m = sys.modules[extra]
        for p in m.__path__[1:]:
            modulefinder.AddPackagePath(extra, p)
except ImportError:
    # no build path setup, no worries.
    pass

import os
from distutils.core import setup

import sys

# Reading version file:
try:
    with open('VERSION', 'r') as v:
        VERSION = v.read()
except IOError:
    VERSION = '1.1.0'

sys.argv.append('py2exe')


def get_requests_cert_file():
    """Add Python requests or certifi .pem file for installers."""
    import requests
    f = os.path.join(os.path.dirname(requests.__file__), 'cacert.pem')
    if not os.path.exists(f):
        import certifi
        f = os.path.join(os.path.dirname(certifi.__file__), 'cacert.pem')
    return f


class Target:

    def __init__(self, **kw):
        self.__dict__.update(kw)
        # for the versioninfo resources
        self.version = VERSION
        self.name = 'OGAgentService'
        self.description = 'OpenGnsys Agent Service'
        self.author = 'Adolfo Gomez'
        self.url = 'https://opengnsys.es/'
        self.company_name = "OpenGnsys Project"
        self.copyright = "(c) 2014 VirtualCable S.L.U."
        self.name = "OpenGnsys Agent"

# Now you need to pass arguments to setup
# windows is a list of scripts that have their own UI and
# thus don't need to run in a console.


udsservice = Target(
    description='OpenGnsys Agent Service',
    modules=['opengnsys.windows.OGAgentService'],
    icon_resources=[(0, 'img\\oga.ico'), (1, 'img\\oga.ico')],
    cmdline_style='pywin32'
)

# Some test_modules are hidden to py2exe by six, we ensure that they appear on "includes"
HIDDEN_BY_SIX = ['SocketServer', 'SimpleHTTPServer', 'urllib']

setup(
    windows=[
        {
            'script': 'OGAgentUser.py',
            'icon_resources': [(0, 'img\\oga.ico'), (1, 'img\\oga.ico')]
        },
    ],
    console=[
        {
            'script': 'OGAServiceHelper.py'
        }
    ],
    service=[udsservice],
    data_files=[('', [get_requests_cert_file()]), ('cfg', ['cfg/ogagent.cfg', 'cfg/ogclient.cfg'])],
    options={
        'py2exe': {
            'bundle_files': 3,
            'compressed': True,
            'optimize': 2,
            'includes': ['sip', 'PyQt4', 'win32com.shell', 'requests', 'encodings', 'encodings.utf_8'] + HIDDEN_BY_SIX,
            'excludes': ['doctest', 'unittest'],
            'dll_excludes': ['msvcp90.dll'],
            'dist_dir': '..\\bin',
        }
    },
    name='OpenGnsys Agent',
    version=VERSION,
    description='OpenGnsys Agent',
    author='Adolfo Gomez',
    author_email='agomez@virtualcable.es',
    zipfile='OGAgent.zip', requires=['six']
)
