<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

/**
 * Artisan generator:
 *   php artisan make:vue "Staff Clearence"
 *   → resources/js/vue/StaffClearence.vue
 */
class MakeVuePage extends Command
{
    protected $signature   = 'make:vue {name : Page title}';
    protected $description = 'Scaffold a Keen Bootstrap 5 Vue 3 page (table + modal CRUD)';

    public function handle(): int
    {
        $title      = $this->argument('name');           
        $slug       = Str::slug($title, '-');            
        $component  = Str::studly($slug);             
        $path       = resource_path("js/vue/{$component}.vue");

        if (file_exists($path)) {
            $this->error("❌  {$path} already exists");
            return self::FAILURE;
        }

        (new Filesystem)->ensureDirectoryExists(dirname($path));
        file_put_contents($path, $this->compileStub($title, $slug));

        $this->info("✅ Vue page created: {$path}");
        return self::SUCCESS;
    }

    protected function compileStub(string $title, string $slug): string
    {
        return str_replace(['__TITLE__', '__SLUG__'], [$title, $slug], $this->stub());
    }

    /* --------------------------------------------------------------------- */
    /*  Vue page stub – Keen Bootstrap 5 classes kept exactly as requested   */
    /* --------------------------------------------------------------------- */
    protected function stub(): string
    {
        return <<<'VUE'
<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useMutation }        from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'



function buildPageItems(totalPages, current, delta = 1) {
  const range = [];
  const left = Math.max(2, current - delta);
  const right = Math.min(totalPages - 1, current + delta);

  range.push(1);
  if (left > 2) range.push('...');
  for (let i = left; i <= right; i++) range.push(i);
  if (right < totalPages - 1) range.push('...');
  if (totalPages > 1) range.push(totalPages);

  return range;
}

const pageItems = computed(() => {
  const tp = totalPages.value;
  const cp = currentPage.value;
  if (tp <= 6) return Array.from({ length: tp }, (_, i) => i + 1);
  return buildPageItems(tp, cp, 1);
});


/* ––– Filters ––– */
const filters = { start_date: ref(''), end_date: ref('') }
const startDateFilter = computed({
  get: () => filters.start_date.value, set: v => (filters.start_date.value = v)
})
const endDateFilter = computed({
  get: () => filters.end_date.value,   set: v => (filters.end_date.value = v)
})

/* ––– Pagination query ––– */
const {
  data, isLoading, refetch, currentPage, pageSize, searchQuery, total,
  handlePageChange, handleSizeChange, exportToExcel
} = usePaginationQuery({
  apiUrl        : '/api/__SLUG__',   // ← point to your endpoint
  queryKeyPrefix: '__SLUG__',
  filters
})
const totalPages = computed(() => Math.ceil(total.value / pageSize.value))

/* ––– Select‑all ––– */
const selectedIds = ref([])
const rows        = computed(() => data.value?.data ?? [])

const allSelected = computed(() =>
  rows.value.length && rows.value.every(r => selectedIds.value.includes(String(r.id)))
)
function toggleSelectAll () {
  selectedIds.value = allSelected.value ? [] : rows.value.map(r => String(r.id))
}
watch(rows, () => (selectedIds.value = []))

/* ––– Modal + form ––– */
const form         = reactive({ name: '', note: '' })   /* extend as needed */
const isEditing    = ref(false)
const editId       = ref(null)
const modalLoading = ref(false)

function resetForm () { Object.assign(form, { name: '', note: '' }) }

function openModal () {
  isEditing.value = false; editId.value = null; resetForm()
  bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').show()
}
function showEditModal (item) {
  isEditing.value = true; editId.value = item.id; Object.assign(form, { ...item })
  bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').show()
}

/* ––– Save (create / update) ––– */
const { mutate: saveItem } = useMutation({
  mutationFn: p => editId.value
    ? axios.put(`/api/__SLUG__/${editId.value}`, p)
    : axios.post('/api/__SLUG__', p),
  onMutate  : () => (modalLoading.value = true),
  onSuccess : r => {
    Swal.fire('Success', r.data.message ?? 'Saved', 'success')
    refetch(); bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').hide()
  },
  onError   : e => Swal.fire('Error', e.response?.data?.message ?? e.message, 'error'),
  onSettled : () => (modalLoading.value = false)
})
function handleSubmit () { saveItem({ ...form }) }
</script>

<template>
  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">

      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">
        __TITLE__
      </h1>

      <div class="card">
        <!-- ========== Card header ========== -->
        <div class="card-header border-0 pt-6">
          <!-- search -->
          <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
              <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                <span class="path1"></span><span class="path2"></span>
              </i>
              <input
                v-model="searchQuery"
                type="text"
                class="form-control form-control-solid w-250px ps-12"
                placeholder="Type to Search ..."
              />
            </div>
          </div>

