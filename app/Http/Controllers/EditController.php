<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cerveceria;

class EditController extends Controller {

  public function selectEdit() {
    $cervecerias = Cerveceria::all('id', 'nombre')->makeHidden('_id');
    $cervecerias = json_decode($cervecerias, true);
    usort($cervecerias, function($a, $b) {
      return strcmp($a['nombre'], $b['nombre']);
    });
    return view('select')->with(['cervecerias' => $cervecerias, 'opcion' => 'edit']);
  }

  public function getEdit(String $id) {
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

  public function edit(Request $request, String $id) {
    $cerveceria = Cerveceria::where('id', $id)->get()[0];
    if (isset($cerveceria)) {
      $cerveceria->id = strtolower(preg_replace("/\s+/", "_", $this->removeAccents($request->nombre)));
      $cerveceria->nombre = $request->nombre;
      $cerveceria->direccion = $request->direccion;

      // Agregar los otros campos

      $cerveceria->save();
      return redirect('admin')->with(['mensaje' => 'La cervecería fue actualizada con éxito']);
    } else {
      return redirect('admin');
    }
  }

  public function removeAccents($str) {
    $accents = array( 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
                      'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                      'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
                      'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
                      'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U',
                      'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
                      'Ñ' => 'N', 'ñ' => 'n', 'Ç' => 'C', 'ç' => 'c' );

    return strtr($str, $accents);
  }

  public function __construct() {
    $this->middleware('auth');
  }
}
