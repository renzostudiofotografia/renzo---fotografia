<?php

namespace App\Http\Controllers;

use App\LandingContent;
use App\Models\LandingLead;
use App\Models\LandingPage;
use App\Models\MediaAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __construct(public LandingContent $content) {}

    public function index(Request $request): View
    {
        $showTrash = $request->boolean('trash');
        $query = LandingPage::withCount('leads');
        if ($showTrash) {
            $query->onlyTrashed();
        }

        return view('admin.landings', [
            'landings' => $query->latest($showTrash ? 'deleted_at' : 'updated_at')->paginate(12)->withQueryString(),
            'pendingCount' => 0, 'showTrash' => $showTrash,
            'trashCount' => LandingPage::onlyTrashed()->count(), 'activeCount' => LandingPage::count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:landing_pages,slug'],
            'preset' => ['required', Rule::in(['editorial', 'modern'])],
        ], [
            'slug.regex' => 'Usa letras minúsculas, números y guiones para el enlace.',
            'slug.unique' => 'Este enlace ya pertenece a otra landing.',
        ]);
        $landing = LandingPage::create([
            'name' => $data['name'], 'slug' => $data['slug'],
            'draft' => $this->content->defaults($data['preset']),
            'revision' => 1, 'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.landings.edit', $landing)->with('status', 'Landing creada como borrador. Personalízala antes de publicar.');
    }

    public function edit(LandingPage $landing): View
    {
        $data = $this->content->hydrate($landing->draft);
        $oldData = session()->getOldInput('data');
        if (is_array($oldData)) {
            $data = array_replace($data, $oldData);
            foreach ($this->content->fields() as $key => $field) {
                if ($field['type'] === 'repeater') {
                    $data[$key] = $oldData[$key] ?? [];
                }
            }
        }

        $videoUploadMaxBytes = $this->videoUploadLimit();

        return view('admin.landing-editor', [
            'landing' => $landing, 'data' => $data,
            'revision' => session()->getOldInput('revision', $landing->revision), 'groups' => config('landing.groups'),
            'library' => $this->library(), 'pendingCount' => 0,
            'videoLibrary' => MediaAsset::whereIn('mime_type', ['video/mp4', 'video/webm'])->latest()->get()
                ->map(fn (MediaAsset $asset): array => ['url' => '/media/'.$asset->id, 'name' => $asset->name])->all(),
            'videoUploadMaxBytes' => $videoUploadMaxBytes,
            'videoUploadMaxLabel' => rtrim(rtrim(number_format($videoUploadMaxBytes / 1048576, 1, '.', ''), '0'), '.').' MB',
        ]);
    }

    public function update(Request $request, LandingPage $landing): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'revision' => ['required', 'integer', 'min:1'],
            'data' => ['required', 'array'],
        ]);
        $data = $this->content->validate($request->input('data'));
        DB::transaction(function () use ($request, $landing, $data): void {
            $current = LandingPage::whereKey($landing->id)->lockForUpdate()->firstOrFail();
            $this->checkRevision($request, $current);
            $current->update([
                'name' => $request->input('name'), 'draft' => $data,
                'revision' => $current->revision + 1, 'updated_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('admin.landings.edit', $landing)->with('status', 'Borrador guardado. La versión pública cambiará cuando publiques esta landing.');
    }

    public function preview(Request $request, LandingPage $landing): Response
    {
        if ($request->isMethod('post')) {
            $request->validate(['data' => ['required', 'array']]);
            $data = $this->content->validate($request->input('data'));
        } else {
            $data = $this->content->hydrate($landing->draft);
        }

        return response()->view('pages.landing', ['landing' => $landing, 'data' => $data, 'preview' => true])
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function publish(Request $request, LandingPage $landing): RedirectResponse
    {
        $request->validate(['revision' => ['required', 'integer', 'min:1']]);
        DB::transaction(function () use ($request, $landing): void {
            $current = LandingPage::whereKey($landing->id)->lockForUpdate()->firstOrFail();
            $this->checkRevision($request, $current);
            $data = $this->content->validate($this->content->hydrate($current->draft));
            $visible = collect($data['sections'])->where('enabled', true);
            $formCount = $visible->where('type', 'lead')->count();
            if ($visible->where('type', 'hero')->count() !== 1 || $formCount > 1) {
                throw ValidationException::withMessages(['data.sections' => 'La landing debe tener una sola portada visible y como máximo un formulario de contacto visible.']);
            }
            if ($this->content->primaryButtonUrl($data) === '#contacto' && $formCount === 0) {
                throw ValidationException::withMessages(['data.sections' => 'Activa el formulario de contacto o cambia el destino del botón de la portada por un enlace a WhatsApp u otra página.']);
            }
            if ($data['hero_media'] === 'video' && blank($data['hero_video_url'])) {
                throw ValidationException::withMessages(['data.hero_video_url' => 'Sube un video o añade su enlace antes de publicar una portada de video.']);
            }
            $current->update([
                'published' => $data, 'published_at' => now(), 'revision' => $current->revision + 1,
                'updated_by' => $request->user()->id,
            ]);
        });

        return back()->with('status', 'Esta landing ya está publicada. Puedes usar su enlace en tus campañas de Facebook.');
    }

    public function unpublish(Request $request, LandingPage $landing): RedirectResponse
    {
        $request->validate(['revision' => ['required', 'integer', 'min:1']]);
        DB::transaction(function () use ($request, $landing): void {
            $current = LandingPage::whereKey($landing->id)->lockForUpdate()->firstOrFail();
            $this->checkRevision($request, $current);
            $current->update([
                'published' => null, 'published_at' => null, 'revision' => $current->revision + 1,
                'updated_by' => $request->user()->id,
            ]);
        });

        return back()->with('status', 'La landing dejó de ser pública. El borrador y los contactos se conservan.');
    }

    public function destroy(Request $request, LandingPage $landing): RedirectResponse
    {
        $request->validate([
            'revision' => ['required', 'integer', 'min:1'],
            'confirmed' => ['accepted'],
        ], ['confirmed.accepted' => 'Confirma que quieres mover esta landing a la papelera.']);

        DB::transaction(function () use ($request, $landing): void {
            $current = LandingPage::whereKey($landing->id)->lockForUpdate()->firstOrFail();
            $this->checkRevision($request, $current);
            $current->update(['revision' => $current->revision + 1, 'updated_by' => $request->user()->id]);
            $current->delete();
        });

        return redirect()->route('admin.landings.index')
            ->with('status', 'Landing movida a la papelera. Su enlace dejó de estar disponible y sus contactos se conservaron.');
    }

    public function restore(Request $request, LandingPage $landing): RedirectResponse
    {
        $request->validate(['revision' => ['required', 'integer', 'min:1']]);
        DB::transaction(function () use ($request, $landing): void {
            $current = LandingPage::onlyTrashed()->whereKey($landing->id)->lockForUpdate()->firstOrFail();
            $this->checkRevision($request, $current);
            $current->update([
                'published' => null, 'published_at' => null,
                'revision' => $current->revision + 1, 'updated_by' => $request->user()->id,
            ]);
            $current->restore();
        });

        return redirect()->route('admin.landings.edit', $landing)
            ->with('status', 'Landing restaurada como borrador. Revisa el contenido y publícala cuando esté lista.');
    }

    public function leads(LandingPage $landing): View
    {
        return view('admin.landing-leads', [
            'landing' => $landing, 'leads' => $landing->leads()->latest()->paginate(30), 'pendingCount' => 0,
        ]);
    }

    public function show(string $slug): Response
    {
        $landing = $this->published($slug);

        return response()->view('pages.landing', ['landing' => $landing, 'data' => $this->content->hydrate($landing->published), 'preview' => false])
            ->header('Cache-Control', 'private, no-store');
    }

    public function submitLead(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $landing = $this->published($slug);
        $data = $request->validate([
            'submission_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:160'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9 ()-]{7,24}$/', 'max:30'],
            'email' => ['nullable', 'email', 'max:160'],
            'event_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'message' => ['nullable', 'string', 'max:5000'],
            'consent' => ['accepted'],
            'company' => ['nullable', 'string', 'max:0'],
            'utm_source' => ['nullable', 'string', 'max:200'],
            'utm_medium' => ['nullable', 'string', 'max:200'],
            'utm_campaign' => ['nullable', 'string', 'max:200'],
            'utm_content' => ['nullable', 'string', 'max:200'],
            'utm_term' => ['nullable', 'string', 'max:200'],
            'fbclid' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Escribe tu nombre.',
            'phone.required' => 'Escribe tu teléfono para que podamos contactarte.',
            'phone.regex' => 'Escribe un teléfono válido, con código de país si corresponde.',
            'email.email' => 'Revisa la dirección de correo.',
            'consent.accepted' => 'Necesitamos tu autorización para responder a la consulta.',
            'event_date.after_or_equal' => 'Elige una fecha a partir de hoy.',
            'submission_id.uuid' => 'Recarga la página y vuelve a intentar el envío.',
        ]);
        $phone = preg_replace('/\D/', '', $data['phone']);
        if (strlen($phone) < 7 || strlen($phone) > 15) {
            throw ValidationException::withMessages(['phone' => 'Escribe un teléfono de entre 7 y 15 dígitos.']);
        }
        unset($data['consent'], $data['company']);
        $data['phone'] = $phone;
        $lead = LandingLead::firstOrCreate(['submission_id' => $data['submission_id']], [
            ...$data, 'landing_page_id' => $landing->id, 'consent_at' => now(),
        ]);
        if ($lead->landing_page_id !== $landing->id) {
            throw ValidationException::withMessages(['submission_id' => 'Este envío ya fue utilizado. Recarga la página para continuar.']);
        }
        $published = $landing->published;
        $conversion = [
            'page_id' => $landing->id, 'event_id' => $lead->submission_id,
            'tracking' => [
                'pixel_id' => $published['pixel_id'], 'lead' => (bool) $published['track_lead'],
                'value' => (float) $published['lead_value'], 'currency' => 'PEN',
            ],
        ];
        $message = $published['success_message'] ?: 'Gracias. Recibimos tu solicitud.';
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message, 'event_id' => $lead->submission_id, 'created' => $lead->wasRecentlyCreated,
                'tracking' => $conversion['tracking'],
            ]);
        }

        return redirect()->route('landing.show', $slug)->with('landing_success', $message)
            ->with('landing_conversion', $lead->wasRecentlyCreated ? $conversion : null);
    }

    private function checkRevision(Request $request, LandingPage $landing): void
    {
        if ($request->integer('revision') !== $landing->revision) {
            throw ValidationException::withMessages(['revision' => 'Esta landing cambió en otra ventana. Recarga antes de continuar para conservar esos cambios.']);
        }
    }

    private function published(string $slug): LandingPage
    {
        return LandingPage::where('slug', $slug)->whereNotNull('published')->firstOrFail();
    }

    private function videoUploadLimit(): int
    {
        $limit = 50 * 1048576;
        foreach (['upload_max_filesize', 'post_max_size'] as $setting) {
            $value = trim((string) ini_get($setting));
            if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*([KMG]?)$/i', $value, $matches) !== 1) {
                continue;
            }
            $bytes = (int) ((float) $matches[1] * match (strtoupper($matches[2])) {
                'G' => 1073741824,
                'M' => 1048576,
                'K' => 1024,
                default => 1,
            });
            if ($bytes > 0) {
                $limit = min($limit, $setting === 'post_max_size' ? max(1, $bytes - 10240) : $bytes);
            }
        }

        return $limit;
    }

    /** @return array<int, array{url: string, name: string, provider: string}> */
    private function library(): array
    {
        $originals = collect(File::allFiles(public_path('assets/images')))
            ->filter(fn (\SplFileInfo $file): bool => in_array(strtolower($file->getExtension()), ['webp', 'jpg', 'jpeg', 'png'], true))
            ->map(fn (\SplFileInfo $file): array => [
                'url' => 'assets/images/'.str_replace('\\', '/', $file->getRelativePathname()),
                'name' => $file->getFilename(), 'provider' => 'Original',
            ])->values()->all();
        $uploads = MediaAsset::where('mime_type', 'like', 'image/%')->latest()->get()->map(fn (MediaAsset $asset): array => [
            'url' => '/media/'.$asset->id, 'name' => $asset->name, 'provider' => 'Biblioteca',
        ])->all();

        return [...$uploads, ...$originals];
    }
}
