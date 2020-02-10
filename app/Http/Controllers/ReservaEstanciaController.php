<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ReservaEstancia;
use App\Habitacion;
use App\ReservaEstanciaHabitacion;
use App\ReservaEstanciaHuesped;
use App\ReservaEstanciaVuelo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReservaEstanciaController extends Controller
{
     /*=============================================
    Mostrar todos los registros
    =============================================*/
    public function index(Request $request){

    	$token = $request->header('Authorization');
    	$usuario = Usuario::all();
    	$json = array(); 	

    	foreach ($usuario as $key => $value) {
    		
    		if("Basic ".base64_encode($value["id_usuario"].":".$value["llave_secreta"]) == $token){
   		
    			//AQUI SE GENERA LA CONSULTA
    			$reserva = ReservaEstancia::all();    		
		    	if(!empty($reserva)){
			    	$json = array(
			    		"status"=>200,
			    		"total_registros"=>count($reserva),
			    		"detalles"=>$reserva			    		
			    	);
			    	return json_encode($json, true);
			    }else{
			    	$json = array(
			    		"status"=>200,
			    		"total_registros"=>0,
			    		"detalles"=>"No hay ningún curso registrado"			    		
			    	);
			    }    		
    		}else{
    			$json = array(
			    		"status"=>404,
			    		"detalles"=>"No está autorizado para recibir los registros"			    		
			    	);
    		}    	
    	}    	
    	return json_encode($json, true);
    } 

    /*=============================================
    Crear un registro
    =============================================*/

    public function store(Request $request){

    
    	$json = array();

               
        //OBTENEMOS LOS DATOS ENVIADOS DESDE EL CLIENTE
        $reserva = $request->input('reserva');
        //GENERAMOS CODIGO DE RESERVA
        $codreserva = $reserva['tipo_reserva_estancia'] == 1 ? null : $this->generate_code_reserva(9);
        //------------------------------------>              
        
        //REGISTRAMOS LA HABITACION
        $flag = false;
        $rooms_states = array();
        $exist = 0;

        $habitaciones = $request->input('habitaciones');
        //VERIFICAMOS DISPONIBILIDAD DE LA HABITACION EN  BASE A LAS FECHAS SELECCIONADAS
        foreach($habitaciones as $value){                   
            $auxiliar=$this->verificar_disponibilidad_habitacion($value['habitacion_id'], $reserva['fecha_llegada'], $reserva['fecha_salida']);               
            if ($auxiliar[0] == false) {
                $exist += 1;
                array_push($rooms_states, $auxiliar[1]);
            }
        }
        
        if ($exist > 0) {
            $flag = false;
        } else {
            $flag = true;
        }
        //VALIDAMOS LA RESPUESTA DE VERIFICACION DE LA DISPONIBILIDAD DE HABITACION
        if ($flag == true) {
            //REGISTRAMOS LA RESERVA
            $idreserva = DB::table('reserva_estancia')->insertGetId([
                'fecha_reserva'=>$reserva['fecha_reserva'],
                'fecha_llegada'=>$reserva['fecha_llegada'],  
                'fecha_salida'  => $reserva['fecha_salida'],
                'estado_pago' => $reserva['estado_pago'],
                'numero_adultos'  => $reserva['numero_adultos'],
                'numero_ninos'  => $reserva['numero_ninos'],
                'estado'  => $reserva['estado'],
                'turno_empleado_id'  => $reserva['turno_empleado_id'],
                'tipo_reserva_estancia'  => $reserva['tipo_reserva_estancia'],
                'tipo_especificacion'  => $reserva['tipo_especificacion'],
                'codigo_reserva'  => $codreserva,
                'persona_id'  => $reserva['persona_id'],
                'direccion_persona_id'  => $reserva['direccion_persona_id'],
                'tiempo_confirmacion'  => $reserva['tiempo_confirmacion'],
                'tipo_pago'  => $reserva['tipo_pago']
                ]
            );
 
            //REGISTRAMOS LA O LAS HABITACIONES SELECCIONADAS A LA RESERVA
            foreach($habitaciones as $value){
                $habitacion = new ReservaEstanciaHabitacion(); 
                $habitacion->habitacion_id=$value['habitacion_id'];
                $habitacion->reserva_estancia_id=$idreserva;
                $habitacion->clase_habitacion_id=$value['clase_habitacion_id'];
                $habitacion->tipo_tarifa=$value['tipo_tarifa'];
                $habitacion->tarifa=$value['tarifa'];
                $habitacion->estado=$value['estado'];
                $habitacion->estado_facturacion=$value['estado_facturacion'];
                $habitacion->save();
                //ACTUALIZAMOS EL ESTADO DE LA HABITACION                    
                $affected = DB::table('habitacion')
                ->where('habitacion_id',$value['habitacion_id'])
                ->update(['estado'=>($reserva['tipo_reserva_estancia'] == 1 ? 4 : ($reserva['tipo_pago'] == 1 ? 3 : 2))]);
            }                    

            //REGISTRAMOS LA HUESPED
            if ($reserva['tipo_reserva_estancia'] == 1) {
                
                $max_correlativo= DB::table('reserva_estancia_huesped')->max('correlativo');
                $numeracion = $max_correlativo+1;                       
                //MEJORAR ESTA PARTE DEL DEL CODIGO DE ACUERDO AL AVANCE
                $huespedes = $request->input('huesped');
                foreach($huespedes as $value){
                    $huesped = new ReservaEstanciaHuesped(); 
                    $huesped->persona_id=$value['persona_id'];
                    $huesped->reserva_estancia_id=$idreserva;
                    $huesped->aereolinea=$value['aereolinea'];
                    $huesped->numero_vuelo=$value['numero_vuelo'];
                    $huesped->correlativo=$numeracion;
                    $huesped->save();
                    $numeracion += 1;  
                }

            }else if ($reserva['tipo_reserva_estancia'] == 2) {
                $max_correlativo= DB::table('reserva_estancia_huesped')->max('correlativo');
                $numeracion = $max_correlativo+1; 
                $huespedes = $request->input('huesped');
                foreach($huespedes as $value){
                    $huesped = new ReservaEstanciaHuesped(); 
                    $huesped->persona_id=$value['persona_id'];
                    $huesped->reserva_estancia_id=$idreserva;
                    $huesped->aereolinea=$value['aereolinea'];
                    $huesped->numero_vuelo=$value['numero_vuelo'];
                    $huesped->correlativo=$numeracion;
                    $huesped->save();
                    $numeracion += 1;  
                }
                //REGISTRAMOS LOS VUELOS
                $vuelos = $request->input('vuelo');                                             
                foreach($vuelos as $value){
                    $vuelo = new ReservaEstanciaVuelo(); 
                    $vuelo->aereolinea=$value['aereolinea'];
                    $vuelo->numero_vuelo=$value['numero_vuelo'];
                    $vuelo->reserva_estancia_id=$idreserva;
                    $vuelo->save();            
                }
                
            }
            //MANDAMOS UNA RESPUESTA DE GRABACION EXITOSA
                       //OBTENER LA RESERVA REGISTRADA
           $reserva_registrada = DB::table('reserva_estancia')->where('reserva_estancia_id','=',$idreserva)->get();
          
            $json = array(			    		
                "status"=>200,
                "reserva"=>$reserva_registrada				    	
            );                
        }
             
        return json_encode($json, true);
    }

    private function generate_code_reserva($longitud) {
        $key = '';
        $pattern = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($pattern)-1;

        for($i=0;$i < $longitud;$i++) $key .= $pattern{mt_rand(0,$max)};

        if ($this->check_code_reserva($key) > 0) {
            $this->generate_code_reserva(9);
        } else {
            return $key;
        }
    }
    private function check_code_reserva($code) {
        return DB::table('reserva_estancia')                
                ->where('codigo_reserva','=',$code)->count();
    }

    private function verificar_disponibilidad_habitacion($habitacion, $fecha_llegada, $fecha_salida) {
        $reservas_estancias = DB::table('reserva_estancia_habitacion')
                        ->join('reserva_estancia', 'reserva_estancia.reserva_estancia_id', '=', 'reserva_estancia_habitacion.reserva_estancia_id')
                        ->join('habitacion', 'habitacion.habitacion_id', '=', 'reserva_estancia_habitacion.habitacion_id')
                        ->select('reserva_estancia.*', 'habitacion.numero_habitacion')
                        ->where([
                            ['reserva_estancia.estado','=','1'],
                            ['reserva_estancia_habitacion.habitacion_id','=',$habitacion],
                            ['reserva_estancia_habitacion.estado','<>','0']
                        ])->get();
        
        if ($reservas_estancias->count() == 0) {
            return array(true, null);
        } else {
            
            $flag = 0;
            $habitacion_states = array();

            foreach ($reservas_estancias as $reserva_estancia) {
                if (date('d/m/Y', strtotime($fecha_llegada)) <= date('d/m/Y', strtotime($reserva_estancia->fecha_salida)) && date('d/m/Y', strtotime($fecha_llegada)) >= date('d/m/Y', strtotime($reserva_estancia->fecha_llegada))) {
                    $flag += 1;
                } else if (date('d/m/Y', strtotime($fecha_salida)) <= date('d/m/Y', strtotime($reserva_estancia->fecha_salida)) && date('d/m/Y', strtotime($fecha_salida)) >= date('d/m/Y', strtotime($reserva_estancia->fecha_llegada))) {
                    $flag += 1;
                }
                
                array_push($habitacion_states, $reserva_estancia);
            }           
            
            if ($flag > 0) {
                return array(false, $habitacion_states);
            } else {
                return array(true, null);
            }
        }
    }

}