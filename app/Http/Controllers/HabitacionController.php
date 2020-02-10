<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Habitacion;
use App\Usuario;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class HabitacionController extends Controller
{
    /*=============================================
    Mostrar todos los registros
    =============================================*/

    public function index(Request $request){
    	$json = array(); 	
    			
    			$habitacion = Habitacion::all();
    		
		    	if(!empty($habitacion)){

			    	$json = array(

			    		"status"=>200,
			    		"total_registros"=>count($habitacion),
			    		"detalles"=>$habitacion
			    		
			    	);

			    	return json_encode($json, true);

			    }else{

			    	$json = array(

			    		"status"=>200,
			    		"total_registros"=>0,
			    		"detalles"=>"No hay ningún curso registrado"
			    		
			    	);

			    }
    	return json_encode($json, true);
    } 
}
