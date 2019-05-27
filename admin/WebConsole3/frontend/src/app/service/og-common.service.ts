import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {EngineService} from '../api/engine.service';
import {TranslateService} from '@ngx-translate/core';
import {AuthModule} from 'globunet-angular/core';
import {HardwareProfile} from '../model/hardware-profile';
import {HardwareComponent} from '../model/hardware-component';
import {environment} from '../../environments/environment';
import {LayoutStore} from 'angular-admin-lte';
import {AdminLteConf} from '../admin-lte.conf';
import {User, UserPreferences} from '../model/user';

@Injectable({
    providedIn: 'root'
})
export class OgCommonService {
    private constants: any = null;
    private timers: any = null;
    private user: any;
    private app: any;
    private groups: any;

    public selectedClients: any;
    public selectedOu: any;
    public movingOu: any;
    public movingClients: boolean;

    public showLoader: boolean;

    constructor(private layoutStore: LayoutStore, private adminLteConfig: AdminLteConf, private engineService: EngineService, private translate: TranslateService, private authModule: AuthModule) {
        this.app = {};
        this.selectedClients = {};
        this.selectedOu = null;
        this.movingOu = null;
        this.movingClients = false;
        this.showLoader = true;
        this.constants = {
            ou: environment.ou,
            themes: environment.themes,
            menus: environment.menus,
            languages: environment.languages,
            deployMethods: environment.deployMethods,
            commands: environment.commands,
            user: environment.user
        };
        this.loadEngineConfig();
        /*
        if (localStorage.getItem('selectedClients')) {
          this.selectedClients = JSON.parse(localStorage.getItem('selectedClients'));
        }
        */
    }

    loadEngineConfig(): Observable<{ constants: any, timers: any }> {
        // Cargar en el rootScope los arrays de objetos comunes al sistema
        return new Observable((observer) => {
            if (this.constants === null || this.timers === null) {
                this.engineService.list().subscribe(
                    data => {
                        this.constants = Object.assign(this.constants, data[0]);
                        // inicializar timers generales para refresco de información
                        this.timers = {
                            serverStatusInterval: {
                                tick: 5000,
                                object: null
                            },
                            clientsStatusInterval: {
                                tick: 5000,
                                object: null
                            },
                            executionsInterval: {
                                tick: 5000,
                                object: null
                            },

                        };
                        observer.next({constants: this.constants, timers: this.timers});
                    },
                    (error) => {
                        observer.error(error);
                    }
                );
            } else {
                observer.next({constants: this.constants, timers: this.timers});
            }

        });
    }

    loadUserConfig(): UserPreferences {
        const user = new User();
        if (localStorage.getItem('og_user_preferences')) {
            user.preferences = JSON.parse(localStorage.getItem('og_user_preferences'));
        }
        this.user = this.authModule.getLoggedUser(user);
        // si no existen las preferencias de usuario se crean
        if (!this.user.preferences) {
            this.user.preferences = this.constants.user.preferences;
        }
        if (this.user.preferences.language) {
            this.translate.use(this.user.preferences.language);
        }
        this.layoutStore.setSkin(this.user.preferences.theme);
        this.layoutStore.setLayout(this.user.preferences.layout);
        this.layoutStore.sidebarLeftMini(this.user.preferences.isSidebarLeftMini);
        this.layoutStore.sidebarLeftExpandOnOver(this.user.preferences.isSidebarLeftExpandOnOver);
        this.layoutStore.setSidebarRightSkin(this.user.preferences.sidebarRightSkin);
        return this.user.preferences;
    }

    saveUserPreferences(preferences) {
        this.user = this.authModule.getLoggedUser();
        localStorage.setItem('og_user_preferences', JSON.stringify(preferences));
        this.loadUserConfig();
    }


    changeLanguage(langKey) {
        this.translate.use(langKey);
        this.layoutStore.setSidebarLeftMenu(this.adminLteConfig.get().sidebarLeftMenu);
    }

    createGroups(array, property) {
        let groups = [];
        const newArray = [];

        // Extraer los grupos de los perfiles hardware
        for (let index = 0; index < array.length; index++) {
            const obj = array[index];
            let group = obj.group;
            if (typeof group !== 'undefined') {
                group = this.addGroup(groups, group);
                // Si no se encontró el grupo, buscamos entre los de rootScope
                if (group == null) {
                    let g = this.groups.filter(element => element.id === obj.group.parent.id);
                    g = g[0];
                    if (!g.groups) {
                        g.groups = [];
                    }
                    g.groups.push(obj.group);
                    groups.push(g);
                    group = obj.group;
                }
                delete obj.group;

                if (!group[property]) {
                    group[property] = [];
                }
                group[property].push(obj);
            } else {
                newArray.push(obj);
            }
        }
        groups = {
            // @ts-ignore
            groups: groups,
        };
        groups[property] = newArray;
        return groups;
    }

