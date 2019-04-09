import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {ViewCell} from 'ng2-smart-table';

@Component({
  selector: 'app-table-action',
  templateUrl: './ng2-table-action.component.html',
  styleUrls: ['./ng2-table-action.component.css']
})
export class Ng2TableActionComponent implements ViewCell, OnInit {
  renderValue: string;
  _options = {
    override: false,
    buttons: [
      {
        action: 'edit',
        label: 'edit',
        handler: (rowData) => this.onEdit(rowData),
        classes: 'btn-default'
      },
      {
        action: 'delete',
        label: 'delete',
        handler: (rowData) => this.onDelete(rowData),
        classes: 'btn-danger'
      }
    ]
  };

  @Input() value: string | number;
  @Input() rowData: any;
  @Input()
  set options(options) {
    // @ts-ignore
    if (!options.override || options.override == false) {
      if (options.buttons) {
        for (let index = 0; index < options.buttons.length; index++) {
          let op = options.buttons[index];
          if (op.action === 'edit') {
            options.buttons[index] = Object.assign(op, this._options.buttons[0]);
          } else if (op.action && op.action === 'delete') {
            options.buttons[index] = Object.assign(op, this._options.buttons[1]);
          }
        }
      }
    }
    this._options = options;
  }
  get options() {
    return this._options;
  }

  @Output() edit: EventEmitter<any> = new EventEmitter();
  @Output() delete: EventEmitter<any> = new EventEmitter();

  ngOnInit() {
    this.value = this.value || '';
    this.renderValue = this.value.toString().toUpperCase();
  }

  onEdit(rowData) {
    this.edit.emit(rowData);
  }

  onDelete(rowData) {
    this.delete.emit(rowData);
  }
}
