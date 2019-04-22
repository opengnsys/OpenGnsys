import { Component } from '@angular/core';

import { UserService } from 'src/app/api/user.service';
import { User } from 'src/app/model/user';

@Component({
  selector: 'app-user',
  templateUrl: './user.component.html',
  styleUrls: [ './user.component.scss' ]
})
export class UserComponent {
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public userService: UserService) {
  }
  
}
