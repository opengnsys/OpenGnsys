import { marker as tr } from '@biesbjerg/ngx-translate-extract-marker';

// This file can be replaced during build by using the `fileReplacements` array.
// `ng build --prod` replaces `environment.ts` with `environment.prod.ts`.
// The list of file replacements can be found in `angular.json`.
const url = 'http://172.16.53.200/opengnsys3';
export const environment = {
  production: false,
  BASE_URL: url,
  API_URL: url + '/index.php/api',
  API_PUBLIC_URL: url + '/index.php/api',
  API_BASE_URL: '/index.php/api',
  OAUTH_DOMAIN: '/index.php/oauth/v2/token',
  OAUTH_CLIENT_ID: '1_23amzbdp4kskg80444oscko4w0w8wokocs88k0g8w88o4oggs4',
  OAUTH_CLIENT_SECRET: '46rttt2trwo4gocgoc4w80k4s8ok48sg8s84kk0cw48csks8o8',
  BASE_DIR: 'opengnsys3',
  clientstatus: [
    {id: 0, name: tr('off')},
    {id: 1, name: tr('initializing')},
    {id: 2, name: tr('oglive')},
    {id: 3, name: tr('busy')},
    {id: 4, name: tr('linux')},
    {id: 5, name: tr('linux_session')},
    {id: 6, name: tr('macos')},
    {id: 7, name: tr('windows')},
    {id: 8, name: tr('windows_session')}
  ],
  windowsboots: ['reboot', 'directo (kexec)'],
  hardwareTypes: [
    {id: 'cha', name: tr('chassis')},
    {id: 'boo', name: tr('bios')},
    {id: 'mod', name: tr('model')},
    {id: 'boa', name: tr('motherboard')},
    {id: 'cpu', name: tr('cpu')},
    {id: 'mem', name: tr('memory')},
    {id: 'vga', name: tr('graphic_card')},
    {id: 'ide', name: tr('ide_controller')},
    {id: 'net', name: tr('network_card')},
    {id: 'usb', name: tr('usb_controller')},
    {id: 'mul', name: tr('multimedia_device')},
    {id: 'sto', name: tr('storage_controller')},
    {id: 'dis', name: tr('disk')},
    {id: 'cdr', name: tr('optical_device')},
  ],
  softwareTypes: [
    {id: 'app', name: tr('application')},
    {id: 'drv', name: tr('driver')},
    {id: 'os', name: tr('operating_system')}
  ],
  ou: {
    options: {
      p2p: {
        modes: ['peer', 'leecher', 'seeder']
      },
      multicast: {
        modes: ['Full-duplex', 'half-duplex'],
        ports: function() {
          const result = [];
          for (let index = 9000; index < 9051; index += 2) {
            result.push(index);
          }
          return result;
        }
      }
    }
  },
  menus: {
    resolutions: [
      { id: 'uvesafb:D', text: 'default_detected'},
      { id: 'uvesafb:800x600-16', text: '800x600, 16bit'},
      { id: 'uvesafb:800x600-24', text: '800x600, 24bit'},
      { id: 'uvesafb:800x600-32', text: '800x600, 32bit'},
      { id: 'uvesafb:1024x768-16', text: '1024x768, 16bit'},
      { id: 'uvesafb:1024x768-24', text: '1024x768, 24bit'},
      { id: 'uvesafb:1024x768-32', text: '1024x768, 32bit'},
      { id: 'uvesafb:1152x864-16', text: '1152x864, 16bit'},
      { id: 'uvesafb:1280x1024,16', text: '1280x1024, 16bit'},
      { id: 'uvesafb:1280x1024,24', text: '1280x1024, 24bit'},
      { id: 'uvesafb:1280x1024,32', text: '1280x1024, 32bit'},
      { id: 'uvesafb:1600x1200,16', text: '1600x1200, 16bit'},
      { id: 'uvesafb:1600x1200,24', text: '1600x1200, 24bit'},
      { id: 'uvesafb:1600x1200,32', text: '1600x1200, 32bit'}
    ],
    privateOptions: [
      {
        id: true,
        text: 'private'
      },
      {
        id: false,
        text: 'public'
      }
    ],
    itemImage: [
      {
        value: '0',
        text: ''
      },
      { value: '7', text: 'Logo General de Linux'},
      { value: '6', text: 'Logo Windows XP'},
      { value: '9', text: 'Ordenador apagado'},
      { value: '10', text: 'Ordenador encendido'},
      { value: '8', text: 'Particionar'}
    ]
  },
  user: {
    preferences: {
      ous: {
        showGrid: true
      },
      language: 'es',
      theme: 'blue'
    }
  },
  languages: [
    {
      id: 'es',
      name: 'Español'
    },
    {
      id: 'en',
      name: 'English'
    },
    {
      id: 'cat',
      name: 'Català'
    }
  ],
  partitionTableTypes: {
    '1': 'MSDOS',
    '2': 'GPT',
    '3': 'LVM',
    '4': 'ZPOOL'
  },
  deployMethods: {
    deployImage: ['MULTICAST', 'MULTICAST-DIRECT', 'UNICAST', 'UNICAST-DIRECT', 'TORRENT'],
    updateCache: ['MULTICAST', 'UNICAST', 'TORRENT']
  },
  commands: {
    'CREATE_IMAGE': '/opt/opengnsys/interfaceAdm/CrearImagen',
    'HISTORY_LOG': '/cgi-bin/httpd-history-log.sh',
    'REALTIME_LOG': '/cgi-bin/httpd-log.sh',
    'SOFTWARE_INVENTORY': '/opt/opengnsys/interfaceAdm/InventarioSoftware',
    'REBOOT': 'reboot 1',
    'POWER_OFF': 'poweroff',
    'HARDWARE_INVENTORY': '/opt/opengnsys/interfaceAdm/InventarioHardware',
    'REFRESH_INFO': 'php /opt/opengnsys/ogClient/bin/console GetConfiguration 0'
  },
  themes: ['black', 'black-light', 'blue-light', 'blue', 'green', 'green-light', 'purple', 'purple-light', 'red', 'red-light', 'yellow', 'yellow-light', 'uhu', 'uhu-light']


};

/*
 * For easier debugging in development mode, you can import the following file
 * to ignore zone related error stack frames such as `zone.run`, `zoneDelegate.invokeTask`.
 *
 * This import should be commented out in production mode because it will have a negative impact
 * on performance if an error is thrown.
 */
// import 'zone.js/dist/zone-error';  // Included with Angular CLI.
