<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\AlbumPhoto;
use App\Models\Gallery;
use App\Models\MemberDocument;
use App\Models\SessionDocument;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds 25 galleries, 25 album photos, and session_documents
 * linking LIS member_documents by shared DUMMY-XXX keys.
 */
class DummyMediaSeeder extends Seeder
{
    public function run(): void
    {
        $manifestPath = storage_path('app/dummy_media_manifest.json');
        if (! is_file($manifestPath)) {
            $this->command?->error("Missing manifest: {$manifestPath}. Generate dummy files first.");
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $count = count($manifest['link_key'] ?? []);
        if ($count < 20) {
            $this->command?->error('Manifest must contain at least 20 items.');
            return;
        }

        if (Gallery::where('title', 'like', 'DUMMY-001%')->exists()) {
            $this->command?->info('CMS dummy media already seeded — skipping.');
            return;
        }

        $now = Carbon::now();
        $sessions = [2, 3, 4];

        DB::transaction(function () use ($manifest, $count, $now, $sessions) {
            // One album that holds all 25 linked photos
            $albumId = Album::insertGetId([
                'name' => 'LIS-CMS-DMS Dummy Album',
                'isActive' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            for ($i = 0; $i < $count; $i++) {
                $key = $manifest['link_key'][$i];
                $n = $i + 1;

                Gallery::create([
                    'title' => "{$key} — Gallery Image {$n}",
                    'image' => $manifest['cms_gallery'][$i],
                    'active' => true,
                ]);

                // description column exists on galleries but is not fillable — set via query
                DB::table('galleries')
                    ->where('title', "{$key} — Gallery Image {$n}")
                    ->update([
                        'description' => "Linked image for {$key} (matches LIS doc + DMS document tags).",
                        'updated_at' => $now,
                    ]);

                AlbumPhoto::create([
                    'album_id' => $albumId,
                    'image_path' => $manifest['cms_album'][$i],
                    'isActive' => true,
                ]);
            }

            // Link approved/forwarded LIS docs into CMS session agenda
            $lisDocs = MemberDocument::query()
                ->where('title', 'like', 'DUMMY-%')
                ->orderBy('id')
                ->get();

            foreach ($lisDocs as $idx => $doc) {
                if (! in_array($doc->status, ['approved', 'forwarded'], true)) {
                    continue;
                }

                $exists = SessionDocument::where('document_id', $doc->id)->exists();
                if ($exists) {
                    continue;
                }

                SessionDocument::create([
                    'session_id' => $doc->session_id ?: $sessions[$idx % count($sessions)],
                    'document_id' => $doc->id,
                    'agenda_order' => $doc->agenda_order ?: ($idx + 1),
                    'status' => $doc->status === 'approved' ? 'completed' : 'pending',
                ]);
            }
        });

        $this->command?->info("Seeded {$count} CMS galleries, {$count} album photos, and session links to LIS docs.");
    }
}
