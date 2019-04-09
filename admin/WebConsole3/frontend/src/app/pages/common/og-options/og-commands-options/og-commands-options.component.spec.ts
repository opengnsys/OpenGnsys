import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OgCommandsOptionsComponent } from './og-commands-options.component';

describe('OgCommandsOptionsComponent', () => {
  let component: OgCommandsOptionsComponent;
  let fixture: ComponentFixture<OgCommandsOptionsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OgCommandsOptionsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OgCommandsOptionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
