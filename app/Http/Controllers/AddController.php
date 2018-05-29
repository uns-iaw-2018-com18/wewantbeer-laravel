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
      if (isset($request->happyCheck)) {
        $cerveceria->happyHour = $request->happyOpen."-".$request->happyClose;
      }
      $horarios = array();
      //ahora para cada uno de los dias tengo que agregarlo al arrelgo
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

      // Get lat and long by address
      $address = $request->direccion.", B8000 Bahía Blanca, Buenos Aires"; // Google HQ
      $prepAddr = preg_replace("/\s+/", "+", $address);
      $url = "https://maps.google.com/maps/api/geocode/json?address=".$prepAddr;
      $geocode = file_get_contents($url);
      $output = json_decode($geocode);
      if (isset($output->status) && ($output->status == 'OK')) {
        $latitude = $output->results[0]->geometry->location->lat;
        $longitude = $output->results[0]->geometry->location->lng;
        $cerveceria->latLong = array($latitude,$longitude);
      } else {
       //deberia tirar un error por dirección invalida?
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
