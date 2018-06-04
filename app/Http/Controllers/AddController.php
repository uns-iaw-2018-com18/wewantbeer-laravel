<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cerveceria;
use Session;

class AddController extends Controller {

    public function add(Request $request) {
      $error = $this->chequeos($request);
      if (!($error === NULL)) {
        return redirect('admin/add')->withErrors([$error]);
      }
      $cerveceria = new Cerveceria();
      $cerveceria->id = strtolower(preg_replace("/\s+/", "_", $this->removeAccents($request->nombre)));
      $cerveceria->nombre = $request->nombre;
      $cerveceria->direccion = $request->direccion;
      if (!empty($request->telefono)) {
        $tipoTelefono = $request->tipoTel;
        if ($tipoTelefono == "0") {
          $cerveceria->telefono = "+54291" . $request->telefono;
        } else {
          $cerveceria->telefono = "+549291" . substr($request->telefono, 2, strlen($request->telefono));
        }
      } else {
        $cerveceria->telefono = "";
      }
      if (!empty($request->web)) {
        $cerveceria->web = $request->web;
      } else {
        $cerveceria->web = "";
      }
      if (!empty($request->email)) {
        $cerveceria->email = $request->email;
      } else {
        $cerveceria->email = "";
      }
      if (!empty($request->facebook)) {
        $cerveceria->facebook = $request->facebook;
      } else {
        $cerveceria->facebook = "";
      }
      if (!empty($request->instagram)) {
        $cerveceria->instagram = $request->instagram;
      } else {
        $cerveceria->instagram= "";
      }
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
          if ($response["success"] == true) {
            $link = $response["data"]["link"];
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
          if ($response["success"] == true) {
            $link = $response["data"]["link"];
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
      $cerveceria->horario = $horarios;

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
      //Session::flash('message', 'This is a message!');
      // $request->session()->flash('message', 'Task was successful!');
      return redirect('admin')->with(['mensaje' => 'La cervecería fue creada con éxito']);
    }

    public function chequeos($request){
        //---------------Chequeos del logo
        if(empty($request->logoImg))
          return "Debe haber un logo";
        // Get Image Dimension
        $fileLogo = $request->file("logoImg");
        $fileinfo = @getimagesize($fileLogo);
        $width = $fileinfo[0];
        $height = $fileinfo[1];
        // Get image file extension
        $file_extension = pathinfo($fileLogo->getClientOriginalName(), PATHINFO_EXTENSION);
        // Validate image file extension
        if ($file_extension!="jpg") {
            return "La imagen del logo debe tener la extensión .jpg";
        }
        // Validate image file dimension
         if ($width != "250" || $height != "250") {
          return "La imagen del logo debe tener las dimensiones 250x250";
        }
        //---------------fin de chequeo del logo

        //---------------Chequeos de la foto
        if(empty($request->fotoImg))
          return "Debe haber una foto";
        // Get Image Dimension
        $fileFoto = $request->file("fotoImg");
        $fileinfo = @getimagesize($fileFoto);
        $width = $fileinfo[0];
        $height = $fileinfo[1];
        // Get image file extension
        $file_extension = pathinfo($fileFoto->getClientOriginalName(), PATHINFO_EXTENSION);
        // Validate image file extension
        if ($file_extension!="jpg") {
            return "La imagen de la foto debe tener la extensión .jpg";
        }
        // Validate image file dimension
         if ($width != "520" || $height != "250") {
          return "La imagen de la foto debe tener las dimensiones 520x250 ".$width." ".$height;
        }
        //---------------fin de chequeo de la foto
        if(empty($request->nombre))
          return "El nombre no debe estar vacío";
        $id = strtolower(preg_replace("/\s+/", "_", $request->nombre));
        $cerveceria = Cerveceria::where('id',$id)->get();
        if(isset($cerveceria[0]))
          return "Ya existe una cerveceria con el nombre ".$request->nombre;
        if(empty($request->direccion))
          return "La dirección no debe estar vacía";

        if (isset($request->domCheck)) {
          if(!isset($request->domOpen) || !isset($request->domClose)){
            return "Se deben setear los horarios del día domingo";
          }
          if($request->domOpen==$request->domClose)
            return "El horario de comienzo y fin del domingo no debe ser el mismo";
        }
        if (isset($request->lunCheck)) {
          if(!isset($request->lunOpen) || !isset($request->lunClose)){
            return "Se deben setear los horarios del día lunes";
          }
          if($request->lunOpen==$request->lunClose)
            return "El horario de comienzo y fin del lunes no debe ser el mismo";
        }
        if (isset($request->marCheck)) {
          if(!isset($request->marOpen) || !isset($request->marClose)){
            return "Se deben setear los horarios del día martes";
          }
          if($request->marOpen==$request->marClose)
            return "El horario de comienzo y fin del martes no debe ser el mismo";
        }
        if (isset($request->mieCheck)) {
          if(!isset($request->mieOpen) || !isset($request->mieClose)){
            return "Se deben setear los horarios del día miércoles";
          }
          if($request->mieOpen==$request->mieClose)
            return "El horario de comienzo y fin del Miércoles no debe ser el mismo";
        }
        if (isset($request->jueCheck)) {
          if(!isset($request->jueOpen) || !isset($request->jueClose)){
            return "Se deben setear los horarios del día jueves";
          }
          if($request->jueOpen==$request->jueClose)
            return "El horario de comienzo y fin del jueves no debe ser el mismo";
        }
        if (isset($request->vieCheck)) {
          if(!isset($request->vieOpen) || !isset($request->vieClose)){
            return "Se deben setear los horarios del día viernes";
          }
          if($request->vieOpen==$request->vieClose)
            return "El horario de comienzo y fin del viernes no debe ser el mismo";
        }
        if (isset($request->sabCheck)) {
          if(!isset($request->sabOpen) || !isset($request->sabClose)){
            return "Se deben setear los horarios del día sábado";
          }
          if($request->sabOpen==$request->sabClose)
            return "El horario de comienzo y fin del sábado no debe ser el mismo";
        }
        if (isset($request->happyCheck)) {
          if(!isset($request->happyOpen) || !isset($request->happyClose)){
            return "Se deben setear los horarios del happy hour";
          }
          if($request->happyOpen==$request->happyClose)
            return "El horario de comienzo y fin del happy hour no debe ser el mismo";
        }
        return null;
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
