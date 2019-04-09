import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OgSelectedClientsComponent } from './og-selected-clients.component';

describe('OgSelectedClientsComponent', () => {
  let component: OgSelectedClientsComponent;
  let fixture: ComponentFixture<OgSelectedClientsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OgSelectedClientsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OgSelectedClientsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
