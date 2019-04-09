import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OgExecuteCommandOptionsComponent } from './og-execute-command-options.component';

describe('OgExecuteCommandOptionsComponent', () => {
  let component: OgExecuteCommandOptionsComponent;
  let fixture: ComponentFixture<OgExecuteCommandOptionsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OgExecuteCommandOptionsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OgExecuteCommandOptionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
