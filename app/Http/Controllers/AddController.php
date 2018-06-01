<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cerveceria;

class AddController extends Controller {
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return Response
     */
    public function add(Request $request) {
      // Falta chequear si los campos estan vacios
      // En ese caso no hay que agregarlos

      $cerveceria = new Cerveceria();
      $cerveceria->id = strtolower(preg_replace("/\s+/", "_", $request->nombre));
      $cerveceria->nombre = $request->nombre;
      $cerveceria->direccion = $request->direccion;
      $tipoTelefono = $request->tipoTel;
      if ($tipoTelefono == "0") {
        $cerveceria->telefono = "+54291".$request->telefono;
      } else {
        $cerveceria->telefono = "+549291".$request->telefono;
      }
      $cerveceria->web = $request->web;
      $cerveceria->email = $request->email;
      $cerveceria->facebook = $request->facebook;
      $cerveceria->instagram = $request->instagram;

      // Obtener access_token a partir del refresh_token
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, "https://api.imgur.com/oauth2/token");
      curl_setopt($curl, CURLOPT_TIMEOUT, 30);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array("content-type: multipart/form-data"));
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, array("refresh_token" => "a5432f4ac13a7a4209710b053fcfa9cf390b43b5", "client_id" => "7e1680d3fd3269b", "client_secret" => "c028aa4c4dfc52b37dd72cb31ad2d4d9b03a9356", "grant_type" => "refresh_token"));
      $out = curl_exec($curl);
      curl_close($curl);
      $response = json_decode($out, true);
      $accessToken = $response["access_token"];
      if ($accessToken != "") {
        // Subir logo
        if ($request->hasFile("logoImg")) {
          $file = $request->file("logoImg");
          $name = $file->getClientOriginalName();
          $data = file_get_contents($file);
          $albumId = "Exld0Nt";
          $curl = curl_init();
          curl_setopt($curl, CURLOPT_URL, "https://api.imgur.com/3/image");
          curl_setopt($curl, CURLOPT_TIMEOUT, 30);
          curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
          curl_setopt($curl, CURLOPT_POST, 1);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($curl, CURLOPT_POSTFIELDS, array("image" => $data, "album" => $albumId, "type" => "file", "name" => $name, "title" => "Logo " . $request->nombre));
          $out = curl_exec($curl);
          curl_close($curl);
          $response = json_decode($out, true);
          $link = $response["data"]["link"];
          if ($link != "") {
            $cerveceria->logo = $link;
          } else {
            // Mostrar mensaje de error
          }
        }
        // Subir foto
        if ($request->hasFile("fotoImg")) {
          $file = $request->file("fotoImg");
          $name = $file->getClientOriginalName();
          $data = file_get_contents($file);
          $albumId = "PVgpgu9";
          $curl = curl_init();
          curl_setopt($curl, CURLOPT_URL, "https://api.imgur.com/3/image");
          curl_setopt($curl, CURLOPT_TIMEOUT, 30);
          curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
          curl_setopt($curl, CURLOPT_POST, 1);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($curl, CURLOPT_POSTFIELDS, array("image" => $data, "album" => $albumId, "type" => "file", "name" => $name, "title" => "Foto " . $request->nombre));
          $out = curl_exec($curl);
          curl_close($curl);
          $response = json_decode($out, true);
          $link = $response["data"]["link"];
          if ($link != "") {
            $cerveceria->foto = $link;
          } else {
            // Mostrar mensaje de error
          }
        }
      } else {
        // Mostrar mensaje de error
      }

      if (isset($request->happyCheck)) {
        $cerveceria->happyHour = $request->happyOpen."-".$request->happyClose;
      }
      $horarios = array();
      // Ahora para cada uno de los dias tengo que agregarlo al arreglo
      if (isset($request->domCheck)) {
        $horarios[0] = $request->domOpen."-".$request->domClose;
      } else {
        $horarios[0] = "Cerrado";
      }
      if (isset($request->lunCheck)) {
        $horarios[1] = $request->lunOpen."-".$request->lunClose;
      } else {
        $horarios[1] = "Cerrado";
      }
      if (isset($request->marCheck)) {
        $horarios[2] = $request->marOpen."-".$request->marClose;
      } else {
        $horarios[2] = "Cerrado";
      }
      if (isset($request->mieCheck)) {
        $horarios[3] = $request->mieOpen."-".$request->mieClose;
      } else {
        $horarios[3] = "Cerrado";
      }
      if (isset($request->jueCheck)) {
        $horarios[4] = $request->jueOpen."-".$request->jueClose;
      } else {
        $horarios[4] = "Cerrado";
      }
      if (isset($request->vieCheck)) {
        $horarios[5] = $request->vieOpen."-".$request->vieClose;
      } else {
        $horarios[5] = "Cerrado";
      }
      if (isset($request->sabCheck)) {
        $horarios[6] = $request->sabOpen."-".$request->sabClose;
      } else {
        $horarios[6] = "Cerrado";
      }
      $cerveceria->horario=$horarios;

      // Obtener lag/long con cURL
      $address = $request->direccion . ", B8000 Bahía Blanca, Buenos Aires";
      $addr = preg_replace("/\s+/", "+", $address);
      $url = "https://maps.google.com/maps/api/geocode/json?address=" . $addr;
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_TIMEOUT, 30);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      $out = curl_exec($curl);
      curl_close($curl);
      $response = json_decode($out, true);
      if (isset($response["status"]) && ($response["status"] == "OK")) {
        $lat = $response["results"][0]["geometry"]["location"]["lat"];
        $long = $response["results"][0]["geometry"]["location"]["lng"];
        $cerveceria->latLong = array($lat, $long);
      } else {
        // Mostrar mensaje de error
      }

      $cerveceria->save();
      return redirect('admin');
    }

    /**
    * Instantiate a new controller instance.
    *
    * @return void
    */
   public function __construct() {
       $this->middleware('auth');
   }
}
