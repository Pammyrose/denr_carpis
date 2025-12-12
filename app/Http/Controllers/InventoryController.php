<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index()
    {
        $assets = Inventory::all()->map(function ($item) {
            $assignedTo = $item->assigned ? Employee::find($item->assigned) : null;
            $fullName = $assignedTo 
                ? trim("{$assignedTo->first_name} {$assignedTo->middle_name} {$assignedTo->last_name}")
                : 'Unassigned';
            
            $imageUrl = $item->image ? Storage::url($item->image) : null;
            Log::info('Asset Processing', [
                'asset_id' => $item->asset_id,
                'assigned' => $item->assigned,
                'full_name' => $fullName,
                'image' => $imageUrl,
            ]);

            return [
                'id' => $item->asset_id,
                'name' => $item->name,
                'category' => $item->category,
                'location' => $item->location,
                'purchase_date' => $item->purchase_date,
                'value' => $item->value,
                'condition' => $item->condition,
                'assigned_to' => $fullName,
                'employee_id' => $item->assigned,
                'assigned_to' => $item->assigned_to_name,
                'status' => $item->status,
                'property_no' => $item->property_no,
                'serial_no' => $item->serial_no,
                'serviceable' => $item->serviceable,
                'unserviceable' => $item->unserviceable,
                'coa_representative' => $item->coa_representative,
                'coa_date' => $item->coa_date,
                'assigned_date' => $item->assigned_date,
                'unit_qty' => $item->unit_qty,
                'return_date' => $item->return_date,
                'image' => $imageUrl,
            ];
        })->toArray();

        $employees = Employee::all()->map(function ($employee) {
            $fullName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");
            return [
                'id' => $employee->id,
                'full_name' => $fullName ?: 'Unnamed Employee',
            ];
        })->toArray();

        Log::info('Employees Fetched', ['employees' => $employees]);

        return Inertia::render('inventory/Index', [
            'assets' => $assets,
            'employees' => $employees,
        ]);
    }

    public function create()
    {
        $employees = Employee::all()->map(function ($employee) {
            $fullName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");
            return [
                'id' => $employee->id,
                'full_name' => $fullName ?: 'Unnamed Employee',
            ];
        })->toArray();

        Log::info('Employees for Create Form', ['employees' => $employees]);

        return Inertia::render('inventory/Create', [
            'employees' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Check if the request is for archiving an inventory item
            if ($request->has('archive_inventory_id')) {
                $inventory = Inventory::where('asset_id', $request->archive_inventory_id)->first();
                if (!$inventory) {
                    Log::error('InventoryController: Inventory not found for archiving', [
                        'archive_inventory_id' => $request->archive_inventory_id,
                    ]);
                    return back()->withErrors(['general' => 'Inventory item not found']);
                }
                DB::beginTransaction();
    
                $archivedData = [
                    'id' => $inventory->id,
                    'asset_id' => $inventory->asset_id,
                    'name' => $inventory->name,
                    'category' => $inventory->category,
                    'location' => $inventory->location,
                    'purchase_date' => $inventory->purchase_date,
                    'value' => $inventory->value,
                    'condition' => $inventory->condition,
                    'assigned' => $inventory->assigned,
                    'status' => $inventory->status,
                    'property_no' => $inventory->property_no,
                    'serial_no' => $inventory->serial_no,
                    'serviceable' => $inventory->serviceable,
                    'unserviceable' => $inventory->unserviceable,
                    'coa_representative' => $inventory->coa_representative,
                    'coa_date' => $inventory->coa_date,
                    'assigned_date' => $inventory->assigned_date,
                    'unit_qty' => $inventory->unit_qty,
                    'return_date' => $inventory->return_date,
                    'image' => $inventory->image,
                    'archived_at' => now()->toDateTimeString(),
                ];
    
                $filePath = 'archived_inventory.json';
                $archivedInventory = Storage::disk('local')->exists($filePath)
                    ? json_decode(Storage::disk('local')->get($filePath), true)
                    : [];
                $archivedInventory[$inventory->asset_id] = $archivedData;
    
                if (!Storage::disk('local')->put($filePath, json_encode($archivedInventory, JSON_PRETTY_PRINT))) {
                    Log::error('InventoryController: Failed to write to archived_inventory.json', [
                        'path' => $filePath,
                        'data' => $archivedData,
                    ]);
                    throw new \Exception('Failed to write to archived inventory file');
                }
    
                Log::info('InventoryController: Archiving inventory to storage', $archivedData);
    
                $inventory->delete();
                DB::commit();
    
                Log::info('InventoryController: Inventory archived via store', [
                    'inventory_id' => $inventory->asset_id,
                ]);
    
                return redirect()->route('inventory.index')->with('message', 'Inventory archived successfully');
            }

            // Handle creation of new inventory item
            DB::beginTransaction();

            // Server-side validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'location' => 'required|string|in:PMD,Finance,Admin,Legal,CDD,SAM,LPD,Enforcement,Technical,MSD',
                'purchase_date' => 'required|date',
                'value' => 'required|numeric|min:0',
                'condition' => 'required|string|in:New,Old',
                'status' => 'required|string|in:Good,Check,Repair,Upgrade',
                'property_no' => 'required|string|max:255',
                'serial_no' => 'required|string|max:255',
                'serviceable' => 'required|string|max:255',
                'unserviceable' => 'required|string|max:255',
                'coa_representative' => 'required|string|max:255',
                'coa_date' => 'required|date',
                'assigned_date' => 'required|date',
                'unit_qty' => 'required|integer|min:1',
                'assigned' => 'nullable|exists:employees,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            ]);

            if ($validator->fails()) {
                Log::warning('InventoryController: Validation failed', [
                    'errors' => $validator->errors()->all(),
                    'input' => $request->all(),
                ]);
                return back()->withErrors($validator->errors())->withInput();
            }

            // Prepare data for storage
            $data = $request->only([
                'name',
                'category',
                'location',
                'purchase_date',
                'value',
                'condition',
                'status',
                'property_no',
                'serial_no',
                'serviceable',
                'unserviceable',
                'coa_representative',
                'coa_date',
                'assigned_date',
                'unit_qty',
                'assigned',
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                try {
                    $path = $request->file('image')->store('inventory', 'public');
                    $data['image'] = $path;
                    Log::info('InventoryController: Image stored successfully', [
                        'path' => $path,
                        'url' => Storage::url($path),
                    ]);
                } catch (\Exception $e) {
                    Log::error('InventoryController: Failed to store image', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return back()->withErrors(['image' => 'Failed to store image'])->withInput();
                }
            }

            // Generate unique asset_id (e.g., based on property_no or timestamp)
            $data['asset_id'] = 'A-' . strtoupper(uniqid()); // Example: INV-5F7A3B2C1D

            // Create inventory item
            $inventory = Inventory::create($data);

            DB::commit();

            Log::info('InventoryController: Inventory item created', [
                'asset_id' => $inventory->asset_id,
                'data' => $data,
            ]);

            return redirect()->route('inventory.index')->with('message', 'Inventory item created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('InventoryController: Failed to process store request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
            ]);
            return back()->withErrors(['general' => 'Failed to create inventory item: ' . $e->getMessage()])->withInput();
        }
    }

    public function archived()
{
    try {
        $filePath = 'archived_inventory.json';
        if (!Storage::disk('local')->exists($filePath)) {
            Log::warning('InventoryController: Archived inventory file does not exist', ['path' => $filePath]);
            return Inertia::render('inventory/Archived', [
                'archivedInventory' => [],
                'error' => 'No archived inventory found. Archive file does not exist.',
            ]);
        }

        $fileContents = Storage::disk('local')->get($filePath);
        if (empty($fileContents)) {
            Log::warning('InventoryController: Archived inventory file is empty', ['path' => $filePath]);
            return Inertia::render('inventory/Archived', [
                'archivedInventory' => [],
                'error' => 'No archived inventory found. Archive file is empty.',
            ]);
        }

        $archivedInventory = json_decode($fileContents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('InventoryController: Failed to parse archived_inventory.json', [
                'path' => $filePath,
                'json_error' => json_last_error_msg(),
            ]);
            return Inertia::render('inventory/Archived', [
                'archivedInventory' => [],
                'error' => 'Failed to parse archived inventory: ' . json_last_error_msg(),
            ]);
        }

        $formattedInventory = array_map(function ($item) {
            $assignedTo = isset($item['assigned']) && $item['assigned'] ? Employee::find($item['assigned']) : null;
            $fullName = $assignedTo 
                ? trim("{$assignedTo->first_name} {$assignedTo->middle_name} {$assignedTo->last_name}")
                : 'Unassigned';
            return [
                'id' => $item['asset_id'],
                'asset_id' => $item['asset_id'],
                'name' => $item['name'] ?? 'N/A',
                'category' => $item['category'] ?? 'N/A',
                'location' => $item['location'] ?? 'N/A',
                'purchase_date' => $item['purchase_date'] ?? 'N/A',
                'value' => $item['value'] ?? 0,
                'condition' => $item['condition'] ?? 'N/A',
                'assigned_to' => $item['assigned_to_name'] ?? 'Unassigned',
                'employee_id' => isset($item['assigned']) ? $item['assigned'] : null,
                'status' => $item['status'] ?? 'N/A',
                'property_no' => $item['property_no'] ?? 'N/A',
                'serial_no' => $item['serial_no'] ?? 'N/A',
                'serviceable' => $item['serviceable'] ?? 'N/A',
                'unserviceable' => $item['unserviceable'] ?? 'N/A',
                'coa_representative' => $item['coa_representative'] ?? 'N/A',
                'coa_date' => $item['coa_date'] ?? 'N/A',
                'assigned_date' => $item['assigned_date'] ?? 'N/A',
                'unit_qty' => $item['unit_qty'] ?? 0,
                'return_date' => $item['return_date'] ?? null,
                'image' => isset($item['image']) && $item['image'] ? Storage::url($item['image']) : null,
                'archived_at' => $item['archived_at'] ?? 'N/A',
            ];
        }, array_values($archivedInventory));

        Log::info('InventoryController: Fetched archived inventory from storage', [
            'inventory_count' => count($formattedInventory),
            'data' => $formattedInventory,
        ]);

        return Inertia::render('inventory/Archived', [
            'archivedInventory' => $formattedInventory,
            'error' => null,
        ]);
    } catch (\Exception $e) {
        Log::error('InventoryController: Failed to fetch archived inventory', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return Inertia::render('inventory/Archived', [
            'archivedInventory' => [],
            'error' => 'Failed to load archived inventory: ' . $e->getMessage(),
        ]);
    }
}

public function unarchive(Request $request, $archivedInventoryId)
    {
        try {
            DB::beginTransaction();

            // Retrieve archived inventory from storage
            $filePath = 'archived_inventory.json';
            $archivedInventory = Storage::disk('local')->exists($filePath)
                ? json_decode(Storage::disk('local')->get($filePath), true)
                : [];
            $archivedItem = $archivedInventory[$archivedInventoryId] ?? null;

            if (!$archivedItem) {
                throw new \Exception('Archived inventory not found.');
            }

            // Validate that the asset_id is not already in use
            if (Inventory::where('asset_id', $archivedItem['asset_id'])->exists()) {
                throw new \Exception('Asset ID already exists in the inventory table.');
            }

            // Restore inventory to inventory table
            $inventory = Inventory::create([
                'id' => $archivedItem['id'],
                'asset_id' => $archivedItem['asset_id'],
                'name' => $archivedItem['name'],
                'category' => $archivedItem['category'],
                'location' => $archivedItem['location'],
                'purchase_date' => $archivedItem['purchase_date'],
                'value' => $archivedItem['value'],
                'condition' => $archivedItem['condition'],
                'assigned' => $archivedItem['assigned'],
                'status' => $archivedItem['status'],
                'property_no' => $archivedItem['property_no'],
                'serial_no' => $archivedItem['serial_no'],
                'serviceable' => $archivedItem['serviceable'],
                'unserviceable' => $archivedItem['unserviceable'],
                'coa_representative' => $archivedItem['coa_representative'],
                'coa_date' => $archivedItem['coa_date'],
                'assigned_date' => $archivedItem['assigned_date'],
                'unit_qty' => $archivedItem['unit_qty'],
                'return_date' => $archivedItem['return_date'],
                'image' => $archivedItem['image'],
            ]);

            $inventory->update([
    'assigned_to_name' => $archivedItem['assigned_to_name'] ?? null,
]);

            // Remove from storage
            unset($archivedInventory[$archivedInventoryId]);
            Storage::disk('local')->put($filePath, json_encode($archivedInventory, JSON_PRETTY_PRINT));

            DB::commit();

            Log::info('InventoryController: Inventory unarchived', [
                'archived_inventory_id' => $archivedInventoryId,
                'inventory_id' => $inventory->asset_id,
            ]);

            return redirect()->route('inventory.index')->with('message', "Inventory ID {$inventory->asset_id} unarchived successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('InventoryController: Failed to unarchive inventory', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to unarchive inventory: ' . $e->getMessage()]);
        }
    }

    public function edit($asset_id)
    {
        try {
            $inventory = Inventory::where('asset_id', $asset_id)->firstOrFail();
            $employees = Employee::all()->map(function ($employee) {
                $fullName = trim(
                    implode(' ', array_filter([
                        $employee->first_name,
                        $employee->middle_name,
                        $employee->last_name,
                    ], fn($value) => !is_null($value) && $value !== ''))
                ) ?: 'Unnamed Employee';
                return [
                    'id' => $employee->id,
                    'full_name' => $fullName,
                ];
            })->toArray();

            $assignedTo = $inventory->assigned ? Employee::find($inventory->assigned) : null;
            $fullName = $assignedTo 
                ? trim(
                    implode(' ', array_filter([
                        $assignedTo->first_name,
                        $assignedTo->middle_name,
                        $assignedTo->last_name,
                    ], fn($value) => !is_null($value) && $value !== ''))
                ) ?: 'Unnamed Employee'
                : 'Unassigned';

            $item = [
                'id' => $inventory->asset_id,
                'name' => $inventory->name ?? 'Unknown Item',
                'category' => $inventory->category ?? 'Uncategorized',
                'location' => $inventory->location ?? 'Unknown Location',
                'purchase_date' => $inventory->purchase_date ?? '',
                'value' => (float) ($inventory->value ?? 0),
                'condition' => $inventory->condition ?? 'New',
                'property_no' => $inventory->property_no ?? '',
                'serial_no' => $inventory->serial_no ?? '',
                'serviceable' => (float) ($inventory->serviceable ?? 0),
                'unserviceable' => (float) ($inventory->unserviceable ?? 0),
                'coa_representative' => $inventory->coa_representative ?? '',
                'coa_date' => $inventory->coa_date ?? '',
                'assigned' => $inventory->assigned !== null ? (int)$inventory->assigned : null,
                'assigned_to' => $inventory->assigned !== null ? (int)$inventory->assigned : null,
                'full_name' => $fullName,
                'assigned_date' => $inventory->assigned_date ?? '',
                'unit_qty' => (int) ($inventory->unit_qty ?? 1),
                'status' => $inventory->status ?? 'Good',
                'return_date' => $inventory->return_date ?? '',
                'image' => $inventory->image ? Storage::url($inventory->image) : null,
            ];

            Log::info('InventoryController@edit: Inventory item and employees for edit form', [
                'asset_id' => $asset_id,
                'item' => $item,
                'assigned' => $item['assigned'],
                'assigned_to' => $item['assigned_to'],
                'full_name' => $fullName,
                'employees' => $employees,
            ]);

            return Inertia::render('inventory/Edit', [
                'item' => $item,
                'employees' => $employees,
            ]);
        } catch (\Exception $e) {
            Log::error('InventoryController@edit: Failed to fetch inventory item', [
                'asset_id' => $asset_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Inertia::render('inventory/Edit', [
                'item' => [
                    'id' => $asset_id,
                    'name' => 'Unknown Item',
                    'category' => 'Uncategorized',
                    'location' => 'Unknown Location',
                    'purchase_date' => '',
                    'value' => 0,
                    'condition' => 'New',
                    'property_no' => '',
                    'serial_no' => '',
                    'serviceable' => 0,
                    'unserviceable' => 0,
                    'coa_representative' => '',
                    'coa_date' => '',
                    'assigned' => null,
                    'assigned_to' => null,
                    'full_name' => 'Unassigned',
                    'assigned_date' => '',
                    'unit_qty' => 1,
                    'status' => 'Good',
                    'return_date' => '',
                    'image' => null,
                ],
                'employees' => Employee::all()->map(function ($employee) {
                    $fullName = trim(
                        implode(' ', array_filter([
                            $employee->first_name,
                            $employee->middle_name,
                            $employee->last_name,
                        ], fn($value) => !is_null($value) && $value !== ''))
                    ) ?: 'Unnamed Employee';
                    return [
                        'id' => $employee->id,
                        'full_name' => $fullName,
                    ];
                })->toArray(),
            ]);
        }
    }

    public function update(Request $request, $asset_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'value' => 'required|numeric|min:0|max:9999999.99',
            'condition' => 'required|string|in:New,Old',
            'assigned' => 'nullable|integer|exists:employees,id',
            'property_no' => 'required|string|max:255',
            'serial_no' => 'required|string|max:255',
            'serviceable' => 'required|numeric|min:0|max:9999999.99',
            'unserviceable' => 'required|numeric|min:0|max:9999999.99',
            'coa_representative' => 'required|string|max:255',
            'coa_date' => 'required|date',
            'assigned_date' => 'required|date',
            'unit_qty' => 'required|integer|min:0|max:9999999',
            'status' => 'required|string|in:Good,Check,Repair,Upgrade',
            'return_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            Log::warning('InventoryController@update: Validation failed', [
                'asset_id' => $asset_id,
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all(),
            ]);

            $assignedTo = $request->input('assigned') ? Employee::find($request->input('assigned')) : null;
            $fullName = $assignedTo 
                ? trim(
                    implode(' ', array_filter([
                        $assignedTo->first_name,
                        $assignedTo->middle_name,
                        $assignedTo->last_name,
                    ], fn($value) => !is_null($value) && $value !== ''))
                ) ?: 'Unnamed Employee'
                : 'Unassigned';

            return Inertia::render('inventory/Edit', [
                'item' => [
                    'id' => $asset_id,
                    'name' => $request->input('name', 'Unknown Item'),
                    'category' => $request->input('category', 'Uncategorized'),
                    'location' => $request->input('location', 'Unknown Location'),
                    'purchase_date' => $request->input('purchase_date', ''),
                    'value' => (float) $request->input('value', 0),
                    'condition' => $request->input('condition', 'New'),
                    'property_no' => $request->input('property_no', ''),
                    'serial_no' => $request->input('serial_no', ''),
                    'serviceable' => (float) $request->input('serviceable', 0),
                    'unserviceable' => (float) $request->input('unserviceable', 0),
                    'coa_representative' => $request->input('coa_representative', ''),
                    'coa_date' => $request->input('coa_date', ''),
                    'assigned' => $request->input('assigned', null),
                    'assigned_to' => $request->input('assigned', null),
                    'full_name' => $fullName,
                    'assigned_date' => $request->input('assigned_date', ''),
                    'unit_qty' => (int) $request->input('unit_qty', 1),
                    'status' => $request->input('status', 'Good'),
                    'return_date' => $request->input('return_date', ''),
                    'image' => $request->input('image', null),
                ],
                'employees' => Employee::all()->map(function ($employee) {
                    $fullName = trim(
                        implode(' ', array_filter([
                            $employee->first_name,
                            $employee->middle_name,
                            $employee->last_name,
                        ], fn($value) => !is_null($value) && $value !== ''))
                    ) ?: 'Unnamed Employee';
                    return [
                        'id' => $employee->id,
                        'full_name' => $fullName,
                    ];
                })->toArray(),
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        try {
            DB::beginTransaction();

            $inventory = Inventory::where('asset_id', $asset_id)->firstOrFail();
            $data = $request->all();
            if ($request->has('assigned_to')) {
                $data['assigned'] = $request->input('assigned_to') !== '' ? (int)$request->input('assigned_to') : null;
                unset($data['assigned_to']);
            }

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($inventory->image) {
                    Storage::disk('public')->delete($inventory->image);
                }
                $file = $request->file('image');
                $path = $file->store('assets', 'public');
                if (!Storage::disk('public')->exists($path)) {
                    Log::error('Image storage failed', ['path' => $path]);
                    DB::rollBack();
                    return Inertia::render('inventory/Edit', [
                        'item' => [
                            'id' => $asset_id,
                            'name' => $request->input('name', 'Unknown Item'),
                            'category' => $request->input('category', 'Uncategorized'),
                            'location' => $request->input('location', 'Unknown Location'),
                            'purchase_date' => $request->input('purchase_date', ''),
                            'value' => (float) $request->input('value', 0),
                            'condition' => $request->input('condition', 'New'),
                            'property_no' => $request->input('property_no', ''),
                            'serial_no' => $request->input('serial_no', ''),
                            'serviceable' => (float) $request->input('serviceable', 0),
                            'unserviceable' => (float) $request->input('unserviceable', 0),
                            'coa_representative' => $request->input('coa_representative', ''),
                            'coa_date' => $request->input('coa_date', ''),
                            'assigned' => $request->input('assigned', null),
                            'assigned_to' => $request->input('assigned', null),
                            'full_name' => $fullName,
                            'assigned_date' => $request->input('assigned_date', ''),
                            'unit_qty' => (int) $request->input('unit_qty', 1),
                            'status' => $request->input('status', 'Good'),
                            'return_date' => $request->input('return_date', ''),
                            'image' => null,
                        ],
                        'employees' => Employee::all()->map(function ($employee) {
                            $fullName = trim(
                                implode(' ', array_filter([
                                    $employee->first_name,
                                    $employee->middle_name,
                                    $employee->last_name,
                                ], fn($value) => !is_null($value) && $value !== ''))
                            ) ?: 'Unnamed Employee';
                            return [
                                'id' => $employee->id,
                                'full_name' => $fullName,
                            ];
                        })->toArray(),
                        'errors' => ['image' => 'Failed to store image'],
                    ]);
                }
                $data['image'] = $path;
                Log::info('Image stored successfully', ['path' => $path, 'url' => Storage::url($path)]);
            }

            $inventory->update([
                'name' => $data['name'],
                'category' => $data['category'],
                'location' => $data['location'],
                'purchase_date' => $data['purchase_date'],
                'value' => $data['value'],
                'condition' => $data['condition'],
                'property_no' => $data['property_no'],
                'serial_no' => $data['serial_no'],
                'serviceable' => $data['serviceable'],
                'unserviceable' => $data['unserviceable'],
                'coa_representative' => $data['coa_representative'],
                'coa_date' => $data['coa_date'],
                'assigned' => $data['assigned'],
                'assigned_date' => $data['assigned_date'],
                'unit_qty' => $data['unit_qty'],
                'status' => $data['status'],
                'return_date' => $data['return_date'],
                'image' => $data['image'] ?? $inventory->image,
            ]);

            DB::commit();

            Log::info('InventoryController@update: Inventory item updated', [
                'asset_id' => $asset_id,
                'data' => $data,
            ]);

            return redirect()->route('inventory.index')->with('message', 'Inventory item updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('InventoryController@update: Failed to update inventory item', [
                'asset_id' => $asset_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            $assignedTo = $request->input('assigned') ? Employee::find($request->input('assigned')) : null;
            $fullName = $assignedTo 
                ? trim(
                    implode(' ', array_filter([
                        $assignedTo->first_name,
                        $assignedTo->middle_name,
                        $assignedTo->last_name,
                    ], fn($value) => !is_null($value) && $value !== ''))
                ) ?: 'Unnamed Employee'
                : 'Unassigned';
            return Inertia::render('inventory/Edit', [
                'item' => [
                    'id' => $asset_id,
                    'name' => $request->input('name', 'Unknown Item'),
                    'category' => $request->input('category', 'Uncategorized'),
                    'location' => $request->input('location', 'Unknown Location'),
                    'purchase_date' => $request->input('purchase_date', ''),
                    'value' => (float) $request->input('value', 0),
                    'condition' => $request->input('condition', 'New'),
                    'property_no' => $request->input('property_no', ''),
                    'serial_no' => $request->input('serial_no', ''),
                    'serviceable' => (float) $request->input('serviceable', 0),
                    'unserviceable' => (float) $request->input('unserviceable', 0),
                    'coa_representative' => $request->input('coa_representative', ''),
                    'coa_date' => $request->input('coa_date', ''),
                    'assigned' => $request->input('assigned', null),
                    'assigned_to' => $request->input('assigned', null),
                    'full_name' => $fullName,
                    'assigned_date' => $request->input('assigned_date', ''),
                    'unit_qty' => (int) $request->input('unit_qty', 1),
                    'status' => $request->input('status', 'Good'),
                    'return_date' => $request->input('return_date', ''),
                    'image' => $inventory->image ? Storage::url($inventory->image) : null,
                ],
                'employees' => Employee::all()->map(function ($employee) {
                    $fullName = trim(
                        implode(' ', array_filter([
                            $employee->first_name,
                            $employee->middle_name,
                            $employee->last_name,
                        ], fn($value) => !is_null($value) && $value !== ''))
                    ) ?: 'Unnamed Employee';
                    return [
                        'id' => $employee->id,
                        'full_name' => $fullName,
                    ];
                })->toArray(),
                'errors' => ['general' => 'Failed to update inventory item: ' . $e->getMessage()],
            ]);
        }
    }

    public function destroy(Inventory $inventory)
    {
        try {
            DB::beginTransaction();

            if ($inventory->image) {
                Storage::disk('public')->delete($inventory->image);
            }

            $inventory->delete();
            Log::info('InventoryController@destroy: Asset deleted: ' . json_encode($inventory->toArray()));

            DB::commit();
            return redirect()->route('inventory.index')->with('message', 'Asset deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in InventoryController@destroy: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return back()->withErrors(['general' => 'Failed to delete asset: ' . $e->getMessage()]);
        }
    }

public function view(Request $request, $inventory)
{
    Log::info('View Method Called', ['inventory_param' => $inventory]);

    $inventoryModel = Inventory::where('asset_id', $inventory)->first();
    Log::info('Inventory Query', [
        'asset_id' => $inventory,
        'result' => $inventoryModel ? $inventoryModel->toArray() : null
    ]);

    if (!$inventoryModel) {
        Log::error('Inventory not found', ['asset_id' => $inventory]);
        return Inertia::render('inventory/View', [
            'asset' => null,
            'error' => 'Asset not found for ID: ' . $inventory
        ]);
    }

    // Assigned name
    $assignedTo = $inventoryModel->assigned
        ? Employee::find($inventoryModel->assigned)
        : null;

    $fullName = $assignedTo
        ? trim("{$assignedTo->first_name} {$assignedTo->middle_name} {$assignedTo->last_name}")
        : 'Unassigned';

    // Prefer stored assigned_to_name if present
    if ($inventoryModel->assigned_to_name) {
        $fullName = $inventoryModel->assigned_to_name;
    }

    // --------------------------------------------
    // FIXED QR URL (NO DUPLICATE etams_2025/public)
    // --------------------------------------------
    $viewUrl = url("inventory/tag/" . $inventoryModel->asset_id);

    Log::info('QR Code URL', ['url' => $viewUrl]);

    try {
        $qrCode = new QrCode($viewUrl);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $qrCodeBase64 =
            'data:image/png;base64,' . base64_encode($result->getString());

        Log::info('Viewing Asset', [
            'asset_id' => $inventoryModel->asset_id,
            'assigned_to' => $inventoryModel->assigned_to_name,
            'full_name' => $fullName,
            'image' => $inventoryModel->image ? Storage::url($inventoryModel->image) : null,
        ]);
    } catch (\Exception $e) {
        Log::error('QR Code Generation Failed in View', [
            'asset_id' => $inventoryModel->asset_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $qrCodeBase64 = null;
    }

    // Assemble asset data
    $assetData = [
        'id' => (string) $inventoryModel->asset_id,
        'name' => (string) $inventoryModel->name,
        'category' => (string) $inventoryModel->category,
        'location' => (string) $inventoryModel->location,
        'purchase_date' => (string) $inventoryModel->purchase_date,
        'value' => (float) $inventoryModel->value,
        'condition' => (string) $inventoryModel->condition,
        'assigned_to' => (string) $fullName,
        'employee_id' => $inventoryModel->assigned ? (int)$inventoryModel->assigned : null,
        'status' => (string) $inventoryModel->status,
        'property_no' => (string) $inventoryModel->property_no,
        'serial_no' => (string) $inventoryModel->serial_no,
        'serviceable' => (string) $inventoryModel->serviceable,
        'unserviceable' => (string) $inventoryModel->unserviceable,
        'coa_representative' => (string) $inventoryModel->coa_representative,
        'coa_date' => (string) $inventoryModel->coa_date,
        'assigned_date' => (string) $inventoryModel->assigned_date,
        'unit_qty' => (float) $inventoryModel->unit_qty,
        'qr_code' => $qrCodeBase64,
        'image' => $inventoryModel->image ? asset(Storage::url($inventoryModel->image)) : null,
    ];

    Log::info('View Asset Data', ['asset' => $assetData]);

    return Inertia::render('inventory/View', [
        'asset' => $assetData,
        'error' => null
    ]);
}


    public function tag(Request $request, $inventory)
    {
        Log::info('Tag Method Called', ['inventory_param' => $inventory]);
    
        $inventoryModel = Inventory::where('asset_id', $inventory)->first();
        Log::info('Inventory Query', ['asset_id' => $inventory, 'result' => $inventoryModel ? $inventoryModel->toArray() : null]);
    
        if (!$inventoryModel) {
            Log::error('Inventory not found', ['asset_id' => $inventory]);
            return Inertia::render('inventory/AssetTag', [
                'asset' => null,
                'error' => 'Asset not found for ID: ' . $inventory
            ]);
        }
    
        $fullName = $inventoryModel->assigned_to_name;
        $assignedTo = $inventoryModel->assigned ? Employee::find($inventoryModel->assigned) : null;
        $fullName = $assignedTo 
            ? trim("{$assignedTo->first_name} {$assignedTo->middle_name} {$assignedTo->last_name}")
            : 'Unassigned';
    
        $tagUrl = $request->getSchemeAndHttpHost() . route(
            'inventory.tag',
            ['inventory' => $inventoryModel->asset_id],
            false
        );
    
        Log::info('QR Code URL', ['url' => $tagUrl]);
    
        try {
            $qrCode = new QrCode($tagUrl);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($result->getString());
            Log::info('Viewing Asset Tag', [
                'asset_id' => $inventoryModel->asset_id,
                'assigned' => $inventoryModel->assigned,
                'full_name' => $fullName,
                'image' => $inventoryModel->image ? Storage::url($inventoryModel->image) : null,
            ]);
        } catch (\Exception $e) {
            Log::error('QR Code Generation Failed in Tag', [
                'asset_id' => $inventoryModel->asset_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $qrCodeBase64 = null;
        }
    
        $assetData = [
            'id' => (string) $inventoryModel->asset_id,
            'name' => (string) $inventoryModel->name,
            'category' => (string) $inventoryModel->category,
            'location' => (string) $inventoryModel->location,
            'purchase_date' => (string) $inventoryModel->purchase_date,
            'value' => (float) $inventoryModel->value,
            'condition' => (string) $inventoryModel->condition,
            'assigned_to' => (string) $fullName,
            'employee_id' => $inventoryModel->assigned ? (int)$inventoryModel->assigned : null,
            'status' => (string) $inventoryModel->status,
            'property_no' => (string) $inventoryModel->property_no,
            'serial_no' => (string) $inventoryModel->serial_no,
            'serviceable' => (string) $inventoryModel->serviceable,
            'unserviceable' => (string) $inventoryModel->unserviceable,
            'coa_representative' => (string) $inventoryModel->coa_representative,
            'coa_date' => (string) $inventoryModel->coa_date,
            'assigned_date' => (string) $inventoryModel->assigned_date,
            'unit_qty' => (float) $inventoryModel->unit_qty,
            'qr_code' => $qrCodeBase64,
           'image' => $inventoryModel->image ? asset(Storage::url($inventoryModel->image)) : null,
        ];
    
        Log::info('Tag Asset Data', ['asset' => $assetData]);
    
        return Inertia::render('inventory/AssetTag', [
            'asset' => $assetData,
            'error' => null
        ]);
    }
}