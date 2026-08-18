# Skill: Inertia controllers & pages

## Controller — thin, returns props only
```php
public function show(Request $request, string $slug): Response
{
    $page = Page::published()->whereSlug($slug, app()->getLocale())->firstOrFail();

    return Inertia::render('Pages/Show', [
        'page'   => PageResource::make($page),
        'blocks' => BlockResolver::resolve($page->blocks),
    ]);
}
```
No queries in the controller beyond retrieval. No output formatting — that is the Resource.

## Shared props
```php
// HandleInertiaRequests::share()
return [
    'locale'    => app()->getLocale(),
    'direction' => $this->direction(),
    'locales'   => fn () => Locale::active()->get(['code','native_name','direction']),
    'menus'     => fn () => MenuResource::collection($this->menus()),
    'settings'  => fn () => SettingsResource::make(app(GeneralSettings::class)),
    'auth'      => fn () => ['user' => $this->authUser($request)],
    'flash'     => fn () => ['success' => session('success')],
];
```
Wrap expensive props in closures — Inertia skips them on partial reloads.

## Forms — no API, no fetch
```tsx
const { data, setData, post, errors, processing } = useForm({
  name: '', email: '', cv: null as File | null,
});

const submit = (e: React.FormEvent) => {
  e.preventDefault();
  post('/careers/senior-developer/apply', { forceFormData: true });
};
```
Laravel validation errors arrive in `errors` automatically. No manual error handling.

## Permission checks — presentation only
```tsx
<Can do="applications.export"><button onClick={exportCsv}>Export</button></Can>
```
This hides a button. It does not protect anything. Every action needs a server policy.

## Never
- `fetch()` or `axios` to your own backend
- Business logic in a page component
- Trusting `auth.user.permissions` for anything but rendering