          <!-- actions -->
          <div class="card-toolbar">
            <div class="d-flex justify-content-end">
              <!-- filter btn -->
              <button
                class="btn btn-light-primary me-3"
                data-kt-menu-trigger="click"
                data-kt-menu-placement="bottom-end"
              >
                <i class="ki-duotone ki-filter fs-2">
                  <span class="path1"></span><span class="path2"></span>
                </i>Filter
              </button>

              <!-- filter dropdown -->
              <div
                id="kt-toolbar-filter"
                class="menu menu-sub menu-sub-dropdown w-300px w-md-325px"
                data-kt-menu="true"
              >
                <div class="px-7 py-5">
                  <div class="fs-4 text-gray-900 fw-bold">Filter Options</div>
                </div>
                <div class="separator border-gray-200"></div>
                <div class="px-7 py-5">
                  <div class="mb-10">
                    <label class="form-label fs-5 fw-semibold mb-3">Month:</label>
                    <select
                      class="form-select form-select-solid fw-bold"
                      data-placeholder="Select option"
                    >
                      <option></option>
                      <option value="01">January</option>
                      <option value="02">February</option>
                    </select>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button
                      class="btn btn-primary"
                      data-kt-menu-dismiss="true"
                      @click="refetch"
                    >Apply</button>
                  </div>
                </div>
              </div>

              <!-- export + add -->
              <button class="btn btn-light-primary me-3" @click="exportToExcel">
                <i class="ki-duotone ki-exit-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                Export
              </button>
              <button class="btn btn-primary" @click="openModal">+ Add</button>
            </div>
          </div>
        </div>

        <!-- total badge -->
        <div class="ps-8 pt-4">
          <span class="badge badge-light-secondary">Total: {{ total }}</span>
        </div>

        <!-- ========== Card body / table ========== -->
        <div class="card-body pt-0">
          <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
              <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                  <th class="w-10px pe-2">
                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                      <input
                        type="checkbox"
                        class="form-check-input"
                        :checked="allSelected"
                        @change="toggleSelectAll"
                      />
                    </div>
                  </th>
                  <th>Name</th>
                  <th>Note</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>

              <tbody class="fw-semibold text-gray-600">
                <tr v-if="isLoading">
                  <td :colspan="4" class="text-center py-10">
                    <div class="spinner-border text-primary me-2"></div>
                    Loading data, please wait...
                  </td>
                </tr>

                <tr v-for="row in rows" :key="row.id">
                  <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                      <input
                        type="checkbox"
                        class="form-check-input"
                        :value="String(row.id)"
                        v-model="selectedIds"
                      />
                    </div>
                  </td>
                  <td>{{ row.name ?? 'N/A' }}</td>
                  <td>{{ row.note ?? '—' }}</td>
                  <td class="text-end">
                    <button
                      class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                      @click="showEditModal(row)"
                    >
                      <i class="ki-duotone ki-pencil fs-2">
                        <span class="path1"></span><span class="path2"></span>
                      </i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- pagination -->
            <div class="d-flex justify-content-between align-items-center mt-5 flex-wrap gap-3">
              <div>
                <label class="me-2 fw-semibold">Page size:</label>
                <select
                  v-model="pageSize"
                  @change="handleSizeChange(parseInt($event.target.value))"
                  class="form-select form-select-sm w-auto d-inline-block"
                >
                  <option value="10">10</option><option value="25">25</option>
                  <option value="50">50</option><option value="100">100</option>
                </select>
              </div>

        <ul class="pagination mb-0">
              <!-- Previous -->
              <li :class="['page-item previous', { disabled: currentPage === 1 }]">
                <a href="javascript:;" class="page-link" @click="handlePageChange(Math.max(1, currentPage - 1))">
                  <i class="previous"></i>
                </a>
              </li>

              <!-- Dynamic Pages -->
              <li v-for="it in pageItems" :key="`p-${it}`"
                  :class="['page-item', { active: it === currentPage, disabled: it === '...' }]">
                <a v-if="it !== '...'" href="javascript:;" class="page-link" @click="handlePageChange(it)">{{ it }}</a>
                <a v-else href="javascript:;" class="page-link">…</a>
              </li>

              <!-- Next -->
              <li :class="['page-item next', { disabled: currentPage === totalPages }]">
                <a href="javascript:;" class="page-link" @click="handlePageChange(Math.min(totalPages, currentPage + 1))">
                  <i class="next"></i>
                </a>
              </li>
            </ul>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== Modal ========== -->
  <div class="modal fade" id="kt_modal_crud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">{{ isEditing ? 'Edit' : 'Add' }} Item</h3>
          <button class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
          </button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <input v-model="form.name" type="text" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Note</label>
              <input v-model="form.note" type="text" class="form-control" />
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button
            class="btn btn-primary"
            :disabled="modalLoading"
            @click="handleSubmit"
          >
            <span v-if="modalLoading" class="spinner-border spinner-border-sm"></span>
            {{ isEditing ? 'Update' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Page‑specific tweaks (optional) */
</style>
VUE;
    }
}
