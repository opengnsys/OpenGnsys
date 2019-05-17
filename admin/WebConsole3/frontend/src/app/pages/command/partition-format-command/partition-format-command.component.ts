import {Component, OnInit} from '@angular/core';

import {ToasterService} from '../../../service/toaster.service';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {OgCommonService} from '../../../service/og-common.service';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {AuthModule} from 'globunet-angular/core';
import {User} from '../../../model/user';
import {OGCommandsService} from '../../../service/og-commands.service';
import {CommandService} from '../../../api/command.service';
import {DomSanitizer} from '@angular/platform-browser';
import {ChartOptions} from 'chart.js';
import {Client} from '../../../model/client';
import {Execution} from '../../../model/command';

@Component({
  selector: 'app-partition-format-command',
  templateUrl: './partition-format-command.component.html',
  styleUrls: [ './partition-format-command.component.scss' ]
})
export class PartitionFormatCommandComponent implements OnInit {
  execution = new Execution();
  command = {};
  user: User;
  constants: any;
  diskConfig: any;
  partitionTableTypes = ['MSDOS', 'GPT'];
  partitionTypes = [];
  editInstructions = false;

  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public ogCommandsService: OGCommandsService,
              public sanitizer: DomSanitizer,
              private authModule: AuthModule,
              private router: Router,
              private activatedRoute: ActivatedRoute,
              public ogCommonService: OgCommonService,
              private commandService: CommandService,
              private ogSweetAlert: OgSweetAlertService,
              private toaster: ToasterService,
              private translate: TranslateService) {
    this.user = this.authModule.getLoggedUser();
    this.diskConfig = {};
    this.ogCommonService.saveSelection();
  }

  ngOnInit() {
    if (this.user) {
      this.ogCommonService.loadEngineConfig().subscribe(
          data => {
            this.constants = data.constants;
            // Comprobar la selección de clientes
            if (this.ogCommonService.selectedClients) {

              // Recorrer todos los clientes seleccionados y usar el tamaño del disco de menor tamaño
              const clientsId = Object.keys(this.ogCommonService.selectedClients);
              let minSize = 0;
              // por defecto la tabla de particiones msdos
              let parttable = this.ogCommonService.getPartitionTable({partitionCode: 1});
              if (this.ogCommonService.selectedClients[clientsId[0]].partitions[0]) {
                minSize = this.ogCommonService.selectedClients[clientsId[0]].partitions[0].size;
                parttable = this.ogCommonService.getPartitionTable(this.ogCommonService.selectedClients[clientsId[0]].partitions[0]);
              }

              for (let c = 1; c < clientsId.length; c++) {
                if (this.ogCommonService.selectedClients[clientsId[0]].partitions[0].size < minSize) {
                  minSize = this.ogCommonService.selectedClients[clientsId[0]].partitions[0].size;

                }
              }

              this.diskConfig = {
                disk: 1,
                parttable: parttable,
                size: minSize,
                partitions: [
                  {
                    partition: 0,
                    type: 'free_space',
                    filesystem: '',
                    size: minSize,
                    usage: 100,
                  }
                ]
              };
              // TODO - Revisar Usamos la tabla de particiones del cliente seleccionado
              const client: Client = this.ogCommonService.selectedClients[clientsId[0]];
              const clientPartitions = [];
              client.partitions.forEach((partition) => {
                if (partition.numPartition !== 0 && partition.filesystem !== 'EMPTY') {

                  // Obtener el tipo de partición según su code
                  if (partition.partitionCode.length === 1) {
                    partition.partitionCode = '0' + partition.partitionCode;
                  }
                  const partTypes = this.diskConfig.parttable.partitions.filter(p => p.id === partition.partitionCode.toUpperCase());
                  if (partTypes.length > 0) {
                    partition.type = partTypes[0].type;
                  }
                  clientPartitions.push(partition);
                }
              });
              this.diskConfig.partitions = clientPartitions.concat((this.diskConfig.partitions));
              this.reorderPartitions();

              this.setChartData(this.diskConfig);
              this.partitionTableTypes = this.constants.partitiontable;
            } else {
              // TODO - dar error?
              this.ogSweetAlert.error(this.translate.instant('opengnsys_error'), this.translate.instant('not_clients_selected'));
              this.router.navigate(['app/ous']);
            }

          }
      );
    }
  }


  partTableTypeIsGPT() {
    return this.diskConfig.parttable.type === 'GPT';
  }

  partTableTypeIsMSDOS() {
    return this.diskConfig.parttable.type === 'MSDOS';
  }

  partTableTypeIsLVM() {
    return this.diskConfig.parttable.type === 'LVM';
  }
  partTableTypeIsZPOOL() {
    return this.diskConfig.parttable.type === 'ZPOOL';
  }

  isEFI(partition) {
    return partition.type === 'EFI';
  }

  isCACHE(partition) {
    return partition.type === 'CACHE';
  }

  isEXTENDED(partition) {
    return partition.type === 'EXTENDED';
  }

  isWINDOWS(partition) {
    return partition.type === 'NTFS' || partition.type === 'WINDOWS';
  }

  isLINUX(partition) {
    return typeof partition.type === 'string' && partition.type.includes('LINUX');
  }

  isLINUXSWAP(partition) {
    return partition.type === 'LINUX-SWAP';
  }

  isDATA(partition) {
    return partition.type === 'DATA';
  }

  isUNKNOWN(partition) {
    return partition.type === 'UNKNOWN';
  }

  isFreeSpace(partition) {
    return partition.type === 'free_space';
  }


  convertPartitionType(partition) {
    if (this.partTableTypeIsMSDOS()) {
      if (this.isWINDOWS(partition)) {
        partition.type = 'NTFS';
      } else if (this.isUNKNOWN(partition)) {
        partition.type = 'NTFS';
      }
    } else if (this.partTableTypeIsGPT()) {
      if (this.isWINDOWS(partition)) {
        partition.type = 'WINDOWS';
      } else if (this.isDATA(partition)) {
        partition.type = 'UNKNOWN';
      }
    } else if (this.partTableTypeIsLVM()) {
      partition.type = 'LVM-LV';
    } else if (this.partTableTypeIsZPOOL()) {
      partition.type = 'ZFS-VOL';
    }
  }

  checkPartitionTableType() {
    const self = this;
    if (this.partTableTypeIsMSDOS()) {
      if (this.diskConfig.partitions.length > 5) {
        this.ogSweetAlert.info('opengnsys_info', 'En MS-DOS sólo puede haber 4 particiones primarias, se creará una extendida con el resto de particiones');
        const tmpPartitions = [];
        const extendedPartition = {
          type: 'EXTENDED',
          partitions: [],
          size: 0,
          usage: 0
        };
        const hasCache = (this.diskConfig.partitions.filter((partition) => partition.type === 'CACHE').length > 0);
        // Si tiene cache se añaden 2 particiones, más la cache y el espacio libre
        const numParts = hasCache ? 2 : 3;
        this.diskConfig.partitions.forEach(function(partition, index) {
          self.convertPartitionType(partition);
          if (index < numParts || self.isFreeSpace(partition) || self.isCACHE(partition)) {
            tmpPartitions.push(partition);
          } else {
            extendedPartition.partitions.push(partition);
            extendedPartition.size += partition.size;
          }
        });
        // Actualizar porcentajes de las particiones extendidas
        for (let p = 0; p < extendedPartition.partitions.length; p++) {
          self.setPartitionUsage(extendedPartition, extendedPartition.partitions[p]);
        }
        tmpPartitions.push(extendedPartition);
        self.diskConfig.partitions = tmpPartitions;
        self.updatePartitionUsage(this.diskConfig.partitions[0]);
      } else {
        self.diskConfig.partitions.forEach(function(partition, index) {
          self.convertPartitionType(partition);
        });
      }

    } else {
      const tmpPartitions = [];
      // Para particiones GPT se crea una particion EFI en primer lugar de 512M
      if (this.partTableTypeIsGPT()) {
        // Comprobar si existe ya una partición EFI al principio del disco, sino, crearla
        if (!this.isEFI(this.diskConfig.partitions[0])) {
          tmpPartitions.push({
            type: 'EFI',
            size: 512000,
            usage: (512000 / this.diskConfig.size) * 100
          });
        }
      }

      this.diskConfig.partitions.forEach(function(partition) {
        self.convertPartitionType(partition);
        if (!self.isEXTENDED(partition)) {
          tmpPartitions.push(partition);
        } else {
          partition.partitions.forEach(function(extPart) {
            self.convertPartitionType(extPart);
            tmpPartitions.push(extPart);
            self.setPartitionUsage(this.diskConfig, extPart);
          });
        }
      });
      this.diskConfig.partitions = tmpPartitions;
      this.updatePartitionUsage(this.diskConfig.partitions[0]);
    }
  }

  addPartition() {
    // Si el tipo de tabla de particiones es MSDOS, sólo se admiten 4 particiones
    if (this.partTableTypeIsGPT() || (this.partTableTypeIsMSDOS() && this.diskConfig.partitions.length < 5)) {
      this.diskConfig.partitions.push({
        partition: (this.diskConfig.partitions.length),
        type: this.partTableTypeIsGPT() ? 'WINDOWS' : 'NTFS',
        filesystem: '',
        size: 0,
        usage: 5

      });
      this.updatePartitionUsage(this.diskConfig.partitions[this.diskConfig.partitions.length - 1]);
    } else if (this.partTableTypeIsMSDOS()) {
      this.ogSweetAlert.warning('opengnsys_warning', 'En MS-DOS sólo puede haber 4 particiones primarias, utilice alguna como extendida si necesita más particiones');
    }
    // Actualizar información
    // setChartData(this.diskConfig);
  }

  addExtendedPartition(partition) {
    partition.partitions.push({
      partition: (partition.partitions.length + 1),
      type: 'NTFS',
      filesystem: '',
      size: 0,
      usage: 0

    });
    const extendedPartUsage = Math.round(100 / partition.partitions.length);
    partition.partitions.forEach(function(extPart) {
      extPart.usage = extendedPartUsage;
    });
    // Actualiza tamaños en funcion del porcentaje de uso
    this.updateExtendedPartitions(partition);
  }

  updateExtendedPartitions(extPartition) {
    const parentPartition = this.diskConfig.partitions.filter((partition) => partition.type === 'EXTENDED')[0];
    const totalSize = parentPartition.size;
    parentPartition.partitions.forEach( function(extPart, index) {
      extPart.partition = (index + 1);
      extPart.size = Math.round((extPart.usage || 0) * totalSize / 100);
    });
  }

  updateExtendedPartitionsUsage(extPartition) {
    const parentPartition = this.diskConfig.partitions.filter((partition) => partition.type === 'EXTENDED')[0];
    const index = parentPartition.partitions.indexOf(extPartition);
    let nextPart = null;
    // si solo hay una partición el uso es siempre el 100%
    if (parentPartition.partitions.length === 1) {
      extPartition.usage = 100;
    } else {
      nextPart = null;
      // el porcentaje que crezca la particion, se le resta a la siguiente o a la anterior si es la ultima
      if (index === parentPartition.partitions.length - 1) {
        nextPart = parentPartition.partitions[index - 1];
      } else {
        nextPart = parentPartition.partitions[index + 1];
      }
      let restPercent = 100;
      parentPartition.partitions.forEach(function(extPart) {
        restPercent -= (extPart.usage || 0);	// Hay casos en los que se obtiene NaN
      });
      // Le quitamos el porcentaje a la particion contigua hasta que quede con un mínimo de 1
      if (nextPart.usage > (restPercent * -1)) {
        nextPart.usage += restPercent;
      } else {
        // restamos 1 al resto del porcentaje que será lo que ocupe la particion contigua
        restPercent = Math.abs(restPercent) - (nextPart.usage - 1);
        nextPart.usage = 1;

        extPartition.usage -= restPercent;
      }
    }
    this.updateExtendedPartitions(extPartition);
  }

  removeExtendedPartition(extPartition) {
    const parentPartition = this.diskConfig.partitions.filter((partition) => partition.type === 'EXTENDED')[0];
    const index = parentPartition.partitions.indexOf(extPartition);
    if (index !== -1) {
      parentPartition.partitions.splice(index, 1);
    }
    // Comprobamos el % que queda libre ahora
    const freePercent = Math.round(extPartition.usage / parentPartition.partitions.length);
    parentPartition.partitions.forEach(function(extPart) {
      extPart.usage += freePercent;
      extPart.size = Math.round(extPart.usage * parentPartition.size / 100);
    });
  }


  reorderPartitions() {
    const self = this;
    const tmpPartitions = [];
    let indexFreeSpace = -1;
    let indexCache = -1;
    this.diskConfig.partitions.forEach(function(partition, index) {
      if (partition.type !== 'free_space' && !self.isCACHE(partition)) {
        partition.partition = (tmpPartitions.length + 1);
        tmpPartitions.push(partition);
      } else if (self.isCACHE(partition)) {
        indexCache = index;
      } else if (partition.type === 'free_space') {
        indexFreeSpace = index;
      }
    });
    // Añadir el espacio libre y la cache
    if (indexFreeSpace !== -1) {
      this.diskConfig.partitions[indexFreeSpace].usage = this.calculateFreeSpace(this.diskConfig.partitions[indexFreeSpace]);
      tmpPartitions.push(this.diskConfig.partitions[indexFreeSpace]);
    }
    if (indexCache !== -1) {
      tmpPartitions.push(this.diskConfig.partitions[indexCache]);
    }
    this.diskConfig.partitions = tmpPartitions;
  }

  setChartData(diskConfig) {
    const self = this;
    const diskChartData = [];
    const diskChartLabels = [];
    const diskPieChartColors = [{
      backgroundColor: []
    }];
    let usedSpace = 0;

    diskConfig.partitions.forEach( function(partition) {
      if (partition.size > 0) {
        self.setPartitionUsage(diskConfig, partition);
        if (partition.type === 'free_space') {
          partition.usage = self.calculateFreeSpace(partition);
        }
        // El espacio libre solo se añade si es 0
        if (partition.type !== 'free_space' || (partition.type === 'free_space' && partition.usage > 0)) {
          diskChartData.push(partition.usage);
          diskChartLabels.push([
            self.translate.instant(partition.os || partition.filesystem || partition.type),
            (partition.usage + '%')
          ]);
          diskPieChartColors[0].backgroundColor.push(self.getPartitionColor(partition));
        }
        if (partition.type !== 'free_space') {
          usedSpace += partition.usage;
        }
      }
    });

    this.diskConfig.remaining = Math.round(100 * (100 - usedSpace)) / 100;

    const diskChartOptions: ChartOptions = {
      responsive: true,
      legend: {
        position: 'bottom'
      },
      plugins: {
        datalabels: {
          formatter: (value, ctx) => {
            const label = ctx.chart.data.labels[ctx.dataIndex];
            return label;
          },
        },
      }
    };

    diskConfig.diskChartData = diskChartData;
    diskConfig.diskChartOptions = diskChartOptions;
    diskConfig.diskChartLabels = diskChartLabels;
    diskConfig.diskPieChartColors = diskPieChartColors;
  }

  getPartitionColor(partition) {
    let color = '#c5e72b';
    // Para la partición de datos se usa un color específico
    if (this.isDATA(partition)) {
      color = 'rgb(237,194,64)';
    } else if (this.isEFI(partition)) {
      color = '#bfe4e5';
    } else if (this.isWINDOWS(partition)) {
      color = '#00c0ef';
    } else if (this.isLINUXSWAP(partition)) {
      color = '#545454';
    } else if (this.isLINUX(partition)) {
      color = '#605ca8';
    } else if (this.isCACHE(partition)) {
      color = '#FC5A5A';
    } else if (this.isFreeSpace(partition)) {
      color = '#bcbcbc';
    }
    return color;
  }

  /*
  * Custom Label formatter
  * ----------------------
  */
  labelFormatter(label, series) {
    return '<div style="font-size:13px; text-align:center; padding:2px; color: #000; font-weight: 600;">'
        + '<br>'
        + series.usage + '%</div>';
  }

  getSizeInGB(size) {
    size = size / (1024 * 1024);
    return Math.round(size * 100) / 100;
  }

  setPartitionUsage(diskConfig, partition) {
    partition.usage = Math.round(((partition.size * 100) / diskConfig.size) * 100) / 100;
  }

  checkPartitionType(partition) {
    let ok = true;
    if (this.isCACHE(partition)) {
      // Comprobar si ya hay alguna partición como CACHE
      if (this.diskConfig.partitions.filter((p) => p.type === 'CACHE').length > 1) {
        this.ogSweetAlert.error('opengnsys_error', 'Solo debe haber una CACHE');
        partition.type = 'NTFS';
        ok = false;
      }
    } else if (this.isEXTENDED(partition)) {
      // Comprobar si ya hay alguna partición como EXTENDIDA
      if (this.diskConfig.partitions.filter((p) => p.type === 'EXTENDED').length > 1) {
        this.ogSweetAlert.error('opengnsys_error', 'Solo debe haber una EXTENDIDA');
        partition.type = 'NTFS';
        ok = false;
      } else {
        partition.partitions = [
          {
            partition: 1,
            type: 'NTFS',
            filesystem: '',
            size: partition.size,
            usage: 100

          }
        ];
      }
    } else if (typeof partition.partitions !== 'undefined' && partition.partitions.length > 0) {
      ok = false;
      const self = this;
      this.ogSweetAlert.question('opengnsys_question', 'Esta particion contiene otras partitiones!, si continua, dichas particiones serán eliminadas....',
          function(yes) {
            partition.partitions = [];
            self.updatePartitionUsage(partition);
          },
          function(cancel) {
            // Si contesta no se deja el tipo extendido
            partition.type = 'EXTENDED';
          }
      );
    }

    if (ok) {
      this.updatePartitionUsage(partition);
    }
  }

  updatePartitionUsage(partition) {
    const remaining =  this.calculateFreeSpace(partition);
    if (partition.usage > remaining) {
      partition.usage = remaining;
    }
    partition.size = Math.round(this.diskConfig.size * partition.usage / 100);
    this.setChartData(this.diskConfig);
    this.reorderPartitions();
    // Si es una partición extendida
    if (typeof partition.partitions !== 'undefined' && partition.partitions.length > 0) {
      this.updateExtendedPartitions(partition.partitions[0]);
    }
  }

  calculateFreeSpace(asignedPartition) {
    let usedSpace = 0;
    this.diskConfig.partitions.forEach(function(partition, index) {
      if (partition !== asignedPartition && partition.type !== 'free_space') {
        usedSpace += partition.usage || 0;
      }
    });
    return  Math.round(100 * (100 - usedSpace)) / 100;
  }

  removePartition(partition) {
    const index = this.diskConfig.partitions.indexOf(partition);
    if (index !== -1) {
      this.diskConfig.partitions.splice(index, 1);
    }

    this.setChartData(this.diskConfig);

  }


