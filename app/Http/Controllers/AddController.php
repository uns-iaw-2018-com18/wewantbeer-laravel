<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cerveceria;
use Session;

class AddController extends Controller {

    public function checkId(Request $request) {
      $nombre = $request->input('nombre');
      $id = strtolower(preg_replace("/\s+/", "_", $this->removeAccents($request->nombre)));
      $exists = \App\Cerveceria::where('id', $id)->first();
      if ($exists) {
        return response()->json(array("exists" => true));
      } else {
        return response()->json(array("exists" => false));
      }
    }

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
        $cerveceria->instagram = "";
      }

      // Si llegaste hasta aca, probablemente quieras saber como funciona la API de Imgur para subir imagenes
      // Primero tenes que crearte una cuenta en https://imgur.com/register
      // Luego tenes que registrar tu aplicacion en https://api.imgur.com/oauth2/addclient
      // Completa los campos, y elegi "OAuth 2 authorization without a callback URL"
      // Despues tenes que acceder a https://api.imgur.com/oauth2/authorize?client_id=CLIENT_ID&response_type=token
      // Reemplazando CLIENT_ID por tu Client ID
      // Ahi te va a pedir autorizacion para ingresar con tu cuenta de Imgur, lo que tenes que aceptar
      // Luego te redirecciona a una pagina con un URL asi https://imgur.com/#access_token=ACCESS_TOKEN&expires_in=EXPIRES_IN&token_type=bearer&refresh_token=REFRESH_TOKEN&account_username=ACCOUNT_NAME&account_id=ACCOUNT_ID
      // El valor en el campo access_token es el que tenes que usar para subir imagenes a tu propia cuenta
      // Ese token expira en un mes, para lo cual necesitas el refresh token para solicitar otro access token
      // Asi que te conviene guardar todos esos campos para evitar tener que hacer todos los pasos del acceso nuevamente
      // Para mas informacion sobre la API visita https://apidocs.imgur.com/
      // GL, HF :)

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
          return redirect('admin/add')->withErrors(["Hubo un error al cargar la imagen, por favor intente nuevamente"]);
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
          return redirect('admin/add')->withErrors(["Hubo un error al cargar la imagen, por favor intente nuevamente"]);
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
        return redirect('admin/add')->withErrors(["Hubo un error al cargar la dirección, por favor intente nuevamente"]);
      }

      $cerveceria->save();
      return redirect('admin')->with(['mensaje' => 'La cervecería fue creada con éxito']);
    }

    public function chequeos(Request $request) {
        // Chequeos del logo
        if (empty($request->logoImg)) {
          return "La cervecería debe tener un logo";
        }
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

        // Chequeos de la foto
        if (empty($request->fotoImg)) {
          return "La cervecería debe tener una foto";
        }
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

        if (empty($request->nombre)) {
          return "La cervecería debe tener un nombre";
        }
        $id = strtolower(preg_replace("/\s+/", "_", $this->removeAccents($request->nombre)));
        $cerveceria = Cerveceria::where('id', $id)->first();
        if (isset($cerveceria)) {
          return "Ya existe una cerveceria con el nombre \"" . $request->nombre . "\"";
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
