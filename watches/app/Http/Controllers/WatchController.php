<?php

namespace App\Http\Controllers;

use App\Helpers\MarkdownHelper;
use App\Http\Requests\SearchWatchRequest;
use App\Models\Watch;
use OpenAI;
use Pgvector\Laravel\Distance;
use Pgvector\Laravel\Vector;

class WatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function search(SearchWatchRequest $request)
    {
        $embeddingClient = OpenAI::client(config('services.openai.key'));
        $chatClient = OpenAI::factory()
            ->withApiKey(config('services.groq.key'))
            ->withBaseUri('api.groq.com/openai/v1') // default: api.openai.com/v1
            ->make();

        // Create the embedding of the query
        $response = $embeddingClient->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $request->input('query')
        ]);

        $embedding = new Vector($response->embeddings[0]->embedding);

        // Find the closest watch
        $neighbor = Watch::query()->nearestNeighbors('embedding', $embedding, Distance::L2)->first();

        // Ask OpenAI for the closest watches
        $stream = $chatClient->chat()->createStreamed([
            'model' => 'llama3-8b-8192',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are an helpful AI that will recommend watches to the user. The watch selected is below with its attributes as a list. Your response is a markdown document.First, output the brand and model of the watch as a title.  Second, send the user a bullet list view of the watch attributes without doing any modification. Then, add a paragrah of why this watch matches their request using the watch attributes."
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
