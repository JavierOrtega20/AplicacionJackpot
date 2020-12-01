<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\trans_head;
use Illuminate\Support\Facades\DB;

class ActualizarTransacciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:actualiza_transacciones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Esta tarea actualiza los estatus de las transacciones a intervalos de 1 minuto';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = Carbon::now();
        $newDate = $date->subMinutes(0);
        
        
        $transacciones = trans_head::select('id','token_time','status')
                        ->where('token_status',false)
                        ->get();
 
        foreach ($transacciones as $key => $value) {
                 if($newDate  >= $value->token_time && $value->status == 1 ){
                        $actualizar = trans_head::find($value->id)
                            ->update([
                            'status' => 2,
                            ]);

                            echo nl2br(date('d/m/Y H:i:s')." -- se relaizó la actualización del estatus de transaccción del registro con id '.$value->id.' en la tabla trans_head.\r\n");
                 }else{
                            echo nl2br(date('d/m/Y H:i:s')." -- El cron se ejecutó pero no econtró registros para actualizar.\r\n");
                 }
        }
    

        $trans = trans_head::select(
            'id','updated_at')
        ->where('trans_head.procesado',null)
        ->whereIn('trans_head.status',[6])
        ->whereNotIn('trans_head.status',[5])
        ->where('trans_head.reverso',null)
        ->where(DB::raw("trans_head.created_at"),'>',DB::raw("current_timestamp - interval '30 hours'"))
        ->get();


        foreach($trans as $value => $v){
            $time = date($v->updated_at);
            $newTime = strtotime('+1 minute',strtotime($time)) ;
            $newTime = date('Y-m-d H:i:s',$newTime);
            $time = \Carbon\Carbon::parse($time);
            $newTime = \Carbon\Carbon::parse($newTime);
            $actual = \Carbon\Carbon::now();
            $actual = $time->diffInMinutes($newTime);

            if($actual >= 1){
                $actualizar = trans_head::find($v->id)
                            ->update([
                                    'status' => 0,
                ]);
                echo nl2br(date('d/m/Y H:i:s')."----".$v->id."- ID desbloqueado. \r\n");
            }
            
        }

        //dd(date('d/m/Y H:i:s').' Cron de actualización de estatus de transacciones ejecutado');*/
    }
}
