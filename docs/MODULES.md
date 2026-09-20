# Adding a module

A walkthrough, written against the code rather than from memory. Following it
builds a working `Notes` module: a note belongs to a user, has a title and a
body, and is listed and created through both the API and a screen.

Nothing in `app/Core` is edited at any point. The only file outside the module
that changes is one line in `config/modules.php`.

I wrote this, then followed it to build a module, and fixed whatever did not
match. If a step here disagrees with the code, the step is wrong.

---

## 1. The directory

Every module is the same shape, so start by making it.

```bash
mkdir -p app/Modules/Notes/{Contracts,Data,Http/{Controllers/{Api,Web},Requests},Models,Policies,Presenters,Processors,Providers,Repositories,Resources/views,Database/{Migrations,Factories},routes,Tests/{Unit,Feature,Support}}
```

Directories you do not need can be left out. The base provider looks for each
one and skips what is absent.

## 2. The provider

This is the only class the application requires a module to have. It declares
what the module binds, and nothing else: routes, migrations, views and config
are discovered from the directory.

`app/Modules/Notes/Providers/NotesServiceProvider.php`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Notes\Providers;

use App\Core\Module\ModuleServiceProvider;
use App\Modules\Notes\Contracts\NoteRepository;
use App\Modules\Notes\Repositories\EloquentNoteRepository;

final class NotesServiceProvider extends ModuleServiceProvider
{
    protected function bindings(): array
    {
        return [
            NoteRepository::class => EloquentNoteRepository::class,
        ];
    }

    protected function permissions(): array
    {
        return [
            'notes.view' => 'View notes',
            'notes.create' => 'Write notes',
        ];
    }
}
```

The name is derived from the class: `NotesServiceProvider` is the `Notes`
module, its views are namespaced `notes::`, and its config file would be
`config/notes.php` inside the module.

## 3. Switch it on

`config/modules.php` is the single switchboard.

```php
'enabled' => [
    UsersServiceProvider::class,
    AccessServiceProvider::class,
    AuthServiceProvider::class,
    NotesServiceProvider::class,   // <- added
],
```

An entry that does not extend `ModuleServiceProvider` fails at boot with a
message naming the class, rather than being quietly ignored.

## 4. Schema and model

The migration lives in the module and is deleted with it.

`app/Modules/Notes/Database/Migrations/2026_04_01_000000_create_notes_table.php`

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->timestamps();

            $table->index(['author_id', 'created_at'], 'notes_author_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
```

Two things worth copying: the index is named explicitly, and `down()` is real.

The foreign key names a table, not a class. That is the allowed kind of
cross-module dependency, and it is what the boundary test permits: no file in
`app/Modules/Notes` imports anything from `App\Modules\Users`.

The model goes in the module too, with a factory beside it. The factory
deliberately does not supply `author_id`: doing so would mean calling
`User::factory()`, which is an import from another module, and a factory is
production-side code as far as the boundary test is concerned. Tests pass the
author in, which reads better anyway:

```php
Note::factory()->create(['author_id' => $author->id]);
```

```php
#[Fillable(['author_id', 'title', 'body'])]
final class Note extends Model
{
    /** @use HasFactory<NoteFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return NoteFactory::new();
    }
}
```

## 5. The contract and the repository

State the persistence the module needs as an interface, then implement it
once with Eloquent.

```php
interface NoteRepository
{
    public function findById(int $id): ?Note;

    /** @return Collection<int, Note> */
    public function recent(int $limit = 25): Collection;

    public function create(WriteNote $data): Note;
}
```

The interface exists so a processor can be unit tested against a fake, and so
that fake can be proven equivalent by a contract test. It is not about
swapping databases.

## 6. The DTO and the processor

The DTO carries no framework types:

```php
final readonly class WriteNote
{
    public function __construct(
        public int $authorId,
        public string $title,
        public string $body,
    ) {}
}
```

The processor is one use case, with no HTTP in it:

```php
/** @implements Processor<WriteNote, Note> */
final readonly class CreateNote implements Processor
{
    public function __construct(
        private NoteRepository $notes,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): Note
    {
        $note = $this->notes->create($input);

        $this->audit->record('notes.created', 'note', $note->id, ['title' => $note->title]);

        return $note;
    }
}
```

If it needs to refuse, it throws a `DomainException` subclass. One renderer in
`bootstrap/app.php` turns that into the error envelope for a JSON caller and a
redirect carrying the message for a browser.

## 7. Presenter, request, controller, route

The presenter is explicit. Adding a column never leaks it into a response.

```php
/** @implements Presenter<Note> */
final readonly class NotePresenter implements Presenter
{
    public function present(mixed $subject): array
    {
        return [
            'id' => $subject->id,
            'title' => $subject->title,
            'body' => $subject->body,
            'created_at' => $subject->created_at?->toAtomString(),
        ];
    }
}
```

The form request is the only class that knows request key names, and it hands
a DTO onwards:

```php
public function toData(): WriteNote
{
    return new WriteNote(
        authorId: ActorId::required($this->user()),
        title: trim($this->string('title')->value()),
        body: trim($this->string('body')->value()),
    );
}
```

