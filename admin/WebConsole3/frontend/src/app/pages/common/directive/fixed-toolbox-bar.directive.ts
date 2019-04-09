import {Directive, ElementRef, OnInit} from '@angular/core';

let self;

@Directive({
  selector: '[fixed-toolboxbar]'
})
export class FixedToolboxBarDirective implements OnInit {
  private el: ElementRef;
  private titleHeight: number;
  private headerHeight: number;
  private fixClass: string;

  constructor(el: ElementRef) {
    this.el = el;
    self = this;
  }

  ngOnInit(): void {
    this.fixClass = 'fixed';
    const headerClass = this.el.nativeElement.attributes.getNamedItem('main-header') ? this.el.nativeElement.attributes.getNamedItem('main-header').value : 'main-header';
    // Grab as much info as possible
    // outside the scroll handler for performace reasons.
    const header = document.querySelector('.' + headerClass);
    this.headerHeight = 0;
    this.titleHeight = 0;
    if (header) {
      this.headerHeight = Number(window.getComputedStyle(header).height.split('px')[0]);
      this.titleHeight = Number(window.getComputedStyle(this.el.nativeElement).height.split('px')[0]);
      // Scroll handler to toggle classes.
      window.addEventListener('scroll', this.stickyScroll, false);
    }
  }
  stickyScroll(e) {
    if ( window.pageYOffset > (self.headerHeight - self.titleHeight ) / 2 ) {
      self.el.nativeElement.classList.add(self.fixClass);
    }

    if ( window.pageYOffset === 0 || window.pageYOffset < (self.headerHeight - self.titleHeight ) / 2 ) {
      self.el.nativeElement.classList.remove(self.fixClass);
    }
  }

}
