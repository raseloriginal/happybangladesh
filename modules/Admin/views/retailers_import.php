<?php $pageTitle = 'Import Retailers'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Import Retailers</h1>
    <div class="breadcrumb">Admin &rsaquo; Import Retailers</div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Import Form Card -->
  <div class="card lg:col-span-2">
    <div class="card-header bg-slate-50 border-b border-slate-100 flex items-center justify-between">
      <h2 class="card-title text-slate-800"><i class="fas fa-file-import mr-2"></i> CSV Import Configuration</h2>
    </div>
    <div class="card-body">
      <form method="POST" action="<?= url('admin/retailers/import') ?>" enctype="multipart/form-data">
        <?= Helpers::csrfField() ?>


        <div class="form-group mb-6">
          <label class="form-label" for="csv_file">Upload CSV File <span class="required">*</span></label>
          <input type="file" id="csv_file" name="csv_file" accept=".csv" required class="form-input py-2">
          <p class="text-xs text-gray-500 mt-1">Please select a valid <code>.csv</code> file containing the retailer data.</p>
        </div>

        <div class="flex gap-3">
          <button type="submit" class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 font-bold rounded-lg shadow-md transition active:scale-[0.98]">
            <i class="fas fa-upload mr-2"></i> Import Retailer Data
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Format Guidelines Card -->
  <div class="card">
    <div class="card-header bg-slate-50 border-b border-slate-100">
      <h2 class="card-title text-slate-800"><i class="fas fa-info-circle mr-2"></i> CSV Format Guidelines</h2>
    </div>
    <div class="card-body text-sm text-gray-600 space-y-4">
      <p>
        Your CSV file must include a header row with matching column names. Columns can be in any order. The system will look for column headers like:
      </p>
      
      <ul class="list-disc pl-5 space-y-1.5 text-xs text-gray-700">
        <li><strong>Name</strong> / <strong>Store Name</strong> (Required)</li>
        <li><strong>Phone</strong> / <strong>Number</strong> (Optional)</li>
        <li><strong>Lat</strong> / <strong>Latitude</strong> (Optional)</li>
        <li><strong>Lng</strong> / <strong>Longitude</strong> (Optional)</li>
      </ul>

      <div class="border-t border-slate-100 pt-3">
        <h4 class="font-bold text-gray-800 mb-2">Example Template:</h4>
        <div class="overflow-x-auto bg-gray-50 rounded-md border border-gray-200">
          <table class="min-w-full text-xs text-left text-gray-500">
            <thead class="bg-gray-100 text-gray-700 font-semibold border-b border-gray-200">
              <tr>
                <th class="px-2 py-1.5">Name</th>
                <th class="px-2 py-1.5">Phone</th>
                <th class="px-2 py-1.5">Lat</th>
                <th class="px-2 py-1.5">Lng</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr>
                <td class="px-2 py-1.5 text-gray-850 font-medium">আশিক</td>
                <td class="px-2 py-1.5">0170583027</td>
                <td class="px-2 py-1.5">23.912012</td>
                <td class="px-2 py-1.5">90.281786</td>
              </tr>
              <tr>
                <td class="px-2 py-1.5 text-gray-850 font-medium">রঞ্জন স্টোর হা</td>
                <td class="px-2 py-1.5">0173030303</td>
                <td class="px-2 py-1.5">24.334164</td>
                <td class="px-2 py-1.5">88.750777</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
