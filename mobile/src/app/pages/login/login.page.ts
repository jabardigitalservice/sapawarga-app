import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import {
  NavController,
  MenuController,
  ToastController,
  AlertController,
  LoadingController
} from '@ionic/angular';
import { AuthService } from './../../services/auth.service';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss']
})
export class LoginPage implements OnInit {
  public onLoginForm: FormGroup;

  public app_version = environment.VERSION_APP;
  constructor(
    public navCtrl: NavController,
    public menuCtrl: MenuController,
    public toastCtrl: ToastController,
    public alertCtrl: AlertController,
    public loadingCtrl: LoadingController,
    private formBuilder: FormBuilder,
    private auth: AuthService,
    public router: Router
  ) {}

  // show password
  type: string = 'password';
  passwordShown: boolean = false;

  public showPassword() {
    this.passwordShown = !this.passwordShown;

    if (this.passwordShown) {
      this.type = 'text';
    } else {
      this.type = 'password';
    }
  }
  // end of show password

  ionViewWillEnter() {
    this.menuCtrl.enable(false);
  }

  ngOnInit() {
    this.onLoginForm = this.formBuilder.group({
      username: [null, Validators.compose([Validators.required])],
      password: [null, Validators.compose([Validators.required])]
    });

    // console.log(this.auth.isAuthenticated());
  }

  async forgotPass() {
    const alert = await this.alertCtrl.create({
      header: 'Lupa Kata Sandi?',
      message: 'Masukan email Anda untuk mereset kata sandi.',
      inputs: [
        {
          name: 'email',
          type: 'email',
          placeholder: 'Email'
        }
      ],
      buttons: [
        {
          text: 'Batal',
          role: 'batal',
          cssClass: 'secondary',
          handler: () => {
            console.log('Confirm Cancel');
          }
        },
        {
          text: 'Konfirmasi',
          handler: async () => {
            const loader = await this.loadingCtrl.create({
              duration: 2000
            });

            loader.present();
            loader.onWillDismiss().then(async l => {
              const toast = await this.toastCtrl.create({
                showCloseButton: true,
                message: 'Email was sended successfully.',
                duration: 3000,
                position: 'bottom'
              });

              toast.present();
            });
          }
        }
      ]
    });

    await alert.present();
  }

  async login() {
    // console.log('sadsa');
    // this.auth.login('asd');
    await this.auth.login(this.onLoginForm.value).subscribe(
      res => {
        if (res.success === true) {
          // console.log(res.data.access_token);
          this.auth.saveToken(res.data.access_token);
          this.navCtrl.navigateRoot('/home-results');
        } else {
          console.log('login gagal');
        }
      },
      err => {
        console.log(err);
        this.showToast('Login', 'Login gagal');
      }
    );
  }

  // // //
  // goToRegister() {
  //   this.navCtrl.navigateRoot('/register');
  // }

  // goToHome() {
  //   this.navCtrl.navigateRoot('/home-results');
  // }

  async showToast(title: string, msg: string) {
    const toast = await this.toastCtrl.create({
      message: msg,
      duration: 2000
    });
    toast.present();
  }
}