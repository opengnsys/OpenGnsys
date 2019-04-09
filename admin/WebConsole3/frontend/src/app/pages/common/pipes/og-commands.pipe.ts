import {Pipe, PipeTransform} from '@angular/core';

@Pipe({
  name: 'ogCommands'
})
export class OgCommandsPipe implements PipeTransform {
  private ogCommands = [
    'ogEcho',
    'ogGetCacheSize',
    'ogCreatePartitionTable',
    'ogUnmountAll',
    'ogUnmountCache',
    'ogUnmount',
    'ogFormatCache',
    'ogFormat',
    'ogDeletePartitionTable',
    'ogExecAndLog',
    'ogUpdatePartitionTable',
    'initCache',
    'ogListPartitions',
    'ogCreatePartitions',
    'ogSetPartitionActive',
    'deployImage',
    'updateCache'
  ];

  transform(input) {
    let out = input;
    if (typeof out === 'string') {
      // Sustituimos en el input cualquier comando de ogCommands por su versión con etiquetas html span class="og-command"
      for (let i = 0; i < this.ogCommands.length; i++) {
        out = out.replace(new RegExp('\\b' + this.ogCommands[i], 'g'), '<span class=\'og-command\'>' + this.ogCommands[i] + '</span>');
      }
      out = out.replace(new RegExp('\n', 'g'), '<br>');
      // Todo lo que esté entre comillas dobles se considera string y se muestra entre span class="og-string"
      out = out.replace(new RegExp('"(.*?)"', 'g'), '<span class=\'og-string\'>"$1"</span>');
    }

    return out;
  }

}
