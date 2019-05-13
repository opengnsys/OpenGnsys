import {Component, OnInit} from '@angular/core';
import {AuthModule} from 'globunet-angular/core';
import {Router} from '@angular/router';
import {TraceService} from '../../api/trace.service';
import {User} from '../../model/user';
import {Trace} from '../../model/trace';
import {QueryOptions} from 'globunet-angular/core/providers/api/query-options';
import {OGCommandsService} from '../../service/og-commands.service';
import {OgCommonService} from '../../service/og-common.service';
import {OgSweetAlertService} from '../../service/og-sweet-alert.service';
import {ToasterService} from '../../service/toaster.service';
import {TranslateService} from '@ngx-translate/core';

@Component({
  selector: 'app-header-inner',
  templateUrl: './header-inner.component.html'
})
export class HeaderInnerComponent implements  OnInit {
    public user: User;
    public executionTasks: Trace[];
    public constants: any;


    constructor(public ogCommonService: OgCommonService, public ogCommandsService: OGCommandsService, private router: Router, private authModule: AuthModule, private traceService: TraceService, private ogSweetAlert: OgSweetAlertService, private toaster: ToasterService, private translate: TranslateService) {
        this.user = this.authModule.getLoggedUser();
        this.executionTasks = [];
        this.constants = {};
        this.ogCommonService.loadEngineConfig().subscribe(
            data => {
                this.constants = data.constants;
            }
        );
    }

    ngOnInit(): void {
        this.getExectutionTasks();
    }

    getExectutionTasks(){
        this.traceService.list(new QueryOptions({limit: 5, finished: 0})).subscribe(
            data => {
                this.executionTasks = data;
            }
        );
    }

    logout() {
        this.authModule.logout();
        this.router.navigate(['/login']);
    }

    deleteExecutionTace(trace) {
        const self = this;
        this.ogSweetAlert.question(
            this.translate.instant('delete_task'),
            this.translate.instant('sure_to_delete_task') + '?',
            function(result) {
                if (result) {
                    this.traceService.delete(trace.id).then(
                        function(response) {
                            self.toaster.pop({type: 'success', title: 'success', body: this.translate.instant('successfully_deleted')});
                            self.getExectutionTasks();
                        },
                        function(error) {
                            self.toaster.pop({type: 'error', title: 'error', body: error});
                        }
                    );

                }
            }
        );

    }

    relaunchExecutionTask(task) {
        this.ogSweetAlert.question(
            this.translate.instant('relaunch_task'),
            this.translate.instant('sure_to_relaunch_task') + '?',
            function(result) {
                if (result) {
                    // TODO - Relanzar la tarea especificada
                }
            }
        );
    }
}
