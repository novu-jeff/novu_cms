<?php

namespace App\Jobs;

use App\Models\SessionTranscript;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Throwable;

class CheckSessionTranscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $transcriptId)
    {
    }

    public function handle(): void
    {
        $transcript = SessionTranscript::with('recording')
            ->findOrFail($this->transcriptId);

        $metadata = $transcript->provider_metadata ?? [];
        $providerId = $metadata['transcript_id'] ?? null;

        if (! $providerId) {
            throw new RuntimeException('Missing AssemblyAI transcript ID.');
        }

        $client = new Client([
            'base_uri' => 'https://api.assemblyai.com',
            'timeout' => 30,
        ]);

        $response = $client->get(
            '/v2/transcript/' . rawurlencode($providerId),
            [
                'headers' => [
                    'Authorization' => config('services.assemblyai.api_key'),
                ],
            ]
        );

        $result = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (($result['status'] ?? null) === 'completed') {
            $transcript->update([
                'transcript_text' => $result['text'] ?? '',
                'language' => $result['language_code'] ?? null,
                'status' => 'completed',
                'error_message' => null,
                'provider_metadata' => array_merge($metadata, [
                    'completed_at' => now()->toISOString(),
                ]),
            ]);

            $transcript->recording?->update([
                'status' => 'completed',
                'error_message' => null,
            ]);

            return;
        }

        if (($result['status'] ?? null) === 'error') {
            $transcript->update([
                'status' => 'failed',
                'error_message' => 'AssemblyAI could not transcribe this recording.',
                'provider_metadata' => array_merge($metadata, [
                    'provider_error' => $result['error'] ?? 'Unknown provider error',
                ]),
            ]);

            $transcript->recording?->update([
                'status' => 'failed',
                'error_message' => 'Transcription failed. Please review the job details.',
            ]);

            return;
        }

        // Continue polling while the provider is still processing.
        $pollCount = ($metadata['poll_count'] ?? 0) + 1;

        if ($pollCount >= 120) {
            $transcript->update([
                'status' => 'failed',
                'error_message' => 'Transcription timed out while waiting for the provider.',
                'provider_metadata' => array_merge($metadata, [
                    'poll_count' => $pollCount,
                ]),
            ]);

            $transcript->recording?->update([
                'status' => 'failed',
                'error_message' => 'Transcription timed out.',
            ]);

            return;
        }

        $transcript->update([
            'provider_metadata' => array_merge($metadata, [
                'poll_count' => $pollCount,
            ]),
        ]);

        self::dispatch($transcript->id)->delay(now()->addSeconds(15));
    }

    public function failed(Throwable $exception): void
    {
        $transcript = SessionTranscript::with('recording')
            ->find($this->transcriptId);

        if ($transcript) {
            $transcript->update([
                'status' => 'failed',
                'error_message' => 'Unable to check transcription status. Please retry.',
            ]);

            $transcript->recording?->update([
                'status' => 'failed',
                'error_message' => 'Unable to retrieve transcription status.',
            ]);
        }

        report($exception);
    }
}