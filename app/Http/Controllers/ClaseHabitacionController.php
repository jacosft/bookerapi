<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ClaseHabitacion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class ClaseHabitacionController extends Controller
{
    /*=============================================
    Mostrar todos los registros
    =============================================*/

    public function index(Request $request){	
    			
    			$clasehabitacion = ClaseHabitacion::all();
    		
		    	if(!empty($clasehabitacion)){

			    	$json = array(

			    		"status"=>200,
			    		"total_registros"=>count($clasehabitacion),
			    		"detalles"=>$clasehabitacion
			    		
			    	);

			    	

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
