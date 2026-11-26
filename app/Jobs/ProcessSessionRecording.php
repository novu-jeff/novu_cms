<?php

namespace App\Jobs;

use App\Models\SessionRecording;
use App\Models\SessionTranscript;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProcessSessionRecording implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $recordingId)
    {
    }

    public function handle(): void
    {
        $recording = SessionRecording::findOrFail($this->recordingId);
        $apiKey = config('services.assemblyai.api_key');

        if (! $apiKey) {
            throw new RuntimeException('AssemblyAI API key is missing.');
        }

        $recording->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        $transcript = SessionTranscript::firstOrCreate(
            ['recording_id' => $recording->id],
            ['status' => 'pending', 'provider' => 'assemblyai']
        );

        $stream = Storage::disk($recording->disk)
            ->readStream($recording->file_path);

        if (! is_resource($stream)) {
            throw new RuntimeException('Unable to read recording file.');
        }

        try {
            $client = new Client([
                'base_uri' => 'https://api.assemblyai.com',
                'timeout' => 120,
                'http_errors' => true,
            ]);

            $uploadResponse = $client->post('/v2/upload', [
                'headers' => [
                    'Authorization' => $apiKey,
                    'Content-Type' => 'application/octet-stream',
                ],
                'body' => $stream,
            ]);

            $upload = json_decode(
                (string) $uploadResponse->getBody(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = $e->getResponse()
                ? (string) $e->getResponse()->getBody()
                : $e->getMessage();

            \Illuminate\Support\Facades\Log::error(
                'AssemblyAI audio upload failed.',
                [
                    'recording_id' => $recording->id,
                    'http_status' => $e->getResponse()?->getStatusCode(),
                    'response' => $responseBody,
                ]
            );

            throw $e;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $audioUrl = $upload['upload_url'] ?? null;

        if (! $audioUrl) {
            throw new RuntimeException('AssemblyAI returned no upload URL.');
        }

        $response = $client->post('/v2/transcript', [
            'headers' => [
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'audio_url' => $audioUrl,
                'speech_models' => ['universal-2'],
                'language_detection' => true,
                'punctuate' => true,
                'format_text' => true,
            ],
        ]);

        $result = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (empty($result['id'])) {
            throw new RuntimeException(
                'AssemblyAI returned no transcript ID.'
            );
        }

        $transcript->update([
            'provider' => 'assemblyai',
            'status' => 'processing',
            'provider_metadata' => [
                'transcript_id' => $result['id'],
            ],
        ]);

        CheckSessionTranscription::dispatch($transcript->id)
            ->delay(now()->addSeconds(15));
    }

    public function failed(Throwable $exception): void
    {
        $recording = SessionRecording::find($this->recordingId);

        if ($recording) {
            $recording->update([
                'status' => 'failed',
                'error_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    1000
                ),
            ]);

            $recording->transcript()?->update([
                'status' => 'failed',
                'error_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    1000
                ),
            ]);
        }

        report($exception);
    }
}