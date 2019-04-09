import {Component, NgZone} from '@angular/core';
import {AuthModule, GlobunetUser} from 'globunet-angular/core';
import {Router} from '@angular/router';


@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: [ './login.component.scss' ]
})
export class LoginComponent {
  private user: GlobunetUser;
  login = {
    username: '',
    password: ''
  };
  // this tells the tabs component which Pages
  // should be each tab's root Page
  constructor(public authModule: AuthModule, private router: Router) {
    this.user = new GlobunetUser();
  }

  goToDashboard() {
    this.router.navigate(['/app/dashboard']).then(
      success => {
        console.log(success);
      },
      error => {
        console.log(error);
      }
    );

  }

  signIn() {
    this.authModule.login(this.login.username, this.login.password, this.user).subscribe(
      data => {
          this.user = data;
          this.goToDashboard();
      }
    );
  }
}
