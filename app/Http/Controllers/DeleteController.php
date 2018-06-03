<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cerveceria;

class DeleteController extends Controller {

  public function delete(String $id) {
    $cerveceria = Cerveceria::where('id', $id)->get()[0];
    if (isset($cerveceria)) {
      // Eliminar imagenes

      $cerveceria->delete();
      return redirect('admin');
    } else {
      return redirect('admin');
    }
  }

  public function __construct() {
    $this->middleware('auth');
  }
}
