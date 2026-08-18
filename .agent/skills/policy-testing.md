# Skill: Policies & authorisation tests

## Roles
`ceo` · `manager` (department-scoped) · `hr` · `it`

## Permission naming
`{resource}.{action}` — e.g. `applications.view`, `applications.viewPii`, `applications.move`

`applications.view` (counts, stages, scores) is SEPARATE from `applications.viewPii`
(names, email, phone, CV). IT has the first, never the second.

## Policy template
```php
class JobApplicationPolicy
{
    public function view(User $u, JobApplication $a): bool
    {
        return $u->can('applications.view') && $this->inScope($u, $a);
    }

    public function viewPii(User $u, JobApplication $a): bool
    {
        return $u->can('applications.viewPii') && $this->inScope($u, $a);
    }

    private function inScope(User $u, JobApplication $a): bool
    {
        if ($u->hasAnyRole(['ceo', 'hr'])) return true;

        if ($u->hasRole('manager')) {
            return $u->managedDepartments()
                     ->whereKey($a->jobPosting->department_id)->exists();
        }

        return false;
    }
}
```

## Redaction in the API Resource
```php
$canSeePii = $request->user()->can('viewPii', $this->resource);

'first_name' => $canSeePii ? $this->first_name : null,
'cv_url'     => $canSeePii ? $this->signedCvUrl() : null,
'redacted'   => ! $canSeePii,
```
Blocking the route is not enough. The data must not reach the response.

## Test pattern — one per matrix cell
```php
it('denies a manager another department\'s application', function () {
    $manager = User::factory()->manager(department: 'engineering')->create();
    $app     = JobApplication::factory()->forDepartment('design')->create();

    actingAs($manager)->get("/admin/job-applications/{$app->id}")->assertForbidden();
});

it('redacts candidate names from IT', function () {
    $it = User::factory()->it()->create();
    JobApplication::factory()->create(['first_name' => 'Layla']);

    actingAs($it)->get('/admin/job-applications')
        ->assertDontSee('Layla')->assertSee('Candidate #');
});
```

Target: 100% coverage of authorisation paths. Every denial in the matrix is a test.
