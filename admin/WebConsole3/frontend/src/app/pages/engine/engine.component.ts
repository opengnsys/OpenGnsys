import { Component } from '@angular/core';

import { EngineService } from 'src/app/api/engine.service';
import { Engine } from 'src/app/model/engine';

@Component({
  selector: 'engine',
  templateUrl: './engine.component.html',
  styleUrls: [ './engine.component.scss' ]
})
export class EngineComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public engineService: EngineService) {
  }
  
}
