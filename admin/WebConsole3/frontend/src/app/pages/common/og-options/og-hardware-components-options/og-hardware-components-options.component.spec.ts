import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OgHardwareComponentsOptionsComponent } from './og-hardware-components-options.component';

describe('OgHardwareComponentsOptionsComponent', () => {
  let component: OgHardwareComponentsOptionsComponent;
  let fixture: ComponentFixture<OgHardwareComponentsOptionsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OgHardwareComponentsOptionsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OgHardwareComponentsOptionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
