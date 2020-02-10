<?php

namespace App\Http\Controllers;
use App\Persona;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PersonaController extends Controller
{
    public function index(Request $request){
    	$json = array(); 	
   		
		//AQUI SE GENERA LA CONSULTA
		$persona = Persona::all();    		
		if(!empty($persona)){
			$json = array(
				"status"=>200,
				"total_registros"=>count($persona),
				"detalles"=>$persona			    		
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

	public function store(Request $request){
    
    	$json = array();

        //Recoger datos
        $datos = array( 
			"ubica_persona"=>$request->input("ubica_persona"),
			"tipo_persona_id"=>$request->input("tipo_persona_id"),
			"tipo_cliente_id"=>$request->input("tipo_cliente_id"),
			"tipo_documento_id"=>$request->input("tipo_documento_id"),
			"numero_documento"=>$request->input("numero_documento"),
			"razon_social_nombre"=>$request->input("razon_social_nombre"),
			"nombre_comercial"=>$request->input("nombre_comercial"),                
			"profesion"=>$request->input("profesion"),
			"estado_civil"=>$request->input("estado_civil"),
			"fecha_nacimiento"=>$request->input("fecha_nacimiento"),
			"nacionalidad"=>$request->input("nacionalidad"),
			"sexo"=>$request->input("sexo"),
			"correo_electronico"=>$request->input("correo_electronico"),
			"telefono"=>$request->input("telefono"),
			"celular"=>$request->input("celular"),
			"rpm_rpc"=>$request->input("rpm_rpc"),
			"pagina_web"=>$request->input("pagina_web"),
			"nombre_contacto"=>$request->input("nombre_contacto"),
			"telefono_contacto"=>$request->input("telefono_contacto"),
			"estado"=>$request->input("estado")
    	);             
        
    if(!empty($datos)){
		//validamos si existe un cliente
		$persona = DB::table('persona')		
		->where('numero_documento','=',$datos['numero_documento'])
		->orWhere('razon_social_nombre','=',$datos['razon_social_nombre'])
		->orWhere('nombre_comercial','=',$datos['nombre_comercial'])
		->get();

        if ($persona->count()==0){

			//Validar datos
			$validator = Validator::make($datos, [
				'numero_documento' => 'required',
				'razon_social_nombre' => 'required',
				'nombre_comercial' => 'required',
			]);
			//Si falla la validación
			if ($validator->fails()) {
				$errors = $validator->errors();
				$json = array(                    
					"status"=>404,
					"detalle"=>$errors                    
				);

				return json_encode($json, true);
			}else{
				
				$persona = new Persona();
				$persona->ubica_persona =$datos["ubica_persona"];
				$persona->tipo_persona_id = $datos["tipo_persona_id"];
				$persona->tipo_cliente_id = $datos["tipo_cliente_id"];
				$persona->tipo_documento_id = $datos["tipo_documento_id"];
				$persona->numero_documento = $datos["numero_documento"];
				$persona->razon_social_nombre = $datos["razon_social_nombre"];
				$persona->nombre_comercial = $datos["nombre_comercial"];
				$persona->profesion = $datos["profesion"];
				$persona->estado_civil = $datos["estado_civil"];
				$persona->fecha_nacimiento = $datos["fecha_nacimiento"];
				$persona->nacionalidad = $datos["nacionalidad"];
				$persona->sexo = $datos["sexo"];
				$persona->correo_electronico = $datos["correo_electronico"];
				$persona->telefono = $datos["telefono"];
				$persona->celular = $datos["celular"];
				$persona->rpm_rpc = $datos["rpm_rpc"];
				$persona->pagina_web = $datos["pagina_web"];
				$persona->nombre_contacto = $datos["nombre_contacto"];
				$persona->telefono_contacto = $datos["telefono_contacto"];
				$persona->estado = $datos["estado"];
				$persona->save();

				$json = array(
					"status"=>200,
					"detalle"=>"Registro exitoso, la persona ha sido guardado",
					"persona"=>$persona                     
				);            
			}
			}else{
				$json = array(
				"status"=>404,
				"detalle"=>"Ya existe un registro con los datos que intenta ingresas"                
			); 
			}
		}else{
			$json = array(
				"status"=>404,
				"detalle"=>"Los registros no pueden estar vacíos"                
			);			
		}        
        return json_encode($json, true);
    }

    public function getpersonaid(Request $request){
		$json = array(); 	
   		
		//AQUI SE GENERA LA CONSULTA
		$persona = DB::table('persona')
		->join('tipo_persona','tipo_persona.tipo_persona_id','=','persona.tipo_persona_id')
		->join('tipo_cliente','tipo_cliente.tipo_cliente_id','=','persona.tipo_cliente_id')
		->join('tipo_documento','tipo_documento.tipo_documento_id','=','persona.tipo_cliente_id')
		->where('numero_documento','like','%'.$request->get("parametro").'%')
		->orWhere('razon_social_nombre','like','%'.$request->get("parametro").'%')
		->get();   		

		if(!empty($persona)){
			$json = array(
				"status"=>200,
				"total_registros"=>count($persona),
				"detalles"=>$persona			    		
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
