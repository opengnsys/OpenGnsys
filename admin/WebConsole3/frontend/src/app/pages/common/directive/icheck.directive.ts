import {Component, Directive, ElementRef, OnInit} from '@angular/core';


@Directive({
  selector: '[icheck]'
})
export class IcheckDirective implements OnInit {
  private el: ElementRef;

  constructor(el: ElementRef) {
    this.el = el;
  }

  ngOnInit(): void {
    const checkboxClass = this.el.nativeElement.getAttribute('checkbox-class') || 'icheckbox_square-aero';
    const radioClass = this.el.nativeElement.getAttribute('radio-class') || 'iradio_square-aero';
    const parent = this.el.nativeElement.parentElement;
    const container = document.createElement('div');
    container.classList.add('icheck');
    parent.removeChild(this.el.nativeElement);
    container.appendChild(this.el.nativeElement);
    const span = document.createElement('span');
    span.classList.add('checkmark');
    const self = this;
    span.onclick = function() {
      self.el.nativeElement.click();
    };
    container.appendChild(span);
    parent.appendChild(container);
  }
}
