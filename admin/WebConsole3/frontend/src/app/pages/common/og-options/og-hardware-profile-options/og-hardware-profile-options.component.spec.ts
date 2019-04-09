import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OgHardwareProfileOptionsComponent } from './og-hardware-profile-options.component';

describe('OgHardwareProfileOptionsComponent', () => {
  let component: OgHardwareProfileOptionsComponent;
  let fixture: ComponentFixture<OgHardwareProfileOptionsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OgHardwareProfileOptionsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OgHardwareProfileOptionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
