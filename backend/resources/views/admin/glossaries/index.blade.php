@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-[#00D02B]">
                    Admin Panel
                </p>
                <h1 class="mt-2 text-3xl font-black text-[#050505]" style="font-family: 'Orbitron', sans-serif;">
                    Glossary
                </h1>
                <p class="mt-2 text-sm text-[#64748B]">
                    Manage glossary terms, definitions, and categories.
                </p>
            </div>

            <button
                type="button"
                data-open-add
                class="inline-flex items-center justify-center rounded-xl bg-[#00D02B] px-5 py-3 text-sm font-extrabold uppercase tracking-[0.15em] text-black shadow-sm transition hover:bg-[#00e830] active:scale-[0.98]"
            >
                Add Term
            </button>
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Please fix the following errors:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
            <div class="border-b border-[#E2E8F0] px-5 py-4">
                <h2 class="text-base font-bold text-[#050505]">All Glossary Terms</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[#F8FAFC]">
                        <tr>
                            <th class="px-5 py-4 text-left font-semibold text-[#475569]">Term</th>
                            <th class="px-5 py-4 text-left font-semibold text-[#475569]">Category</th>
                            <th class="px-5 py-4 text-left font-semibold text-[#475569]">Slug</th>
                            <th class="px-5 py-4 text-left font-semibold text-[#475569]">Order</th>
                            <th class="px-5 py-4 text-left font-semibold text-[#475569]">Status</th>
                            <th class="px-5 py-4 text-right font-semibold text-[#475569]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($glossaries as $item)
                            <tr class="hover:bg-[#FAFCFF]">
                                <td class="px-5 py-4 align-top">
                                    <div class="font-semibold text-[#050505]">{{ $item->term }}</div>
                                    <div class="mt-1 max-w-md text-xs leading-relaxed text-[#64748B] line-clamp-2">
                                        {{ $item->definition }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 align-top text-[#334155]">
                                    {{ $item->category }}
                                </td>

                                <td class="px-5 py-4 align-top text-[#64748B]">
                                    {{ $item->slug }}
                                </td>

                                <td class="px-5 py-4 align-top text-[#334155]">
                                    {{ $item->sort_order }}
                                </td>

                                <td class="px-5 py-4 align-top">
                                    @if($item->is_active)
                                        <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 align-top">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            data-open-edit
                                            data-id="{{ $item->id }}"
                                            data-term="{{ $item->term }}"
                                            data-category="{{ $item->category }}"
                                            data-definition="{{ $item->definition }}"
                                            data-sort-order="{{ $item->sort_order }}"
                                            data-is-active="{{ $item->is_active ? 1 : 0 }}"
                                            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold uppercase tracking-wide text-blue-700 transition hover:bg-blue-100"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            data-open-delete
                                            data-id="{{ $item->id }}"
                                            data-term="{{ $item->term }}"
                                            class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold uppercase tracking-wide text-red-700 transition hover:bg-red-100"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <p class="text-base font-semibold text-[#050505]">No glossary terms found.</p>
                                    <p class="mt-2 text-sm text-[#64748B]">Click “Add Term” to create your first glossary entry.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($glossaries, 'links'))
                <div class="border-t border-[#E2E8F0] px-5 py-4">
                    {{ $glossaries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-6">
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#E2E8F0] px-6 py-4">
            <div>
                <h2 class="text-xl font-bold text-[#050505]">Add Glossary Term</h2>
                <p class="mt-1 text-sm text-[#64748B]">Create a new glossary entry.</p>
            </div>
            <button type="button" data-close-modal="addModal" class="text-2xl leading-none text-[#94A3B8] hover:text-[#050505]">
                &times;
            </button>
        </div>

        <form method="POST" action="{{ route('admin.glossaries.store') }}" class="space-y-5 px-6 py-6">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#050505]">Term</label>
                <input
                    type="text"
                    name="term"
                    value="{{ old('glossary_modal') === 'add' ? old('term') : '' }}"
                    class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] placeholder:text-[#94A3B8] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
                    placeholder="Enter glossary term"
                    required
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#050505]">Category</label>
                <input
                    type="text"
                    name="category"
                    value="{{ old('glossary_modal') === 'add' ? old('category') : '' }}"
                    class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] placeholder:text-[#94A3B8] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
                    placeholder="Example: Xbox Features"
                    required
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#050505]">Definition</label>
                <textarea
                    name="definition"
                    rows="5"
                    class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] placeholder:text-[#94A3B8] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
                    placeholder="Enter glossary definition"
                    required
                >{{ old('glossary_modal') === 'add' ? old('definition') : '' }}</textarea>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#050505]">Sort Order</label>
                <input
                    type="number"
                    name="sort_order"
                    min="0"
                    value="{{ old('glossary_modal') === 'add' ? old('sort_order', 0) : 0 }}"
                    class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
                >
            </div>

            <div class="flex items-center gap-3">
                <input
                    id="add_is_active"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('glossary_modal') === 'add' ? (old('is_active', true) ? 'checked' : '') : 'checked' }}
                    class="rounded border-[#CBD5E1] text-[#00D02B] focus:ring-[#00D02B]"
                >
                <label for="add_is_active" class="text-sm font-medium text-[#475569]">Active</label>
            </div>

            <input type="hidden" name="glossary_modal" value="add">

            <div class="flex justify-end gap-3 border-t border-[#E2E8F0] pt-5">
                <button type="button" data-close-modal="addModal" class="rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm font-semibold text-[#475569] transition hover:bg-[#F8FAFC]">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-[#00D02B] px-5 py-2.5 text-sm font-extrabold uppercase tracking-[0.12em] text-black transition hover:bg-[#00e830]">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-6">
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#E2E8F0] px-6 py-4">
            <div>
                <h2 class="text-xl font-bold text-[#050505]">Edit Glossary Term</h2>
                <p class="mt-1 text-sm text-[#64748B]">Update the selected glossary entry.</p>
            </div>
            <button type="button" data-close-modal="editModal" class="text-2xl leading-none text-[#94A3B8] hover:text-[#050505]">
                &times;
            </button>
        </div>

        <form id="editForm" method="POST" action="" class="space-y-5 px-6 py-6">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#050505]">Term</label>
                <input
                    id="edit_term"
                    type="text"
                    name="term"
                    class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] placeholder:text-[#94A3B8] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
                    required
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#050505]">Category</label>
                <input
                    id="edit_category"
                    type="text"
                    name="category"
                    class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] placeholder:text-[#94A3B8] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
                    required
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#050505]">Definition</label>
                <textarea
                    id="edit_definition"
                    name="definition"
                    rows="5"
                    class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] placeholder:text-[#94A3B8] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
                    required
                ></textarea>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#050505]">Sort Order</label>
                <input
                    id="edit_sort_order"
                    type="number"
                    name="sort_order"
                    min="0"
                    class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
                >
            </div>

            <div class="flex items-center gap-3">
                <input
                    id="edit_is_active"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="rounded border-[#CBD5E1] text-[#00D02B] focus:ring-[#00D02B]"
                >
                <label for="edit_is_active" class="text-sm font-medium text-[#475569]">Active</label>
            </div>

            <input type="hidden" name="glossary_modal" value="edit">
            <input type="hidden" name="glossary_id" id="edit_glossary_id">

            <div class="flex justify-end gap-3 border-t border-[#E2E8F0] pt-5">
                <button type="button" data-close-modal="editModal" class="rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm font-semibold text-[#475569] transition hover:bg-[#F8FAFC]">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-[#00D02B] px-5 py-2.5 text-sm font-extrabold uppercase tracking-[0.12em] text-black transition hover:bg-[#00e830]">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Modal --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-6">
    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-2xl">
        <div class="border-b border-[#E2E8F0] px-6 py-4">
            <h2 class="text-xl font-bold text-[#050505]">Delete Glossary Term</h2>
            <p class="mt-1 text-sm text-[#64748B]">This action cannot be undone.</p>
        </div>

        <div class="px-6 py-6">
            <p class="text-sm text-[#475569]">
                Are you sure you want to delete
                <span id="delete_term_name" class="font-bold text-[#050505]"></span>?
            </p>

            <form id="deleteForm" method="POST" action="" class="mt-6 flex justify-end gap-3">
                @csrf
                @method('DELETE')

                <button type="button" data-close-modal="deleteModal" class="rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm font-semibold text-[#475569] transition hover:bg-[#F8FAFC]">
                    Cancel
                </button>

                <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-extrabold uppercase tracking-[0.12em] text-white transition hover:bg-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    const deleteModal = document.getElementById('deleteModal');

    const editForm = document.getElementById('editForm');
    const deleteForm = document.getElementById('deleteForm');

    const editTerm = document.getElementById('edit_term');
    const editCategory = document.getElementById('edit_category');
    const editDefinition = document.getElementById('edit_definition');
    const editSortOrder = document.getElementById('edit_sort_order');
    const editIsActive = document.getElementById('edit_is_active');
    const editGlossaryId = document.getElementById('edit_glossary_id');

    const deleteTermName = document.getElementById('delete_term_name');

    const glossaryBaseUrl = @json(url('/admin/glossaries'));

    function openModal(modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal(modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    document.querySelector('[data-open-add]')?.addEventListener('click', () => {
        openModal(addModal);
    });

    document.querySelectorAll('[data-close-modal]').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-close-modal');
            const modal = document.getElementById(modalId);
            if (modal) closeModal(modal);
        });
    });

    document.querySelectorAll('[data-open-edit]').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            editForm.action = `${glossaryBaseUrl}/${id}`;
            editGlossaryId.value = id;
            editTerm.value = button.dataset.term ?? '';
            editCategory.value = button.dataset.category ?? '';
            editDefinition.value = button.dataset.definition ?? '';
            editSortOrder.value = button.dataset.sortOrder ?? 0;
            editIsActive.checked = (button.dataset.isActive === '1');

            openModal(editModal);
        });
    });

    document.querySelectorAll('[data-open-delete]').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            const term = button.dataset.term ?? '';

            deleteForm.action = `${glossaryBaseUrl}/${id}`;
            deleteTermName.textContent = term;

            openModal(deleteModal);
        });
    });

    [addModal, editModal, deleteModal].forEach(modal => {
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal(addModal);
            closeModal(editModal);
            closeModal(deleteModal);
        }
    });

    @if ($errors->any() && old('glossary_modal') === 'add')
        openModal(addModal);
    @endif

    @if ($errors->any() && old('glossary_modal') === 'edit')
        editForm.action = `${glossaryBaseUrl}/{{ old('glossary_id') }}`;
        editGlossaryId.value = `{{ old('glossary_id') }}`;
        editTerm.value = @json(old('term'));
        editCategory.value = @json(old('category'));
        editDefinition.value = @json(old('definition'));
        editSortOrder.value = @json(old('sort_order', 0));
        editIsActive.checked = {{ old('is_active') ? 'true' : 'false' }};
        openModal(editModal);
    @endif
</script>
@endsection