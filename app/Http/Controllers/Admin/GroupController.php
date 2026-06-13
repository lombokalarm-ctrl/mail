<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\EmailMaintenanceService;
use App\Services\InboxImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));

        $groups = Group::query()
            ->withCount('inboxes')
            ->with([
                'inboxes' => fn ($query) => $query
                    ->withCount('emails')
                    ->orderBy('inbox_name'),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('viewer_token', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('inboxes', function ($inboxQuery) use ($search): void {
                            $inboxQuery
                                ->where('inbox_name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.groups.index', [
            'groups' => $groups,
            'groupOptions' => Group::query()->orderBy('name')->get(['id', 'name']),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'viewer_token' => ['required', 'string', 'max:64', 'alpha_num', Rule::unique('groups', 'viewer_token')],
            'status' => ['required', 'string', 'max:50'],
        ]);

        Group::query()->create([
            'name' => $data['name'],
            'viewer_token' => strtolower($data['viewer_token']),
            'status' => $data['status'],
        ]);

        return back()->with('status', 'Group berhasil dibuat.');
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'viewer_token' => ['required', 'string', 'max:64', 'alpha_num', Rule::unique('groups', 'viewer_token')->ignore($group->id)],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $group->update([
            'name' => $data['name'],
            'viewer_token' => strtolower($data['viewer_token']),
            'status' => $data['status'],
        ]);

        return back()->with('status', 'Group berhasil diperbarui.');
    }

    public function destroy(Group $group, EmailMaintenanceService $maintenance): RedirectResponse
    {
        $maintenance->deleteGroup($group);

        return back()->with('status', 'Group berhasil dihapus.');
    }

    public function importInboxes(Request $request, InboxImportService $importService): RedirectResponse
    {
        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'import_file' => [
                'required',
                'file',
                'max:10240',
                function (string $attribute, $value, $fail): void {
                    $extension = strtolower($value->getClientOriginalExtension());

                    if (! in_array($extension, ['csv', 'txt', 'xlsx'], true)) {
                        $fail('Format file tidak didukung. Gunakan CSV atau XLSX.');
                    }
                },
            ],
        ]);

        $group = Group::query()->findOrFail($data['group_id']);

        try {
            $report = $importService->import($group, $request->file('import_file'));
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'import_file' => $exception->getMessage(),
            ])->withInput();
        }

        $status = "{$report['created']} inbox berhasil diimport ke group {$group->name}.";

        if (count($report['skipped']) > 0) {
            $status .= ' '.count($report['skipped']).' baris dilewati.';
        }

        return back()
            ->with('status', $status)
            ->with('import_report', $report);
    }
}
