import {Component, EventEmitter, Input, Output} from '@angular/core';
import {OrganizationalUnit} from '../../../model/organizational-unit';
import {Client} from '../../../model/client';
import {forkJoin, Observable} from 'rxjs';
import {OrganizationalUnitService} from '../../../api/organizational-unit.service';
import {OgSweetAlertService} from '../../../service/og-sweet-alert.service';
import {ToasterService} from '../../../service/toaster.service';
import {ClientService} from '../../../api/client.service';
import {TranslateService} from '@ngx-translate/core';
import {OgCommonService} from '../../../service/og-common.service';

@Component({
  selector: 'app-ou-group-component',
  templateUrl: 'ou-group.component.html',
  styleUrls: ['ou-group.component.css']
})
export class OuGroupComponent {
  @Input() ous;
  @Input() content;
  @Input() clientStatus;
  @Input() showGrid;
  @Input() selectedStatus;

  public config: any;

  public moveChildren = false;

  constructor(public ogCommonService: OgCommonService,
              private organizationalUnitService: OrganizationalUnitService,
              private clientService: ClientService,
              private ogSweetAlert: OgSweetAlertService,
              private toaster: ToasterService,
              private translate: TranslateService) {
  }

  doMove(ou) {
    // Comprobar si hay que mover clientes o una ou a la ou pasada por parametro
    if (this.ogCommonService.movingOu != null) {
      /**/
      const id = ou ? ou.id : null;
      const obj = new OrganizationalUnit();
      // @ts-ignore
      obj.id = this.ogCommonService.movingOu.id;
      // @ts-ignore
      obj.parent = id;
      this.organizationalUnitService.update(obj).subscribe(
        (response) => {
          this.toaster.pop({type: 'success', title: 'success', body: 'Successfully moved'});
          this.deleteOuFromModel(this.ous, this.ogCommonService.movingOu);
          if (ou) {
            ou.children.push(this.ogCommonService.movingOu);
          } else {
            this.ous.push(this.ogCommonService.movingOu);
          }
          this.ogCommonService.movingOu = null;
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
        }
      );
      /**/

    } else if (this.ogCommonService.movingClients === true) {
      const clientIds = Object.keys(this.ogCommonService.selectedClients);
      let cId = '';
      const promises = [];
      for (let i = 0; i < clientIds.length; i++) {
        cId = clientIds[i];
        const client = new Client();
        // @ts-ignore
        client.id = cId;
        client.organizationalUnit = ou.id;
        promises.push(this.clientService.update(client));
      }
      forkJoin(promises).subscribe(
        (response)  => {
          for (let i = 0; i < clientIds.length; i++) {
            cId = clientIds[i];
            this.deleteClientFromOu(this.ous, this.ogCommonService.selectedClients[cId]);
            ou.clients.push(this.ogCommonService.selectedClients[cId]);
            this.ogCommonService.selectClient(this.ogCommonService.selectedClients[cId], ou);
          }
          this.toaster.pop({type: 'success', title: 'success', body: 'Successfully moved'});
          this.ogCommonService.movingClients = false;
        },
        (error) => {
          this.toaster.pop({type: 'error', title: 'error', body: error});
          this.ogCommonService.movingClients = false;
        }
      );
    }
  }

  deleteOuFromModel(ous, ou) {
    let found = false;
    const nOus = ous.length;
    let index = 0;
    while (!found && index < nOus) {
      if (ous[index] === ou) {
        found = true;
        ous.splice(index, 1);
      } else if (ous[index].children.length > 0) {
        found = this.deleteOuFromModel(ous[index].children, ou);
      }
      index++;
    }
    return found;
  }

  deleteClientFromOu(ous, client) {
    let found = false;
    const nOus = ous.length;
    let index = 0;
    while (!found && index < nOus) {
      if (ous[index].id === client.parent.id) {
        found = true;
        const cIndex = ous[index].clients.indexOf(client);
        if (cIndex !== -1) {
          ous[index].clients.splice(cIndex, 1);
        }
      } else if (ous[index].children.length > 0) {
        found = this.deleteClientFromOu(ous[index].children, client);
      }
      index++;
    }
    return found;
  }

  selectForMove(content) {
    this.ogCommonService.selectForMove(content);
  }

  selectOu(ou) {
    // seleccionar/deseleccionar todos los elementos dentro de ou
    for (let i = 0; i < ou.children.length; i++){
      ou.children[i].selected = ou.selected;
      this.selectOu(ou.children[i]);
    }
    for (let i = 0; i < ou.clients.length; i++) {
      ou.clients[i].selected = ou.selected;
      this.ogCommonService.selectClient(ou.clients[i], ou);
    }
  }


  deleteOu(ou) {
    this.ogSweetAlert.swal(
      {
        title: this.translate.instant('sure_to_delete') + '?',
        input: 'checkbox',
        inputValue: 1,
        inputPlaceholder:
          this.translate.instant('move_children_to_parent'),
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: this.translate.instant('yes_delete')
      }).then(
      function(answer) {
        if (!answer.dismiss) {
          const promises = [];

          new Observable((observer) => {
            // Se eligió mover los clientes
            if (answer.value === 1) {
              // Obtener la ou para saber el id de su padre
              this.organizationalUnitService.read(ou.id + '?parent= 1').subscribe(
                (response) => {
                  // Mover todos los hijos al nivel superior de la ou actual
                  const parentId = response.parent.id;
                  for (let i = 0; i < ou.children.length; i++) {
                    ou.children[i].parent = parentId;
                    promises.push(this.organizationalUnitService.update({id: ou.children[i].id, parent: parentId}));
                  }
                  ou.clients = ou.clients || [];
                  for (let i = 0; i < ou.clients.length; i++) {
                    ou.clients[i].organizationalUnit = parentId;
                    promises.push(this.clientService.update({id: ou.clients[i].id, organizationalUnit: parentId}));
                  }
                  forkJoin(promises).subscribe(
                    (success) => {
                      observer.next(true);
                    },
                    (error) => {
                      observer.error(error);
                    }
                  );
                },
                (error) => {
                  observer.error(error);
                }
              );
            } else {
              observer.next(true);
            }
          }).subscribe(
            (response) => {
              this.organizationalUnitService.delete(ou.id).subscribe(
                (success) => {
                  // Si la unidad organizativa es un nivel superior, se recarga la lista y se borra la unidad organizativa del usuario
                  if (ou.parent == null) {
                    delete this.config.ous;
                    this.user.ou = null;
                  }

                  this.toaster.pop({type: 'success', title: 'success', body: 'Successfully deleted'});
                  window.setTimeout(function() {
                    this.router.navigate(['app.ous']);
                  }, 500);
                },
                (error) => {
                  this.toaster.pop({type: 'error', title: 'error', body: error});
                }
              );
            },
            (error) => {
              this.toaster.pop({type: 'error', title: 'error', body: error});
            }
          );
        }
      },
      function(cancel) {
      }
    );

  }
}
