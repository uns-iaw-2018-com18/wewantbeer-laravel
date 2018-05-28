<?php
namespace App;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
class Cerveceria extends Eloquent
{
    protected $collection = 'cervecerias';
    protected $fillable = ['id','nombre','direccion','telefono','web','email','sumaPuntajes','cantidadPuntajes','horario','happyHour','logo','foto','facebook','instagram'];
}