    addGroup(groups, group) {
        let found = null;
        if (!group.parent) {
            const tmp = groups.filter((element) => element.id === group.id);
            if (tmp.length === 0) {
                groups.push(group);
            } else {
                group = tmp[0];
            }
            found = group;
        } else {
            let index = 0;
            // buscar el grupo donde insertarlo
            while (found == null && index < groups.length) {
                if (groups[index].id === group.parent.id) {
                    if (!groups[index].groups) {
                        groups[index].groups = [];
                        groups[index].groups.push(group);
                    } else {
                        // Comprobar si ya contiene el grupo, sino, se añade
                        const tmp = groups[index].groups.filter((element) => element.id === group.id);
                        if (tmp.length === 0) {
                            groups[index].groups.push(group);
                        } else {
                            group = tmp[0];
                        }
                    }
                    found = group;
                } else if (groups[index].groups) {
                    found = this.addGroup(groups[index].groups, group);
                }
                index++;
            }
        }
        return found;
    }


    selectClient(client, parent) {
        client.parent = parent;
        if (client.selected) {
            this.selectedClients[client.id] = client;
        } else {
            delete this.selectedClients[client.id];
        }
        this.saveSelection();
    }

    saveSelection() {
        /*
        localStorage.setItem('selectedClients', JSON.stringify(this.selectedClients, function (key, value) {
          let result = value;
          if (key === 'parent' && typeof value === 'object') {
            result = value.id;
          }
          return result;
        }));
        */
    }

    getSelectionSize() {
        return Object.keys(this.selectedClients).length;
    }

    isMovingClients() {
        return (this.movingClients === true);
    }

    /**/

    selectForMove(ou, select?) {
        // si existe una operacion de movimiento de clientes se cancela
        this.movingClients = false;
        if (typeof select === 'undefined') {
            this.movingOu = (this.movingOu === ou) ? null : ou;
            select = select || (this.movingOu === ou);
        }
        // seleccionar/deseleccionar todos los elementos dentro de ou
        for (let i = 0; i < ou.children.length; i++) {
            ou.children[i].selectedForMove = select;
            this.selectForMove(ou.children[i], select);
        }
    }

    /**
     * Dada la particion 0 de la configuracion de un cliente, devuelve el objeto partitionTable asociado
     */
    getPartitionTable(partition) {
        return this.constants.partitiontable[parseInt(partition.partitionCode, 10) - 1];
    }

    getDisksConfigFromPartitions(partitions) {
        // Ordenar la lista por numero de partición
        partitions = partitions.sort(function(p1, p2) {
            let result = 0;
           if (p1.numPartition < p2.numPartition) {
               result = -1;
           } else if (p1.numPartition > p2.numPartition) {
               result = 1;
           }
           return result;
        });
        const disks = [];
        let partitionTable;
        // La partición 0 es la configuración del disco
        for (let p = 0; p < partitions.length; p++) {
            const partition = partitions[p];
            if (!disks[partition.numDisk - 1]) {
                disks.push({});
            }

            // La partición 0 es la configuración del disco
            if (partition.numPartition === 0) {
                partitionTable = this.getPartitionTable(partition);
                disks[partition.numDisk - 1] = {
                    size: partition.size,
                    disk: partition.numDisk,
                    parttable: partitionTable.type,
                    partitions: []
                };
            } else {
                // Comprobar el tipo de partición dependiendo del código
                const elements = partitionTable.partitions.filter((element) => (element.id === partition.partitionCode));
                partition.parttype = (elements.length > 0) ? elements[0].type : '';
                // Si es cache, actualizar su contenido
                if (partition.partitionCode === 'ca') {
                    // actualizar el contenido de la cache
                    if (typeof partition.cacheContent === 'string') {
                        let cacheContent = [];
                        cacheContent = partition.cacheContent.trim().split(',');
                        const cacheContentObj = {
                            files: [],
                            freeSpace: 0
                        };
                        for (let index = 0; index < cacheContent.length; index++) {
                            if (index === 0) {
                                cacheContentObj.freeSpace = cacheContent[index];
                            } else {
                                if (cacheContent[index] !== '') {
                                    const parts = cacheContent[index].trim().split(' ');

                                    const fileSize = parts[0].trim() + 'KB';
                                    const fileName = parts[1].trim();
                                    const file = {name: fileName, size: fileSize, type: ''};
                                    file.type = (file.name.indexOf('/') !== -1) ? 'D' : 'F';
                                    cacheContentObj.files.push(file);
                                }
                            }
                        }
                        partition.cacheContent = cacheContentObj;
                    } else if (!partition.cacheContent) {
                        partition.cacheContent = [];
                    }
                }
                disks[partition.numDisk - 1].partitions.push(partition);
            }

        }
        return disks;
    }

    getUnits(bytes) {
        let units = 'B';
        let divider = 1;
        if (bytes > 1073741824) {
            units = 'GB';
            divider = 1024 * 1024 * 1024;
        } else if (bytes > 1048576) {
            units = 'MB';
            divider = 1024 * 1024;
        } else if (bytes > 1024) {
            units = 'KB';
            divider = 1024;
        }
        return Math.round((bytes / divider) * 100) / 100 + ' ' + units;
    }


    checkUnchekComponent(profile: any, component: any) {
        // Seleccionar o deseleccionar el componente en el perfil hardware o software
        const array = profile.hardwares || profile.softwares;
        // Si el componente que llega está deseleccionado
        if (component.$$selected === false) {
            // Hay que quitarlo del perfil hardware
            const index = array.indexOf(component.id);
            if (index !== -1) {
                array.splice(index, 1);
            }
        } else {
            array.push(component.id);
        }
    }
}
