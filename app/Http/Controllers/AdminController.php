<?php

namespace App\Http\Controllers;

use App\CloudflareImages;
use App\CloudflareR2;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\User;
use App\SiteContent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        public SiteContent $site,
        public CloudflareImages $cloudflare,
        public CloudflareR2 $r2,
    ) {}

    public function login(): View|RedirectResponse
    {
        if (Auth::user()?->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login', ['setup' => false, 'hasAdmin' => User::where('is_admin', true)->exists()]);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $credentials['is_admin'] = true;
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no coinciden.']);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function setup(Request $request): View|RedirectResponse
    {
        abort_if(User::where('is_admin', true)->exists(), 403, 'El propietario ya fue configurado.');
        if ($request->isMethod('get')) {
            return view('admin.login', ['setup' => true, 'hasAdmin' => false]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);
        $user = Cache::lock('site-owner-setup', 10)->block(5, function () use ($data): User {
            abort_if(User::where('is_admin', true)->exists(), 403);
            $user = new User($data);
            $user->is_admin = true;
            $user->save();

            return $user;
        });
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('status', 'Tu estudio ya tiene un espacio de edición. Bienvenido.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', $this->shared());
    }

    public function edit(string $section): View
    {
        $definition = $this->definition($section);
        $setting = SiteSetting::where('section', $section)->first();

        return view('admin.editor', [
            ...$this->shared(), 'section' => $section, 'definition' => $definition,
            'data' => array_replace($definition['defaults'], $setting?->draft ?? []),
            'revision' => $setting?->revision ?? 0, 'library' => $this->library(),
        ]);
    }

    public function save(Request $request, string $section): RedirectResponse
    {
        $definition = $this->definition($section);
        $request->validate(['data' => ['required', 'array']]);
        $input = ['data' => $this->normalize($request->input('data', []), $definition['fields'])];
        $validated = Validator::make($input, $this->rules($definition['fields']), [
            'required' => 'Completa este campo.', 'max' => 'Este contenido supera el límite permitido.',
            'regex' => 'Ingresa un valor válido para este campo.',
        ])->validate();
        $request->validate(['revision' => ['required', 'integer', 'min:0']]);

        DB::transaction(function () use ($request, $section, $definition, $validated): void {
            $setting = SiteSetting::where('section', $section)->lockForUpdate()->first();
            if (($setting?->revision ?? 0) !== $request->integer('revision')) {
                throw ValidationException::withMessages(['revision' => 'Esta sección cambió en otra ventana. Recarga antes de guardar para no sobrescribir esos cambios.']);
            }
            SiteSetting::updateOrCreate(['section' => $section], [
                'draft' => array_replace($definition['defaults'], $setting?->draft ?? [], $validated['data']),
                'revision' => ($setting?->revision ?? 0) + 1,
                'updated_by' => $request->user()->id,
            ]);
        });

        $this->site->forget();

        return back()->with('status', 'Borrador guardado. Revísalo en la vista previa y publica cuando esté listo.');
    }

    public function publish(Request $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            foreach (SiteSetting::lockForUpdate()->get() as $setting) {
                $setting->update([
                    'published' => $setting->draft, 'published_at' => now(),
                    'revision' => $setting->revision + 1, 'updated_by' => $request->user()->id,
                ]);
            }
        });

        $this->site->forget();

        return back()->with('status', 'Los cambios guardados ya están publicados en tu sitio.');
    }

    public function discard(string $section): RedirectResponse
    {
        $definition = $this->definition($section);
        $setting = SiteSetting::where('section', $section)->first();
        $setting?->update(['draft' => $setting->published ?? $definition['defaults'], 'revision' => $setting->revision + 1]);
        $this->site->forget();

        return back()->with('status', 'El borrador volvió a la última versión publicada.');
    }

    public function reorder(Request $request, string $section): RedirectResponse
    {
        $definition = $this->definition($section);
        abort_unless($definition['page'], 404);
        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);
        DB::transaction(function () use ($section, $definition, $request): void {
            $keys = collect(config('editor.pages.'.$definition['page']))->sortBy(function (string $key): float {
                $setting = SiteSetting::where('section', $key)->first();

                return (float) ($setting?->draft['position'] ?? $this->definition($key)['defaults']['position'] ?? 0);
            })->values()->all();
            $index = array_search($section, $keys, true);
            $target = $index + ($request->input('direction') === 'up' ? -1 : 1);
            if ($target < 0 || $target >= count($keys)) {
                return;
            }
            [$keys[$index], $keys[$target]] = [$keys[$target], $keys[$index]];
            foreach ($keys as $position => $key) {
                $setting = SiteSetting::where('section', $key)->first();
                SiteSetting::updateOrCreate(['section' => $key], [
                    'draft' => array_replace($this->definition($key)['defaults'], $setting?->draft ?? [], ['position' => $position]),
                    'revision' => ($setting?->revision ?? 0) + 1, 'updated_by' => $request->user()->id,
                ]);
            }
        });

        $this->site->forget();

        return back()->with('status', 'Orden actualizado en el borrador.');
    }

    public function media(): View
    {
        return view('admin.media', [...$this->shared(), 'library' => $this->library(), 'assets' => MediaAsset::latest()->get()]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:12288', 'dimensions:max_width=10000,max_height=10000'],
            'alt' => ['nullable', 'string', 'max:200'],
        ]);
        $image = $request->file('image');
        $path = $image->store('site-media', 'local');
        abort_unless($path, 500, 'No se pudo guardar la fotografía.');
        $asset = MediaAsset::create([
            'name' => $image->getClientOriginalName(), 'alt' => $request->input('alt', '') ?? '',
            'path' => $path, 'mime_type' => $image->getMimeType(), 'size' => $image->getSize(),
        ]);
        $remoteStorage = $this->r2->configured() ? $this->r2 : $this->cloudflare;
        if ($remoteStorage->configured()) {
            try {
                $remoteStorage->upload($asset);
            } catch (\Throwable $exception) {
                report($exception);

                return back()->withErrors(['image' => 'La fotografía quedó guardada localmente. No se pudo enviar a Cloudflare; puedes reintentarlo desde su tarjeta.']);
            }
        }

        return back()->with('status', 'Fotografía guardada. Ya puedes elegirla en cualquier sección.');
    }

    public function updateMedia(Request $request, MediaAsset $asset): RedirectResponse
    {
        $validated = $request->validate(['alt' => ['nullable', 'string', 'max:200'], 'name' => ['required', 'string', 'max:200']]);
        if (array_key_exists('alt', $validated)) {
            $validated['alt'] ??= '';
        }
        $asset->update($validated);

        return back()->with('status', 'Los datos de la fotografía se actualizaron.');
    }

    public function sendToCloudflare(MediaAsset $asset): RedirectResponse
    {
        try {
            $this->cloudflare->upload($asset);
        } catch (ConnectionException $exception) {
            report($exception);

            return back()->withErrors(['image' => 'No pudimos conectar con Cloudflare. Tu fotografía local sigue disponible.']);
        }

        return back()->with('status', 'Fotografía conectada a Cloudflare. Los enlaces del sitio siguen funcionando.');
    }

    public function sendToR2(MediaAsset $asset): RedirectResponse
    {
        try {
            $this->r2->upload($asset);
        } catch (ConnectionException $exception) {
            report($exception);

            return back()->withErrors(['image' => 'No pudimos conectar con Cloudflare R2. Tu fotografía local sigue disponible.']);
        }

        return back()->with('status', 'Fotografía disponible en Cloudflare R2.');
    }

    public function integrations(): View
    {
        return view('admin.integrations', $this->shared());
    }

    /** @return array<string, mixed> */
    private function shared(): array
    {
        $settings = SiteSetting::all()->keyBy('section');

        return [
            'definitions' => $this->site->definitions(), 'settings' => $settings,
            'pendingCount' => $settings->filter(fn (SiteSetting $setting): bool => $setting->draft !== $setting->published)->count(),
            'mediaCount' => MediaAsset::count(), 'cloudflareReady' => $this->cloudflare->configured(),
            'r2Ready' => $this->r2->configured(),
            'calendlyReady' => filled($settings->get('booking')?->published['url'] ?? ''),
        ];
    }

    /** @return array<string, mixed> */
    private function definition(string $section): array
    {
        abort_unless(isset($this->site->definitions()[$section]), 404);

        return $this->site->definitions()[$section];
    }

    /** @return array<int, array<string, string>> */
    private function library(): array
    {
        $existing = collect(File::allFiles(public_path('assets/images')))->filter(
            fn (\SplFileInfo $file): bool => in_array(strtolower($file->getExtension()), ['webp', 'jpg', 'jpeg', 'png'], true)
        )->map(fn (\SplFileInfo $file): array => [
            'url' => 'assets/images/'.str_replace('\\', '/', $file->getRelativePathname()),
            'name' => $file->getFilename(), 'provider' => 'Original',
        ])->values()->all();
        $uploads = MediaAsset::latest()->get()->map(fn (MediaAsset $asset): array => [
            'url' => '/media/'.$asset->id, 'name' => $asset->name,
            'provider' => match ($asset->provider) {
                'r2' => 'Cloudflare R2',
                'cloudflare' => 'Cloudflare',
                default => 'Biblioteca',
            },
        ])->all();

        return [...$uploads, ...$existing];
    }

    /** @param array<string, mixed> $values
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function normalize(array $values, array $fields): array
    {
        foreach ($fields as $name => $field) {
            if ($field['type'] === 'lines' && is_string($values[$name] ?? null)) {
                $values[$name] = array_values(array_filter(array_map('trim', preg_split('/\R/u', $values[$name]))));
            }
            if ($field['type'] === 'repeater' && is_array($values[$name] ?? null)) {
                $values[$name] = array_values(array_map(fn (mixed $item): mixed => is_array($item) ? $this->normalize($item, $field['fields']) : $item, $values[$name]));
            }
        }

        return $values;
    }

    /** @param array<string, array<string, mixed>> $fields
     * @return array<string, array<int, mixed>>
     */
    private function rules(array $fields, string $prefix = 'data'): array
    {
        $rules = [$prefix => ['required', 'array:'.implode(',', array_keys($fields))]];
        foreach ($fields as $name => $field) {
            $key = $prefix.'.'.$name;
            $rules[$key] = match ($field['type']) {
                'boolean' => ['required', 'boolean'],
                'number' => ['required', 'integer', 'min:'.$field['min'], 'max:'.$field['max']],
                'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
                'font' => ['required', Rule::in(array_keys(config('editor.fonts')))],
                'select' => ['required', Rule::in(array_keys($field['options']))],
                'email' => ['required', 'email', 'max:160'],
                'url' => ['nullable', 'url:https', 'max:500'],
                'calendly' => ['nullable', 'regex:#^https://calendly\.com/[a-zA-Z0-9][a-zA-Z0-9/_-]*$#', 'max:300'],
                'link' => ['nullable', 'regex:#^(?:/(?!/)[a-zA-Z0-9/_?=&.%-]*|\#[a-zA-Z0-9_-]+|https://[^\s<>"\x27]+)$#', 'max:500'],
                'image' => ['nullable', 'regex:#^(?:/?assets/images/[a-zA-Z0-9/_.-]+|/media/[0-9]+)$#', 'max:500'],
                'lines' => ['required', 'array', 'min:1', 'max:30'],
                'repeater' => ['required', 'array', 'min:'.$field['min'], 'max:'.$field['max']],
                default => ['nullable', 'string', 'max:'.($field['type'] === 'textarea' ? 5000 : 500)],
            };
            if ($field['type'] === 'lines') {
                $rules[$key.'.*'] = ['required', 'string', 'max:2000'];
            }
            if ($field['type'] === 'repeater') {
                $rules = [...$rules, ...$this->rules($field['fields'], $key.'.*')];
            }
        }

        return $rules;
    }
}
