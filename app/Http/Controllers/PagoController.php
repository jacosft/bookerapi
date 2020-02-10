<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pago;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class PagoController extends Controller
{
    public function index(Request $request){

    	
    	$json = array(); 
        //AQUI SE GENERA LA CONSULTA
        $pago = Pago::all();    		
        if(!empty($pago)){
            $json = array(
                "status"=>200,
                "total_registros"=>count($pago),
                "detalles"=>$pago			    		
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
        "concepto"=>$request->input("concepto"),
        "fecha_hora_deposito"=>$request->input("fecha_hora_deposito"),
        "medio_pago"=>$request->input("medio_pago"),
        "entidad_bancaria"=>$request->input("entidad_bancaria"),
        "numero_deposito"=>$request->input("numero_deposito"),
        "monto_deposito"=>$request->input("monto_deposito"),
        "tipo_moneda"=>$request->input("tipo_moneda"),                
        "reserva_estancia_id"=>$request->input("reserva_estancia_id"),
        "habitacion_id"=>$request->input("habitacion_id")
    );             
        
    if(!empty($datos)){
        
        //Validar datos
        $validator = Validator::make($datos, [
            'concepto' => 'required|string|max:45',
            'monto_deposito' => 'required|numeric',

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
            $codigopago = $this->generate_code_pago(9);
            $pagos = new Pago();
            $pagos->codigo_pago = $codigopago;
            $pagos->concepto = $datos["concepto"];
            $pagos->fecha_hora_deposito = $datos["fecha_hora_deposito"];
            $pagos->medio_pago = $datos["medio_pago"];
            $pagos->entidad_bancaria = $datos["entidad_bancaria"];
            $pagos->monto_deposito = $datos["monto_deposito"];
            $pagos->numero_deposito = $datos["numero_deposito"];
            $pagos->tipo_moneda = $datos["tipo_moneda"];
            $pagos->reserva_estancia_id = $datos["reserva_estancia_id"];
            $pagos->habitacion_id = $datos["habitacion_id"];
            $pagos->save();

            $json = array(
                "status"=>200,
                "detalle"=>"Registro exitoso, su curso ha sido guardado"                        
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
               
        $validar = Pago::where("pago_id", $id)->get();
        if(!empty($validar)){    				
            $curso = Pago::where("pago_id", $id)->delete();
            $json = array(
                "status"=>200,
                "detalle"=>"Se elimino el pago con éxito"				    		
            );
            return json_encode($json, true);
            
        }else{
            $json = array(
                "status"=>404,
                "detalle"=>"El pago no existe"
            );				    
        }
    	return json_encode($json, true);
    }

    private function generate_code_pago($longitud) {
        $key = '';
        $pattern = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($pattern)-1;

        for($i=0;$i < $longitud;$i++) $key .= $pattern{mt_rand(0,$max)};

        if ($this->check_code_pago($key) > 0) {
            $this->generate_code_pago(9);
        } else {
            return $key;
        }
    }
    private function check_code_pago($code) {
        return DB::table('pago')                
                ->where('codigo_pago','=',$code)->count();
    }
}
