import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OgHardwareTypesOptionsComponent } from './og-hardware-types-options.component';

describe('OgHardwareTypesOptionsComponent', () => {
  let component: OgHardwareTypesOptionsComponent;
  let fixture: ComponentFixture<OgHardwareTypesOptionsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OgHardwareTypesOptionsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OgHardwareTypesOptionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
