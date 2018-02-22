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

# This is a simple module loader, so we can add "external opengnsys" modules as addons
# Modules under "opengsnsys/modules" are always autoloaded
from __future__ import unicode_literals

import pkgutil
import os.path

from opengnsys.workers import ServerWorker
from opengnsys.workers import ClientWorker
from .log import logger


def loadModules(controller, client=False):
    '''
    Load own provided modules plus the modules that are in the configuration path.
    The loading order is not defined (they are loaded as found, because modules MUST be "standalone" modules
    @param service: The service that:
       * Holds the configuration
       * Will be used to initialize modules.
    '''

    ogModules = []
    
    if client is False:
        from opengnsys.modules.server import OpenGnSys  # @UnusedImport
        from .modules import server  # @UnusedImport, just used to ensure opengnsys modules are initialized
        modPath = 'opengnsys.modules.server'
        modType = ServerWorker
    else:
        from opengnsys.modules.client import OpenGnSys  # @UnusedImport @Reimport
        from .modules import client  # @UnusedImport, just used to ensure opengnsys modules are initialized
        modPath = 'opengnsys.modules.client'
        modType = ClientWorker
    
    def addCls(cls):
        logger.debug('Found module class {}'.format(cls))
        try:
            if cls.name is None:
                # Error, cls has no name
                # Log the issue and 
                logger.error('Class {} has no name attribute'.format(cls))
                return
            ogModules.append(cls(controller))
        except Exception as e:
            logger.error('Error loading module {}'.format(e))

    def recursiveAdd(p):
        subcls = p.__subclasses__()
        
        if len(subcls) == 0:
            addCls(p) 
        else:
            for c in subcls:
                recursiveAdd(c)

    def doLoad(paths):
        for (module_loader, name, ispkg) in pkgutil.iter_modules(paths, modPath + '.'):
            if ispkg:
                logger.debug('Found module package {}'.format(name))
                module_loader.find_module(name).load_module(name)

    
    if controller.config.has_option('opengnsys', 'path') is True:
        paths = tuple(os.path.abspath(v) for v in controller.config.get('opengnsys', 'path').split(','))
    else:
        paths = ()
    
    # paths += (os.path.dirname(sys.modules[modPath].__file__),)

    logger.debug('Loading modules from {}'.format(paths))
    
    # Load modules      
    doLoad(paths)
    
    # Add to list of available modules
    recursiveAdd(modType)
    
    return ogModules
