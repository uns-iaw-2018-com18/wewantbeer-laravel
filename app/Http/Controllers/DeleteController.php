<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cerveceria;

class DeleteController extends Controller {

  public function selectDelete() {
    $cervecerias = Cerveceria::all('id', 'nombre')->makeHidden('_id');
    $cervecerias = json_decode($cervecerias, true);
    usort($cervecerias, function($a, $b) {
      return strcmp($a['nombre'], $b['nombre']);
    });
    return view('select')->with(['cervecerias' => $cervecerias, 'opcion' => 'delete']);
  }

  public function delete(String $id) {
    $cerveceria = Cerveceria::where('id', $id)->get()[0];
    if (isset($cerveceria)) {
      // Eliminar imagenes

      $cerveceria->delete();
      return redirect('admin')->with(['mensaje' => 'La cervecería fue eliminada con éxito']);
    } else {
      return redirect('admin')->withErrors(["Error al intentar eliminar, por favor intente más tarde"]);
    }
  }

  public function __construct() {
    $this->middleware('auth');
  }
}
