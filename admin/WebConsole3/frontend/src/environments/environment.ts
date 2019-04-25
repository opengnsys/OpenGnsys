// This file can be replaced during build by using the `fileReplacements` array.
// `ng build --prod` replaces `environment.ts` with `environment.prod.ts`.
// The list of file replacements can be found in `angular.json`.
const url = 'https://SERVERIP/opengnsys3';
export const environment = {
  production: false,
  BASE_URL: url,
  API_URL: url + '/backend/web/app_dev.php/api/private',
  API_PUBLIC_URL: url + '/api',
  API_BASE_URL: '/backend/web/app_dev.php/api',
  OAUTH_DOMAIN: '/backend/web/app_dev.php/oauth/v2/token',
  OAUTH_CLIENT_ID: '1_23amzbdp4kskg80444oscko4w0w8wokocs88k0g8w88o4oggs4',
  OAUTH_CLIENT_SECRET: '46rttt2trwo4gocgoc4w80k4s8ok48sg8s84kk0cw48csks8o8',
  BASE_DIR: 'opengnsys3',
  clientstatus: ['off', 'initializing', 'oglive', 'busy', 'linux', 'linux_session', 'macos', 'windows', 'windows_session'],
  windowsboots: ['reboot', 'directo (kexec)'],
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
      theme: 'skin-black'
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
    'REBOOT': 'reboot 1 &',
    'POWER_OFF': 'poweroff &',
    'HARDWARE_INVENTORY': '/opt/opengnsys/interfaceAdm/InventarioHardware',
    'REFRESH_INFO': 'sendConfigToServer'
  },
  themes: ['skin-black', 'skin-black-light', 'skin-blue-light', 'skin-blue', 'skin-green', 'skin-green-light', 'skin-purple', 'skin-purple-light', 'skin-red', 'skin-red-light', 'skin-yellow', 'skin-yellow-light', 'skin-uhu', 'skin-uhu-light']


};

/*
 * For easier debugging in development mode, you can import the following file
 * to ignore zone related error stack frames such as `zone.run`, `zoneDelegate.invokeTask`.
 *
 * This import should be commented out in production mode because it will have a negative impact
 * on performance if an error is thrown.
 */
// import 'zone.js/dist/zone-error';  // Included with Angular CLI.
