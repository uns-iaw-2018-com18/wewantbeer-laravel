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
    $cerveceria = Cerveceria::where('id', $id)->first();
    if (isset($cerveceria)) {
      $accessToken = env('IMGUR_ACCESS_TOKEN');
      // Eliminar logo anterior
      $imageHash = str_replace("https://i.imgur.com/", "", $cerveceria->logo);
      $imageHash = explode(".", $imageHash)[0];
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, "https://api.imgur.com/3/image/" . $imageHash);
      curl_setopt($curl, CURLOPT_TIMEOUT, 30);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      $out = curl_exec($curl);
      curl_close($curl);
      $response = json_decode($out, true);
      if ($response["success"] != true) {
        // Intentar nuevamente
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.imgur.com/3/image/" . $imageHash);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_exec($curl);
        curl_close($curl);
      }
      // Eliminar foto anterior
      $imageHash = str_replace("https://i.imgur.com/", "", $cerveceria->foto);
      $imageHash = explode(".", $imageHash)[0];
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, "https://api.imgur.com/3/image/" . $imageHash);
      curl_setopt($curl, CURLOPT_TIMEOUT, 30);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      $out = curl_exec($curl);
      curl_close($curl);
      $response = json_decode($out, true);
      if ($response["success"] != true) {
        // Intentar nuevamente
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.imgur.com/3/image/" . $imageHash);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_exec($curl);
        curl_close($curl);
      }

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
