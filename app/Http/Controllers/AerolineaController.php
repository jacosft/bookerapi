<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Aerolinea;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AerolineaController extends Controller
{
   /*=============================================
    Mostrar todos los registros
    =============================================*/

    public function index(Request $request){
   			
		$aerolinea = Aerolinea::all();
		$json = array();
		if(!empty($aerolinea)){

			$json = array(

				"status"=>200,
				"total_registros"=>count($aerolinea),
				"detalles"=>$aerolinea
				
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
