import {Component, Input} from '@angular/core';

@Component({
  selector: 'app-form-input',
  templateUrl: 'form-input.component.html'
})
export class FormInputComponent {
  private _formType: any;
  private _cols: number;

  @Input() model;

  @Input()
  set cols(cols) {
    this._cols = (typeof cols !== 'undefined') ? (12 / cols) : 6;
  }
  get cols() {
    return this._cols;
  }
  @Input()
  set formType(formType) {
    if (typeof formType.rows === 'undefined') {
      formType.rows = [formType];
    }
    this._formType = formType;
  }
  get formType() {
    return this._formType;
  }


  getCol(rowNumber) {
    const elems: number = this.formType.rows[rowNumber].length;
    const nCol = ((this._cols) ? this._cols : Math.ceil(12 / elems));

    return  'col-' + nCol + ' col-md-' + nCol;
  }

  getValue(field: any, option: any) {
    let result = option;
    if (field.value) {
      result = option[field.value];
    }
    return result;
  }
}
