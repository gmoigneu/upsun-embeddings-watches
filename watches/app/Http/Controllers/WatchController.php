<?php

namespace App\Http\Controllers;

use App\Helpers\MarkdownHelper;
use App\Http\Requests\SearchWatchRequest;
use App\Models\Watch;
use Illuminate\Http\Request;
use OpenAI;
use Pgvector\Laravel\Distance;
use Pgvector\Laravel\Vector;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function search(Request $request)
    {
        $client = OpenAI::client(config('services.openai.key'));

        // Create the embedding of the query
        $response = $client->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $request->input('query')
        ]);

        $embedding = new Vector($response->embeddings[0]->embedding);

        // Find the closest watch
        $neighbor = Watch::query()->nearestNeighbors('embedding', $embedding, Distance::L2)->first();

        // Ask OpenAI for the closest watches
        $stream = $client->chat()->createStreamed([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are an helpful AI that will recommend watches to the user. The watch selected is below with its attributes as markdown. Send the user a markdown formatted view of each watch and for each attribute, tell then why they are a good match to their request."
                ],
                [
                    'role' => 'user',
                    'content' => "The recommended watch is: " . MarkdownHelper::toMarkdown($neighbor)
                ],
                ['role' => 'user', 'content' => $request->input('query')],
            ],
        ]);

        return response()->stream(function () use ($stream) {
            $i = 0;
            $text = "";

            foreach($stream as $response){
                $text .= $response->choices[0]->delta->content;

                $i++;

                echo "event: chunk\n";
                echo "data: {\"index\": " . $i . ", \"chunk\": " . json_encode($response->choices[0]->delta->content) . "}\n\n";

                ob_flush();
                flush();

                // Break the loop if the client aborted the connection (closed the page)
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
        ]);
    }
}
