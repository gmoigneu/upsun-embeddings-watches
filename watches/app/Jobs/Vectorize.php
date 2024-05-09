<?php

namespace App\Jobs;

use App\Models\Watch;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NumberFormatter;
use OpenAI;
use Pgvector\Laravel\Vector;

class Vectorize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Empty DB
        DB::table('watches')->truncate();

        $client = OpenAI::client(config('services.openai.key'));

        $file = base_path("seeds/watches-full.csv");
        $csv = array_map("str_getcsv", file($file));
        $header = array_shift($csv);

        $watches = array_map(fn($row) => array_combine($header, $row), $csv);

        foreach($csv as $row) {
            try{
                Watch::create([
                    'brand' => $row[0],
                    'model' => $row[1],
                    'case_material' => $row[2],
                    'strap_material' => $row[3],
                    'movement_type' => $row[4],
                    'water_resistance' => $row[5],
                    'case_diameter_mm' => $row[6],
                    'case_thickness_mm' => $row[7],
                    'band_width_mm' => $row[8],
                    'dial_color' => $row[9],
                    'crystal_material' => $row[10],
                    'complications' => $row[11],
                    'power_reserve' => $row[12],
                    'price_usd' => preg_replace("/[^0-9.]/", "", $row[13]),
                ]);
            } catch(\Exception $e){
                ray($row);
            }
        }

        $watchesInput = collect($watches)->map(function ($watch) {
            $str = '';
            foreach($watch as $key=>$item) {
                $str .= $key.':'.$item.',';
            }
            return rtrim($str, '|');
        });

        $response = $client->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $watchesInput->toArray()
        ]);

        foreach ($response->embeddings as $embedding) {
            $watch = Watch::find($embedding->index + 1);
            $watch->embedding = new Vector($embedding->embedding);
            $watch->save();
        }
    }
}
