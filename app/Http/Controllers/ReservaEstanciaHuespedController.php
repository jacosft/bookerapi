<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\ReservaEstanciaHuesped;

class ReservaEstanciaHuespedController extends Controller
{
    public function index(Request $request){

    	
    	$json = array(); 
        //AQUI SE GENERA LA CONSULTA
        $huesped = ReservaEstanciaHuespued::all();   		
        if(!empty($huesped)){
            $json = array(
                "status"=>200,
                "total_registros"=>count($huesped),
                "detalles"=>$huesped			    		
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

    public function store(Request $request){
    
    	$json = array();

        //Recoger datos
        $datos = array( 
			"persona_id"=>$request->input("persona_id"),
			"medio_transporte"=>$request->input("medio_transporte"),
			"procedencia_id"=>$request->input("procedencia_id"),
			"destino_id"=>$request->input("destino_id"),			
            "reserva_estancia_id"=>$request->input("reserva_estancia_id"), 
            "habitacion_id"=>$request->input("habitacion_id"),                      
            "descripcion_motivo_viaje"=>$request->input("descripcion_motivo_viaje"),
            "aereolinea"=>$request->input("aereolinea"),
            "numero_vuelo"=>$request->input("numero_vuelo")
    	);             
        
        if(!empty($datos)){
            //validamos si existe ya la huesped agregado a la estancia
            $validar = DB::table('reserva_estancia_huesped')		
            ->where('reserva_estancia_id','=',$datos['reserva_estancia_id'])
            ->orWhere('persona_id','=',$datos['persona_id'])
            ->get();

            if ($validar->count()==0){

                //Validar datos
                $validator = Validator::make($datos, [
                    'reserva_estancia_id' => 'required',
                    'persona_id' => 'required',
                ]);
                //Si falla la validación
                if ($validator->fails()) {
                    $errors = $validator->errors();
                    $json = array(                    
                        "status"=>404,
                        "detalle"=>$errors                    
                    );

                    
                }else{
                    $max_correlativo= DB::table('reserva_estancia_huesped')->max('correlativo');
                    $numeracion = $max_correlativo+1;

                    $huesped = new ReservaEstanciaHuesped();
                    $huesped->persona_id =$datos["persona_id"];
                    $huesped->medio_transporte = $datos["medio_transporte"];
                    $huesped->procedencia_id = $datos["procedencia_id"];
                    $huesped->destino_id = $datos["destino_id"];
                    $huesped->reserva_estancia_id = $datos["reserva_estancia_id"];
                    $huesped->habitacion_id = $datos["habitacion_id"];
                    $huesped->correlativo = $numeracion;                    
                    $huesped->descripcion_motivo_viaje = $datos["descripcion_motivo_viaje"];
                    $huesped->aereolinea = $datos["aereolinea"];
                    $huesped->numero_vuelo = $datos["numero_vuelo"];
                    
                    $huesped->save();

                    $json = array(
                        "status"=>200,
                        "detalle"=>"Registro exitoso, la huesped ha sido guardado",
                        "huesped"=>$huesped                     
                    );            
                }
                }else{
                    $json = array(
                    "status"=>404,
                    "detalle"=>"Ya existe un registro con los datos que intenta ingresar"                
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

    public function update($id, Request $request){
    	
    	$json = array();
        //Recoger datos
        $datos = array( "medio_transporte"=>$request->input("medio_transporte"),
                        "procedencia_id"=>$request->input("procedencia_id"),
                        "destino_id"=>$request->input("destino_id"),
                        "motivo_viaje"=>$request->input("motivo_viaje"),
                        "descripcion_motivo_viaje"=>$request->input("descripcion_motivo_viaje"),
                        "aereolinea"=>$request->input("aereolinea"),
                        "numero_vuelo"=>$request->input("numero_vuelo"));

        if(!empty($datos)){         
            $validar = DB::table('reserva_estancia_huesped')		
            ->where('reserva_estancia_huesped_id','=',$id)            
            ->get();

            if ($validar->count()==0){
                $huesped = ReservaEstanciaHuesped::where("reserva_estancia_huesped_id", $id)->update($datos);
                $json = array(
                    "status"=>200,
                    "detalle"=>"Registro exitoso, ha sido actualizado",
                    "huesped"=>$datos                        
                );  			       
            } else{
                $json = array(
                    "status"=>404,
                    "detalle"=>"Los registros no existe"            
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

    public function destroy($id, Request $request){
   
    	$json = array();
        $validar = ReservaEstanciaHuesped::where("reserva_estancia_huesped_id", $id)->get();
        if(!$validar->count()==0){                     
            $huesped = ReservaEstanciaHuesped::where("reserva_estancia_huesped_id", $id)->delete();
            $json = array(
                "status"=>200,
                "detalle"=>"Se ha borrado el registro con éxito",
                "huesped"=>$validar
            ); 
        }else{
            $json = array(
                "status"=>404,
                "detalle"=>"El regsitro no existe"
            );            
        }   
    	return json_encode($json, true);
    }


    public function gethuespedreservaestancia(Request $request){
        $reserva_huesped=DB::table('reserva_estancia_huesped')
        ->join('reserva_estancia', 'reserva_estancia.reserva_estancia_id', '=', 'reserva_estancia_huesped.reserva_estancia_id')
        ->join('persona', 'persona.persona_id', '=', 'reserva_estancia_huesped.persona_id')
        ->select('reserva_estancia.reserva_estancia_id', 'reserva_estancia_huesped.*')
        ->where([
            ['reserva_estancia.reserva_estancia_id','=',$request->get('reservaid')],
            ['reserva_estancia_huesped.persona_id','=',$request->get('huespedid')]
        ])->get();  		
        
		if($reserva_huesped->count()>0){
			$json = array(
				"status"=>200,
				"total_registros"=>count($reserva_huesped),
				"detalles"=>$reserva_huesped			    		
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
