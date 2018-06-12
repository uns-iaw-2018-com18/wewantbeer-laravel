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
    $cerveceria = Cerveceria::where('id', $id)->first();
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
    } else { // No se encontró la cerveceria en el servidor, por lo tanto hubo un error
      return redirect('admin')->with(["mensaje" => "La cervecería no existe, por favor intente nuevamente"]);
    }
  }

  public function edit(Request $request, String $id) {
    $cerveceria = Cerveceria::where('id', $id)->first();

    // if(!isset($cerveceria)){
    //   // Mostrar mensaje de error si es que no se pudo obtener el accessToken
    //   if ($cerveceria['telefono'] != "") {
    //     $num = substr($cerveceria['telefono'], 3, 1);
    //     if ($num == 9) {
    //       $telefono = "15" . substr($cerveceria['telefono'], 7, strlen($cerveceria['telefono']));
    //       $tipo = 1;
    //     } else {
    //       $telefono = substr($cerveceria['telefono'], 6, strlen($cerveceria['telefono']));
    //       $tipo = 0;
    //     }
    //     return view('edit')->with(['cerveceria' => $cerveceria, 'telefono' => $telefono, 'tipo' => $tipo, 'errors'=>["Error al intentar editar, intente nuevamente"]]);
    //   } else {
    //     return view('edit')->with(['cerveceria' => $cerveceria, 'errors'=>["Error al intentar editar, intente nuevamente"]]);
    //   }
    // }
    // $error = $this->chequeos($request,$id);
    // if (!($error === NULL)) {
    //   if ($cerveceria['telefono'] != "") {
    //     $num = substr($cerveceria['telefono'], 3, 1);
    //     if ($num == 9) {
    //       $telefono = "15" . substr($cerveceria['telefono'], 7, strlen($cerveceria['telefono']));
    //       $tipo = 1;
    //     } else {
    //       $telefono = substr($cerveceria['telefono'], 6, strlen($cerveceria['telefono']));
    //       $tipo = 0;
    //     }
    //     return view('edit')->with(['cerveceria' => $cerveceria, 'telefono' => $telefono, 'tipo' => $tipo, 'errors'=>[$error]]);
    //   } else {
    //     return view('edit')->with(['cerveceria' => $cerveceria, 'errors'=>[$error]]);
    //   }
    // }

    if (!isset($cerveceria)) {
      return redirect('admin/edit/' . $id)->withErrors(["La cervecería no existe, por favor intente nuevamente"]);
    }
    $error = $this->chequeos($request, $id);
    if (!($error === NULL)) {
      return redirect('admin/edit/' . $id)->withErrors([$error]);
    }

    $direccionAnterior = $cerveceria->direccion;

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
      $cerveceria->instagram = "";
    }

    $logoAnterior = $cerveceria->logo;
    $fotoAnterior = $cerveceria->foto;

    $accessToken = env('IMGUR_ACCESS_TOKEN');
    // Subir logo
    if ($request->hasFile("logoImg")) {
      $file = $request->file("logoImg");
      $name = $file->getClientOriginalName();
      $data = file_get_contents($file);
      $albumId = env('IMGUR_LOGOS_ALBUM_ID');
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
        return redirect('admin/edit/' . $id)->withErrors(["Hubo un error al cargar la imagen, por favor intente nuevamente"]);
      }
    }
    // Subir foto
    if ($request->hasFile("fotoImg")) {
      $file = $request->file("fotoImg");
      $name = $file->getClientOriginalName();
      $data = file_get_contents($file);
      $albumId = env('IMGUR_FOTOS_ALBUM_ID');
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
        return redirect('admin/edit/' . $id)->withErrors(["Hubo un error al cargar la imagen, por favor intente nuevamente"]);
      }
    }

    // Eliminar logo anterior
    if ($request->hasFile("logoImg")) {
      $imageHash = str_replace("https://i.imgur.com/", "", $logoAnterior);
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
    }
    // Eliminar foto anterior
    if ($request->hasFile("fotoImg")) {
      $imageHash = str_replace("https://i.imgur.com/", "", $fotoAnterior);
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
    }

    if (isset($request->hhCheck)) {
      $cerveceria->happyHour = $request->hhOpen."-".$request->hhClose;
    } else {
      $cerveceria->happyHour = "";
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
    if ($direccionAnterior != $request->direccion) {
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
        return redirect('admin/edit/' . $id)->withErrors(["Hubo un error al cargar la dirección, por favor intente nuevamente"]);
      }
    }

    $cerveceria->save();
    return redirect('admin')->with(['mensaje' => 'La cervecería fue actualizada con éxito']);
  }

  public function chequeos(Request $request, String $id) {
    // Chequeos del logo
    if (!empty($request->logoImg)) {
      // Get Image Dimension
      $fileLogo = $request->file("logoImg");
      $fileinfo = @getimagesize($fileLogo);
      $width = $fileinfo[0];
      $height = $fileinfo[1];
      // Get image file extension
      $file_extension = pathinfo($fileLogo->getClientOriginalName(), PATHINFO_EXTENSION);
      // Validate image file extension
      if ($file_extension != "jpg") {
        return "La imagen del logo debe tener la extensión .jpg";
      }
      // Validate image file dimension
      if ($width != "250" || $height != "250") {
        return "La imagen del logo debe tener las dimensiones 250x250";
      }
    }

    // Chequeos de la foto
    if (!empty($request->fotoImg)) {
      // Get Image Dimension
      $fileFoto = $request->file("fotoImg");
      $fileinfo = @getimagesize($fileFoto);
      $width = $fileinfo[0];
      $height = $fileinfo[1];
      // Get image file extension
      $file_extension = pathinfo($fileFoto->getClientOriginalName(), PATHINFO_EXTENSION);
      // Validate image file extension
      if ($file_extension != "jpg") {
        return "La imagen de la foto debe tener la extensión .jpg";
      }
      // Validate image file dimension
      if ($width != "520" || $height != "250") {
        return "La imagen de la foto debe tener las dimensiones 520x250";
      }
    }

    if (empty($request->nombre)) {
      return "La cervecería debe tener un nombre";
    }
    // Puede ser que nos cambien el nombre y el id no se vea afectado
    // Si el id se ve afectado hay que verificar que el nuevo id no esté ya en las cervecerias
    $idNuevo = strtolower(preg_replace("/\s+/", "_", $this->removeAccents($request->nombre)));
    if (strcmp($idNuevo, $id) != 0) {
      $cerveceriaNueva = Cerveceria::where('id', $idNuevo)->first();
      if (isset($cerveceriaNueva)) {
        return "Ya existe una cerveceria con el nombre \"" . $cerveceriaNueva->nombre . "\"";
      }
    }
    if (empty($request->direccion)) {
      return "La cervecería debe tener una direccion";
    }

    if (isset($request->domCheck)) {
      if (!isset($request->domOpen)) {
        return "El día domingo no tiene horario de apuertura";
      }
      if (!isset($request->domClose)) {
        return "El día domingo no tiene horario de cierre";
      }
      if ($request->domOpen == $request->domClose) {
        return "El día domingo tiene el mismo horario de apertura y cierre";
      }
    }
    if (isset($request->lunCheck)) {
      if (!isset($request->lunOpen)) {
        return "El día lunes no tiene horario de apuertura";
      }
      if (!isset($request->lunClose)) {
        return "El día lunes no tiene horario de cierre";
      }
      if ($request->lunOpen == $request->lunClose) {
        return "El día lunes tiene el mismo horario de apertura y cierre";
      }
    }
    if (isset($request->marCheck)) {
      if (!isset($request->marOpen)) {
        return "El día martes no tiene horario de apuertura";
      }
      if (!isset($request->marClose)) {
        return "El día martes no tiene horario de cierre";
      }
      if ($request->marOpen == $request->marClose) {
        return "El día martes tiene el mismo horario de apertura y cierre";
      }
    }
    if (isset($request->mieCheck)) {
      if (!isset($request->mieOpen)) {
        return "El día miércoles no tiene horario de apuertura";
      }
      if (!isset($request->mieClose)) {
        return "El día miércoles no tiene horario de cierre";
      }
      if ($request->mieOpen == $request->mieClose) {
        return "El día miércoles tiene el mismo horario de apertura y cierre";
      }
    }
    if (isset($request->jueCheck)) {
      if (!isset($request->jueOpen)) {
        return "El día jueves no tiene horario de apuertura";
      }
      if (!isset($request->jueClose)) {
        return "El día jueves no tiene horario de cierre";
      }
      if ($request->jueOpen == $request->jueClose) {
        return "El día jueves tiene el mismo horario de apertura y cierre";
      }
    }
    if (isset($request->vieCheck)) {
      if (!isset($request->vieOpen)) {
        return "El día viernes no tiene horario de apuertura";
      }
      if (!isset($request->vieClose)) {
        return "El día viernes no tiene horario de cierre";
      }
      if ($request->vieOpen == $request->vieClose) {
        return "El día viernes tiene el mismo horario de apertura y cierre";
      }
    }
    if (isset($request->sabCheck)) {
      if (!isset($request->sabOpen)) {
        return "El día sábado no tiene horario de apuertura";
      }
      if (!isset($request->sabClose)) {
        return "El día sábado no tiene horario de cierre";
      }
      if ($request->sabOpen == $request->sabClose) {
        return "El día sábado tiene el mismo horario de apertura y cierre";
      }
    }
    if (isset($request->hhCheck)) {
      if (!isset($request->hhOpen)) {
        return "El happy hour no tiene horario de inicio";
      }
      if (!isset($request->hhClose)) {
        return "El happy hour no tiene horario de finalización";
      }
      if ($request->hhOpen == $request->hhClose) {
        return "El happy hour tiene el mismo horario de inicio y finalización";
      }
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
