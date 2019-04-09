import { Component } from '@angular/core';

import { MenuService } from 'src/app/api/menu.service';
import { Menu } from 'src/app/model/menu';

@Component({
  selector: 'menu',
  templateUrl: './menu.component.html',
  styleUrls: [ './menu.component.scss' ]
})
export class MenuComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public menuService: MenuService) {
  }
  
}
