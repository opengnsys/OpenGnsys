import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TableActionComponent } from './ng2-table-action.component';

describe('TableActionComponent', () => {
  let component: TableActionComponent;
  let fixture: ComponentFixture<TableActionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TableActionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TableActionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
