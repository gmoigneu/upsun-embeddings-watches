<?php

namespace App\Console\Commands;

use App\Helpers\MarkdownHelper;
use App\Models\Watch;
use Illuminate\Console\Command;
use OpenAI;
use Pgvector\Laravel\Distance;
use Pgvector\Laravel\Vector;

class Query extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:query {query}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query OpenAI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = OpenAI::client(config('services.openai.key'));

        // Create the embedding of the query
        $response = $client->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $this->argument('query')
        ]);

        $embedding = new Vector($response->embeddings[0]->embedding);

        // Find the closest watch
        $neighbors = Watch::query()->nearestNeighbors('embedding', $embedding, Distance::L2)->take(1)->get();

        // Ask OpenAI for the closest watches
        $result = $client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are an helpful AI that will recommend watches to the user. The watches selected are below with their attributes as markdown. Send the user a markdown formatted view of each watch and for each attribute, tell then why they are a good match to their request."
                ],
                [
                    'role' => 'user',
                    'content' => "The recommended watch is: " . MarkdownHelper::toMarkdown($neighbors[0])
                ],
                ['role' => 'user', 'content' => $this->argument('query')],
            ],
        ]);

        $this->output->writeln($result->choices[0]->message->content);
    }
}
