<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cerveceria;

class EditController extends Controller {

  public function getEdit($id) {
    $cerveceria = Cerveceria::where('id', $id)->get()[0];
    if (isset($cerveceria)) {
      if ($cerveceria['telefono'] != "") {
        $num = substr($cerveceria['telefono'], 3, 1);
        if ($num == 9) {
          $telefono = "15" . substr($cerveceria['telefono'], 7, strlen($cerveceria['telefono']));
          $tipo = 1;
        } else {
          $telefono = substr($cerveceria['telefono'], 6, strlen($cerveceria['telefono']));
          $tipo = 0;
        }
        return view('edit')->with(['cerveceria' => $cerveceria, 'telefono' => $telefono, 'tipo' => $tipo]);
      } else {
        return view('edit')->with(['cerveceria' => $cerveceria]);
      }
    } else {
      return redirect('admin');
    }
  }

  public function __construct() {
    $this->middleware('auth');
  }
}
