<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

class User extends Authenticatable {
  use Notifiable;

  protected $collection = 'admins';

  protected $fillable = ['username', 'password'];

  protected $hidden = ['password', 'remember_token'];

}
