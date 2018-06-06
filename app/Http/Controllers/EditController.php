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
    } else {// No se encontró la cerveceria en el servidor, por lo tanto hubo un error
      return redirect('admin')->withErrors(["Error al intentar editar, por favor intente más tarde"]);
    }
  }

  public function edit(Request $request, String $id) {
    $cerveceria = Cerveceria::where('id', $id)->get()[0];
    if(!isset($cerveceria)){
      // Mostrar mensaje de error si es que no se pudo obtener el accessToken
      if ($cerveceria['telefono'] != "") {
        $num = substr($cerveceria['telefono'], 3, 1);
        if ($num == 9) {
          $telefono = "15" . substr($cerveceria['telefono'], 7, strlen($cerveceria['telefono']));
          $tipo = 1;
        } else {
          $telefono = substr($cerveceria['telefono'], 6, strlen($cerveceria['telefono']));
          $tipo = 0;
        }
        return view('edit')->with(['cerveceria' => $cerveceria, 'telefono' => $telefono, 'tipo' => $tipo, 'errors'=>["Error al intentar editar, intente nuevamente"]]);
      } else {
        return view('edit')->with(['cerveceria' => $cerveceria, 'errors'=>["Error al intentar editar, intente nuevamente"]]);
      }
    }
    $error = $this->chequeos($request,$id);
    if (!($error === NULL)) {
      if ($cerveceria['telefono'] != "") {
        $num = substr($cerveceria['telefono'], 3, 1);
        if ($num == 9) {
          $telefono = "15" . substr($cerveceria['telefono'], 7, strlen($cerveceria['telefono']));
          $tipo = 1;
        } else {
          $telefono = substr($cerveceria['telefono'], 6, strlen($cerveceria['telefono']));
          $tipo = 0;
        }
        return view('edit')->with(['cerveceria' => $cerveceria, 'telefono' => $telefono, 'tipo' => $tipo, 'errors'=>[$error]]);
      } else {
        return view('edit')->with(['cerveceria' => $cerveceria, 'errors'=>[$error]]);
      }
    }
    //Ya chequié si la cerveceria está seteada arriba
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

    $accessToken="";
    if(!empty($request->logoImg) || !empty($request->fotoImg)){
      //Como solo quiero computar el refresh token una vez, entonces lo obtengo si es que alguno de los dos lo necesita
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

      if($accessToken===""){
        // Mostrar mensaje de error si es que no se pudo obtener el accessToken
        if ($cerveceria['telefono'] != "") {
          $num = substr($cerveceria['telefono'], 3, 1);
          if ($num == 9) {
            $telefono = "15" . substr($cerveceria['telefono'], 7, strlen($cerveceria['telefono']));
            $tipo = 1;
          } else {
            $telefono = substr($cerveceria['telefono'], 6, strlen($cerveceria['telefono']));
            $tipo = 0;
          }
          return view('edit')->with(['cerveceria' => $cerveceria, 'telefono' => $telefono, 'tipo' => $tipo, 'errors'=>["Error al intentar cargar la imagen, intente nuevamente"]]);
        } else {
          return view('edit')->with(['cerveceria' => $cerveceria, 'errors'=>["Error al intentar cargar la imagen, intente nuevamente"]]);
        }
      }
    }


/*
    if($request->hasFile("logoImg"){
      //Eliminar anterior logo

      //Agregar el nuevo logo

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
        if ($cerveceria['telefono'] != "") {
          $num = substr($cerveceria['telefono'], 3, 1);
          if ($num == 9) {
            $telefono = "15" . substr($cerveceria['telefono'], 7, strlen($cerveceria['telefono']));
            $tipo = 1;
          } else {
            $telefono = substr($cerveceria['telefono'], 6, strlen($cerveceria['telefono']));
            $tipo = 0;
          }
          return view('edit')->with(['cerveceria' => $cerveceria, 'telefono' => $telefono, 'tipo' => $tipo, 'errors'=>["Hubo un error al cargar la imagen, por favor intente nuevamente"]]);
        } else {
          return view('edit')->with(['cerveceria' => $cerveceria, 'errors'=>["Hubo un error al cargar la imagen, por favor intente nuevamente"]]);
        }
      }

    }
    if($request->hasFile("fotoImg")){
      //Eliminar anterior foto

      //Agregar la nueva foto
      /*
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
        if ($cerveceria['telefono'] != "") {
          $num = substr($cerveceria['telefono'], 3, 1);
          if ($num == 9) {
            $telefono = "15" . substr($cerveceria['telefono'], 7, strlen($cerveceria['telefono']));
            $tipo = 1;
          } else {
            $telefono = substr($cerveceria['telefono'], 6, strlen($cerveceria['telefono']));
            $tipo = 0;
          }
          return view('edit')->with(['cerveceria' => $cerveceria, 'telefono' => $telefono, 'tipo' => $tipo, 'errors'=>["Hubo un error al cargar la imagen, por favor intente nuevamente"]]);
        } else {
          return view('edit')->with(['cerveceria' => $cerveceria, 'errors'=>["Hubo un error al cargar la imagen, por favor intente nuevamente"]]);
        }
      }

    }
*/
    $cerveceria->save();
    return redirect('admin')->with(['mensaje' => 'La cervecería fue actualizada con éxito']);

  }

  public function chequeos (Request $request, String $id){
    //Puede ser que nos cambien el nombre y el id no se vea afectado
    //Si el id se ve afectado hay que verificar que el nuevo id no esté ya en las cervecerias
    $idNuevo = strtolower(preg_replace("/\s+/", "_", $this->removeAccents($request->nombre)));
    if (strcmp($idNuevo,$id)) {
      $cerveceriaNueva = Cerveceria::where('id',$idNuevo)->get();
      if(isset($cerveceriaNueva[0]))
        return "Ya existe una cerveceria con el nombre ".$cerveceriaNueva[0]->nombre;
    }

    //---------------Chequeos del logo
    //Solo se va a chequear si cambiaron de logo, sino no hay problema ya que quedaría la anterior
    if(!empty($request->logoImg)){
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
    }
    //---------------fin de chequeo del logo

    //---------------Chequeos de la foto
    if(!empty($request->fotoImg)){
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
    }
    //---------------fin de chequeo de la foto

    if(empty($request->nombre))
      return "El nombre no debe estar vacío";
    $id = strtolower(preg_replace("/\s+/", "_", $request->nombre));
    $cerveceria = Cerveceria::where('id',$id)->get();
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