One invokable controller per endpoint:

```php
public function __invoke(WriteNoteRequest $request): JsonResponse
{
    return $this->created($this->presenter->present($this->processor->process($request->toData())));
}
```

And the routes. Coarse gates are route middleware; per-record decisions are a
policy.

`app/Modules/Notes/routes/api.php`

```php
Route::prefix('notes')->name('api.notes.')->group(function (): void {
    Route::get('/', ListNotesController::class)->middleware('permission:notes.view')->name('index');
    Route::post('/', CreateNoteController::class)->middleware('permission:notes.create')->name('store');
});
```

**Route names are namespaced by channel.** The screens own `notes.*`, so the
JSON endpoints own `api.notes.*`. Skipping this makes `route('notes.store')`
ambiguous, and it silently resolves to whichever was registered last.

## 8. Permissions

They were declared in step 2. Project them into storage:

```bash
php artisan access:sync-permissions
```

The rollout runs this after migrating, so deploying a module projects its new
permissions automatically. The projection is additive: pruning what no module
declares any more is `--prune`, and it is separate because removing a
permission takes it away from every role that had it. Existing roles do not
receive new permissions on deploy; somebody has to grant them.

Permissions originate in code. There is no endpoint that creates one, so a
permission that exists in the database but nowhere in the source cannot
happen.

A module that declares a permission another module already declares fails at
boot, naming both.

## 9. Tests

Module tests live in the module. Two kinds are worth writing immediately.

A unit test against a fake, with no application booted:

`RecordingAuditTrail` and `GrantsAllAccess` live in `tests/Support`, at the
application level rather than inside a module: they stand in for core
contracts, so every module's tests need them and none should have to reach
into another module to get one.

```php
final class CreateNoteTest extends TestCase   // PHPUnit's, not Tests\TestCase
{
    #[Test]
    public function it_records_what_it_wrote(): void
    {
        $notes = new InMemoryNoteRepository;
        $audit = new RecordingAuditTrail;

        $note = (new CreateNote($notes, $audit))->process(new WriteNote(1, 'Title', 'Body'));

        $this->assertSame('Title', $note->title);
        $this->assertSame('notes.created', $audit->entries[0]['action']);
    }
}
```

A feature test through the real stack, swapping the core's `AccessChecker` for
a permissive double so the module's tests never mention roles:

```php
protected function setUp(): void
{
    parent::setUp();

    $this->app->instance(AccessChecker::class, new GrantsAllAccess);
    $this->actingAs(User::factory()->create());
}
```

That double lives in `tests/Support` and works because the contract is owned
by the core rather than by the authorization module. Feature tests may
compose modules; production code may not, and the boundary test enforces
exactly that split.

Run them with the wildcard suites already configured:

```bash
composer test
```

No PHPUnit configuration changes: `phpunit.xml` globs
`app/Modules/*/Tests/Unit` and `app/Modules/*/Tests/Feature`.

## 10. A screen

Views live in `Resources/views` and are namespaced by the module name in
lowercase, so `view('notes::index')` resolves. Extend the application shell:

```blade
@extends('layouts.app')

@section('content')
    <x-page title="Notes"/>
    ...
@endsection
```

The navigation entry is declared by the module, not written into a shared
view. Add it to the provider:

```php
protected function navigation(): array
{
    return [
        new NavigationItem(label: 'Notes', route: 'notes.index', permission: 'notes.view', order: 30),
    ];
}
```

The core collects these the same way it collects permissions and renders
whatever survives two filters: the route has to exist, and the caller has to
hold the permission. That is what keeps the promise at the top of this page
literally true, and it is why switching a module off leaves the layout intact
instead of leaving a dead link behind.

## Deleting a module

```bash
rm -rf app/Modules/Notes
# remove its line from config/modules.php
composer test
```

The suite should still pass. If it does not, something outside the module
depended on it, and the failure names what.

This works for a module nothing depends on. `Users` is the base module here:
`Access` and `Auth` both hold foreign keys into its table, so removing it is
not a supported operation. That is a property of the dependency graph, not of
the framework, and it is visible in the schema rather than hidden in imports.

## What the build enforces

`tests/Feature/Core/ArchitectureTest.php` fails when:

- anything in `app/Core` references a module,
- a processor imports `Illuminate\Http`,
- a module's **production** code reaches into another module outside its
  `Contracts`, `Data`, `Enums` or `Events`,
- `config/modules.php` and `app/Modules` disagree about which modules exist.

## Checklist

- [ ] Provider extends `ModuleServiceProvider` and is listed in `config/modules.php`
- [ ] Migration is named, reversible and expand-only
- [ ] Persistence sits behind an interface
- [ ] Processors throw domain exceptions and return domain objects
- [ ] Form requests produce DTOs; nothing downstream knows a request key
- [ ] Presenters list fields explicitly
- [ ] Route names namespaced by channel: `notes.*` and `api.notes.*`
- [ ] Permissions declared in the provider, synced by the command
- [ ] Navigation entry declared in the provider, not in a shared view
- [ ] Tests live in the module; unit tests boot no application
- [ ] `composer check` is green
