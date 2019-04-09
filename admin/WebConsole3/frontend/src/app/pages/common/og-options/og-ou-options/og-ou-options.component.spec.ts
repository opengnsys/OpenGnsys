import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OgOuOptionsComponent } from './og-ou-options.component';

describe('OgOuOptionsComponent', () => {
  let component: OgOuOptionsComponent;
  let fixture: ComponentFixture<OgOuOptionsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OgOuOptionsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OgOuOptionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