// var RC='@';
// document.fdatosejecucion.atributos.value="scp="+escape(document.fdatos.codigo.value)+RC;


  /**/
  generateOgInstruction() {
    const self = this;
    let initPartitionTable = 'ogCreatePartitionTable ' + this.diskConfig.disk + ' ' + this.diskConfig.parttable.type + '\n';
    initPartitionTable += 'ogEcho log session "[0]  $MSG_HELP_ogCreatePartitions"\n';
    initPartitionTable += 'ogEcho session "[10] $MSG_HELP_ogUnmountAll ' + this.diskConfig.disk + '"\n';
    initPartitionTable += 'ogUnmountAll ' + this.diskConfig.disk + ' 2>/dev/null\n';
    initPartitionTable += 'ogUnmountCache\n';
    initPartitionTable += 'ogEcho session "[30] $MSG_HELP_ogUpdatePartitionTable ' + this.diskConfig.disk + '"\n';
    initPartitionTable += 'ogDeletePartitionTable ' + this.diskConfig.disk + '\n';
    initPartitionTable += 'ogUpdatePartitionTable ' + this.diskConfig.disk + '\n';

    let createPartitions = 'ogEcho session "[60] $MSG_HELP_ogListPartitions ' + this.diskConfig.disk + '"\n';
    createPartitions += 'ogExecAndLog command session ogListPartitions ' + this.diskConfig.disk + '\n';

    let cacheInstruction = '';
    let partitionList = '';
    let formatInstructions = '';
    this.diskConfig.partitions.forEach(function(partition, index) {
      if (partition.type !== 'free_space') {
        // La unica particion especial es la 4 que es cache, para el resto
        if (!self.isCACHE(partition)) {
          partitionList += ' ' + partition.type + ':' + partition.size;
          if (self.isEXTENDED(partition)) {
            for (let p = 0; p < partition.partitions.length; p++) {
              partitionList += ' ' + partition.partitions[p].type + ':' + partition.partitions[p].size;
              if (partition.partitions[p].format === true) {
                formatInstructions += 'ogUnmount ' + self.diskConfig.disk + ' ' + (partition.partition + (partition.partitions[p].partition - 1)) + '\n';
                formatInstructions += 'ogFormat ' + self.diskConfig.disk + ' ' + (partition.partition + (partition.partitions[p].partition - 1)) + '\n';
              }
            }
          }
          if (partition.format === true) {
            formatInstructions += 'ogUnmount ' + self.diskConfig.disk + ' ' + partition.partition + '\n';
            formatInstructions += 'ogFormat ' + self.diskConfig.disk + ' ' + partition.partition + '\n';
          }
        } else {
          cacheInstruction = 'ogEcho session "[50] $MSG_HELP_ogCreateCache"\n';
          cacheInstruction += 'initCache ' + self.diskConfig.disk + ' ' + partition.size + ' NOMOUNT &>/dev/null\n';

          if (partition.format === true) {
            formatInstructions += 'ogUnmountCache\n';
            formatInstructions += 'ogFormatCache\n';
          }
        }
      }
    });

    createPartitions += 'ogEcho session "[70] $MSG_HELP_ogCreatePartitions   ' + partitionList + '"\n';
    createPartitions += 'ogExecAndLog command ogCreatePartitions ' + this.diskConfig.disk + partitionList + '\n';
    createPartitions += 'ogEcho session "[80] $MSG_HELP_ogSetPartitionActive ' + this.diskConfig.disk + ' 1"\n';
    createPartitions += 'ogSetPartitionActive ' + this.diskConfig.disk + ' 1\n';
    createPartitions += 'ogEcho log session "[100] $MSG_HELP_ogListPartitions ' + this.diskConfig.disk + '"\n';
    createPartitions += 'ogUpdatePartitionTable ' + this.diskConfig.disk + '\n';
    createPartitions += 'ms-sys /dev/sda | grep unknow && ms-sys /dev/sda\n';
    createPartitions += 'ogExecAndLog command session log ogListPartitions ' + this.diskConfig.disk + '\n';

    this.ogCommandsService.ogInstructions = initPartitionTable + cacheInstruction + createPartitions + formatInstructions;
  }
}
