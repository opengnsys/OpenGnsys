import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OgInformationOptionsComponent } from './og-information-options.component';

describe('OgInformationOptionsComponent', () => {
  let component: OgInformationOptionsComponent;
  let fixture: ComponentFixture<OgInformationOptionsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OgInformationOptionsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OgInformationOptionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
