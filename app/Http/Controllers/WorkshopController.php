<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class WorkshopController extends Controller
{
    public function dashboard(): View
    {
        $stats = ['active' => DB::table('service_orders')->whereNotIn('status', ['Entregado', 'Cancelado'])->count(), 'today' => DB::table('service_orders')->whereDate('received_at', today())->count(), 'ready' => DB::table('service_orders')->where('status', 'Listo para entrega')->count(), 'customers' => DB::table('customers')->count()];
        $orders = DB::table('service_orders')->join('customers','customers.id','=','service_orders.customer_id')->select('service_orders.*','customers.name as customer')->latest('received_at')->limit(7)->get();
        return view('workshop', compact('stats','orders'))->with('page', 'dashboard');
    }
    public function reception(): View { return view('workshop', ['page'=>'reception']); }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['customer_name'=>'required|string|max:160','document'=>'nullable|string|max:40','phone'=>'required|string|max:40','email'=>'nullable|email|max:160','address'=>'nullable|string|max:255','contact_name'=>'nullable|string|max:160','contact_phone'=>'nullable|string|max:40','device_type'=>'required|string|max:60','brand'=>'nullable|string|max:100','model'=>'nullable|string|max:100','color'=>'nullable|string|max:60','imei1'=>'nullable|string|max:40','imei2'=>'nullable|string|max:40','serial'=>'nullable|string|max:100','unlock_method'=>'nullable|string|max:255','battery_state'=>'nullable|string|max:60','powered_off'=>'nullable|boolean','observations'=>'nullable|string|max:5000','requested_work'=>'nullable|string|max:5000','checklist'=>'nullable|array','checklist.*'=>'nullable|string|max:100','accessories'=>'nullable|array','accessories.*.included'=>'nullable|boolean','accessories.*.condition'=>'nullable|string|max:100','photos'=>'nullable|array|max:8','photos.*'=>'image|max:5120']);
        $order = DB::transaction(function () use ($data,$request) {
            $customer = DB::table('customers')->where('document',$data['document'] ?? null)->when(empty($data['document']), fn($q)=>$q->where('phone',$data['phone']))->first();
            $customerId = $customer?->id ?? DB::table('customers')->insertGetId(['name'=>$data['customer_name'],'document'=>$data['document'] ?? null,'phone'=>$data['phone'],'email'=>$data['email'] ?? null,'address'=>$data['address'] ?? null,'created_at'=>now(),'updated_at'=>now()]);
            $id = DB::table('service_orders')->insertGetId(['number'=>'OT-'.now()->format('ymd').'-'.Str::upper(Str::random(5)),'customer_id'=>$customerId,'received_by'=>auth()->id(),'contact_name'=>$data['contact_name'] ?? null,'contact_phone'=>$data['contact_phone'] ?? null,'device_type'=>$data['device_type'],'brand'=>$data['brand'] ?? null,'model'=>$data['model'] ?? null,'color'=>$data['color'] ?? null,'imei1'=>$data['imei1'] ?? null,'imei2'=>$data['imei2'] ?? null,'serial'=>$data['serial'] ?? null,'unlock_method'=>$data['unlock_method'] ?? null,'battery_state'=>$data['battery_state'] ?? null,'powered_off'=>$request->boolean('powered_off'),'observations'=>trim(($data['observations'] ?? '').($request->boolean('powered_off') ? "\nEquipo ingresó apagado; no fue posible verificar su estado ni funcionamiento al momento de la recepción." : '')),'requested_work'=>$data['requested_work'] ?? null,'status'=>'Recibido','received_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
            foreach (($data['accessories'] ?? []) as $name=>$accessory) if (!empty($accessory['included'])) DB::table('order_accessories')->insert(['service_order_id'=>$id,'name'=>$name,'condition'=>$accessory['condition'] ?? 'Bueno','created_at'=>now(),'updated_at'=>now()]);
            foreach (($data['checklist'] ?? []) as $item=>$result) if ($result) DB::table('checklist_checks')->insert(['service_order_id'=>$id,'item'=>$item,'result'=>$result,'checked_by'=>auth()->id(),'checked_at'=>now()]);
            foreach ($request->file('photos',[]) as $photo) DB::table('order_photos')->insert(['service_order_id'=>$id,'path'=>$photo->store('ordenes','public'),'created_at'=>now(),'updated_at'=>now()]);
            return $id;
        });
        return redirect()->route('orders.show', $order)->with('success', 'Orden registrada correctamente.');
    }
    public function orders(Request $request): View
    {
        $itemTotals = DB::table('service_order_items')
            ->select('service_order_id', DB::raw('SUM(quantity * unit_price) as total'))
            ->groupBy('service_order_id');
        $serviceTotals = DB::table('service_order_services')
            ->select('service_order_id', DB::raw('SUM(quantity * unit_price) as total'), DB::raw('COUNT(*) as line_count'))
            ->groupBy('service_order_id');
        $orders = DB::table('service_orders')
            ->join('customers', 'customers.id', '=', 'service_orders.customer_id')
            ->leftJoinSub($itemTotals, 'item_totals', fn ($join) => $join->on('item_totals.service_order_id', '=', 'service_orders.id'))
            ->leftJoinSub($serviceTotals, 'service_totals', fn ($join) => $join->on('service_totals.service_order_id', '=', 'service_orders.id'))
            ->select(
                'service_orders.*',
                'customers.name as customer',
                DB::raw('COALESCE(item_totals.total, 0) + COALESCE(service_totals.total, 0) + CASE WHEN COALESCE(service_totals.line_count, 0) = 0 THEN service_orders.estimated_cost ELSE 0 END as billable_total')
            )
            ->when($request->q, fn ($query) => $query->where(fn ($search) => $search
                ->where('service_orders.number', 'like', '%'.$request->q.'%')
                ->orWhere('customers.name', 'like', '%'.$request->q.'%')
                ->orWhere('customers.phone', 'like', '%'.$request->q.'%')))
            ->latest('service_orders.received_at')
            ->paginate(15)
            ->withQueryString();

        return view('workshop', compact('orders'))->with('page', 'orders');
    }
    public function showOrder(int $id): View
    {
        $order = DB::table('service_orders')
            ->join('customers', 'customers.id', '=', 'service_orders.customer_id')
            ->leftJoin('users', 'users.id', '=', 'service_orders.received_by')
            ->select('service_orders.*', 'customers.name as customer', 'customers.document as customer_document', 'customers.phone as customer_phone', 'customers.email as customer_email', 'customers.address as customer_address', 'users.name as received_by_name')
            ->where('service_orders.id', $id)
            ->first();

        abort_unless($order, 404);

        $accessories = DB::table('order_accessories')->where('service_order_id', $id)->orderBy('id')->get();
        $checks = DB::table('checklist_checks')->where('service_order_id', $id)->orderBy('id')->get();
        $photos = DB::table('order_photos')->where('service_order_id', $id)->orderBy('id')->get();
        $usedItems = DB::table('service_order_items')
            ->join('inventory_items', 'inventory_items.id', '=', 'service_order_items.inventory_item_id')
            ->where('service_order_items.service_order_id', $id)
            ->select('service_order_items.*', 'inventory_items.name as item_name', 'inventory_items.sku')
            ->orderBy('service_order_items.id')
            ->get();
        $orderServices = DB::table('service_order_services')->where('service_order_id', $id)->orderBy('id')->get();
        $serviceTotal = $orderServices->sum(fn ($service): float => $service->quantity * $service->unit_price);
        $itemTotal = $usedItems->sum(fn ($item): float => $item->quantity * $item->unit_price);
        $orderChargeTotal = $itemTotal + ($orderServices->isNotEmpty() ? $serviceTotal : $order->estimated_cost);
        $stockItems = DB::table('inventory_items')->where('quantity', '>', 0)->orderBy('name')->get();
        $orderInvoice = DB::table('invoices')->where('service_order_id', $id)->select('id', 'number', 'status')->first();
        $orderHasInvoice = $orderInvoice !== null;
        $statuses = ['Recibido', 'En diagnóstico', 'Esperando aprobación', 'En reparación', 'Listo para entrega', 'Entregado', 'Cancelado'];
        $publicStatusExpiresAt = now()->addDays(30);
        $publicStatusUrl = URL::temporarySignedRoute('orders.public-status', $publicStatusExpiresAt, ['id' => $id]);

        return view('workshop', compact('order', 'accessories', 'checks', 'photos', 'statuses', 'publicStatusUrl', 'publicStatusExpiresAt', 'usedItems', 'stockItems', 'orderServices', 'serviceTotal', 'orderChargeTotal', 'orderHasInvoice', 'orderInvoice'))->with('page', 'order_detail');
    }

    public function publicOrderStatus(int $id): View
    {
        $order = DB::table('service_orders')
            ->select('service_orders.number', 'service_orders.device_type', 'service_orders.brand', 'service_orders.model', 'service_orders.status', 'service_orders.received_at', 'service_orders.updated_at')
            ->where('service_orders.id', $id)
            ->first();

        abort_unless($order, 404);

        $chargeLines = DB::table('service_order_items')
            ->join('inventory_items', 'inventory_items.id', '=', 'service_order_items.inventory_item_id')
            ->where('service_order_items.service_order_id', $id)
            ->select('inventory_items.name as description', 'service_order_items.quantity', 'service_order_items.unit_price')
            ->get()
            ->map(fn ($line): object => (object) ['type' => 'Repuesto', 'description' => $line->description, 'quantity' => $line->quantity, 'unit_price' => $line->unit_price]);
        $serviceLines = DB::table('service_order_services')
            ->where('service_order_id', $id)
            ->select('description', 'quantity', 'unit_price')
            ->get()
            ->map(fn ($line): object => (object) ['type' => 'Servicio', 'description' => $line->description, 'quantity' => $line->quantity, 'unit_price' => $line->unit_price]);
        $chargeLines = $chargeLines->concat($serviceLines);

        if ($serviceLines->isEmpty()) {
            $estimatedCost = DB::table('service_orders')->where('id', $id)->value('estimated_cost');
            if ((float) $estimatedCost > 0) {
                $chargeLines->push((object) ['type' => 'Servicio', 'description' => 'Servicio técnico estimado', 'quantity' => 1, 'unit_price' => $estimatedCost]);
            }
        }

        $chargeTotal = $chargeLines->sum(fn ($line): float => $line->quantity * $line->unit_price);
        $invoice = DB::table('invoices')->where('service_order_id', $id)->select('number', 'status', 'total')->first();

        return view('service-order-status', compact('order', 'chargeLines', 'chargeTotal', 'invoice'));
    }

    public function updateOrder(Request $request, int $id): RedirectResponse
    {
        $statuses = ['Recibido', 'En diagnóstico', 'Esperando aprobación', 'En reparación', 'Listo para entrega', 'Entregado', 'Cancelado'];
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:160'],
            'document' => ['nullable', 'string', 'max:40'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:160'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'device_type' => ['required', 'string', 'max:60'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:60'],
            'imei1' => ['nullable', 'string', 'max:40'],
            'imei2' => ['nullable', 'string', 'max:40'],
            'serial' => ['nullable', 'string', 'max:100'],
            'unlock_method' => ['nullable', 'string', 'max:255'],
            'battery_state' => ['nullable', 'string', 'max:60'],
            'powered_off' => ['nullable', 'boolean'],
            'observations' => ['nullable', 'string', 'max:5000'],
            'requested_work' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:'.implode(',', $statuses)],
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'estimated_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['nullable', 'string', 'max:100'],
            'accessories' => ['nullable', 'array'],
            'accessories.*.included' => ['nullable', 'boolean'],
            'accessories.*.condition' => ['nullable', 'string', 'max:100'],
            'photos' => ['nullable', 'array', 'max:8'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        $serviceOrder = DB::table('service_orders')->where('id', $id)->first();
        abort_unless($serviceOrder, 404);

        DB::transaction(function () use ($data, $request, $id, $serviceOrder): void {
            DB::table('customers')->where('id', $serviceOrder->customer_id)->update([
                'name' => $data['customer_name'],
                'document' => $data['document'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'updated_at' => now(),
            ]);

            DB::table('service_orders')->where('id', $id)->update([
                'contact_name' => $data['contact_name'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'device_type' => $data['device_type'],
                'brand' => $data['brand'] ?? null,
                'model' => $data['model'] ?? null,
                'color' => $data['color'] ?? null,
                'imei1' => $data['imei1'] ?? null,
                'imei2' => $data['imei2'] ?? null,
                'serial' => $data['serial'] ?? null,
                'unlock_method' => $data['unlock_method'] ?? null,
                'battery_state' => $data['battery_state'] ?? null,
                'powered_off' => $request->boolean('powered_off'),
                'observations' => $data['observations'] ?? null,
                'requested_work' => $data['requested_work'] ?? null,
                'status' => $data['status'],
                'diagnosis' => $data['diagnosis'] ?? null,
                'estimated_cost' => $data['estimated_cost'],
                'updated_at' => now(),
            ]);

            DB::table('order_accessories')->where('service_order_id', $id)->delete();
            foreach (($data['accessories'] ?? []) as $name => $accessory) {
                if (!empty($accessory['included'])) {
                    DB::table('order_accessories')->insert([
                        'service_order_id' => $id,
                        'name' => $name,
                        'condition' => $accessory['condition'] ?? 'Bueno',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('checklist_checks')->where('service_order_id', $id)->delete();
            foreach (($data['checklist'] ?? []) as $item => $result) {
                if ($result) {
                    DB::table('checklist_checks')->insert([
                        'service_order_id' => $id,
                        'item' => $item,
                        'result' => $result,
                        'checked_by' => auth()->id(),
                        'checked_at' => now(),
                    ]);
                }
            }

            foreach ($request->file('photos', []) as $photo) {
                DB::table('order_photos')->insert([
                    'service_order_id' => $id,
                    'path' => $photo->store('ordenes', 'public'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('orders.show', $id)->with('success', 'La orden se actualizó correctamente.');
    }
    public function customers(Request $request): View { $customers=DB::table('customers')->when($request->q,fn($q)=>$q->where(fn($w)=>$w->where('name','like','%'.$request->q.'%')->orWhere('document','like','%'.$request->q.'%')->orWhere('phone','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%')))->latest()->paginate(15); return view('workshop',compact('customers'))->with('page','customers'); }
    public function inventory(): View
    {
        $items = DB::table('inventory_items')->orderBy('quantity')->orderBy('name')->get();

        return view('workshop', compact('items'))->with('page', 'inventory');
    }

    public function storeInventoryItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:inventory_items,sku'],
            'category' => ['nullable', 'string', 'max:100'],
            'min_quantity' => ['required', 'integer', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        DB::table('inventory_items')->insert([
            ...$data,
            'quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('inventory.index')->with('success', 'Producto o repuesto registrado. Registra una compra para ingresar existencias.');
    }

    public function updateInventoryItem(Request $request, int $id): RedirectResponse
    {
        $itemExists = DB::table('inventory_items')->where('id', $id)->exists();
        abort_unless($itemExists, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:inventory_items,sku,'.$id],
            'category' => ['nullable', 'string', 'max:100'],
            'min_quantity' => ['required', 'integer', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        DB::table('inventory_items')->where('id', $id)->update([...$data, 'updated_at' => now()]);

        return redirect()->route('inventory.index')->with('success', 'Datos y precio del producto actualizados.');
    }

    public function purchases(): View
    {
        $purchases = DB::table('purchases')
            ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
            ->select('purchases.*', 'users.name as created_by_name')
            ->latest('purchased_at')
            ->paginate(15);
        $purchaseLines = DB::table('purchase_items')
            ->join('inventory_items', 'inventory_items.id', '=', 'purchase_items.inventory_item_id')
            ->select('purchase_items.*', 'inventory_items.name as item_name', 'inventory_items.sku')
            ->whereIn('purchase_id', $purchases->pluck('id'))
            ->get()
            ->groupBy('purchase_id');

        return view('workshop', compact('purchases', 'purchaseLines'))->with('page', 'purchases');
    }

    public function createPurchase(): View
    {
        $items = DB::table('inventory_items')->orderBy('name')->get();
        $purchase = null;
        $lines = collect();
        $editable = true;

        return view('workshop', compact('items', 'purchase', 'lines', 'editable'))->with('page', 'purchase_form');
    }

    public function showPurchase(int $id): View
    {
        $purchase = DB::table('purchases')->where('id', $id)->first();
        abort_unless($purchase, 404);
        $lines = DB::table('purchase_items')
            ->join('inventory_items', 'inventory_items.id', '=', 'purchase_items.inventory_item_id')
            ->where('purchase_items.purchase_id', $id)
            ->select('purchase_items.*', 'inventory_items.name as item_name', 'inventory_items.sku')
            ->orderBy('purchase_items.id')
            ->get();
        $items = DB::table('inventory_items')->orderBy('name')->get();
        $editable = $purchase->status === 'Borrador';

        return view('workshop', compact('purchase', 'lines', 'items', 'editable'))->with('page', 'purchase_form');
    }

    public function storePurchase(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier' => ['required', 'string', 'max:160'],
            'reference' => ['nullable', 'string', 'max:100'],
            'purchased_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', 'integer', 'distinct', 'exists:inventory_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $purchaseId = DB::transaction(function () use ($data): int {
            $purchaseNumber = 'COMP-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
            $total = round(collect($data['items'])->sum(fn (array $line): float => (int) $line['quantity'] * (float) $line['unit_cost']), 2);
            $purchaseId = DB::table('purchases')->insertGetId([
                'number' => $purchaseNumber,
                'supplier' => $data['supplier'],
                'reference' => $data['reference'] ?? null,
                'purchased_at' => $data['purchased_at'],
                'total' => $total,
                'status' => 'Borrador',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->replacePurchaseLines($purchaseId, $data['items']);

            return $purchaseId;
        }, attempts: 5);

        return redirect()->route('purchases.show', $purchaseId)->with('success', 'Compra guardada como borrador. El stock se actualiza al finalizarla.');
    }

    public function updatePurchase(Request $request, int $id): RedirectResponse
    {
        $purchase = DB::table('purchases')->where('id', $id)->first();
        abort_unless($purchase, 404);
        abort_unless($purchase->status === 'Borrador', 403);
        $data = $this->validatePurchase($request);

        DB::transaction(function () use ($data, $id): void {
            $purchase = DB::table('purchases')->where('id', $id)->lockForUpdate()->first();
            abort_unless($purchase && $purchase->status === 'Borrador', 403);
            $total = round(collect($data['items'])->sum(fn (array $line): float => (int) $line['quantity'] * (float) $line['unit_cost']), 2);
            DB::table('purchases')->where('id', $id)->update([
                'supplier' => $data['supplier'],
                'reference' => $data['reference'] ?? null,
                'purchased_at' => $data['purchased_at'],
                'total' => $total,
                'updated_at' => now(),
            ]);
            DB::table('purchase_items')->where('purchase_id', $id)->delete();
            $this->replacePurchaseLines($id, $data['items']);
        }, attempts: 5);

        return redirect()->route('purchases.show', $id)->with('success', 'Borrador de compra actualizado.');
    }

    public function finalizePurchase(int $id): RedirectResponse
    {
        DB::transaction(function () use ($id): void {
            $purchase = DB::table('purchases')->where('id', $id)->lockForUpdate()->first();
            abort_unless($purchase, 404);
            abort_unless($purchase->status === 'Borrador', 403);
            $lines = DB::table('purchase_items')->where('purchase_id', $id)->orderBy('inventory_item_id')->get();
            $itemIds = $lines->pluck('inventory_item_id')->unique();
            $items = DB::table('inventory_items')->whereIn('id', $itemIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($lines as $line) {
                $item = $items->get($line->inventory_item_id);
                $newQuantity = (int) $item->quantity + (int) $line->quantity;
                $weightedCost = round((((int) $item->quantity * (float) $item->unit_cost) + ((int) $line->quantity * (float) $line->unit_cost)) / $newQuantity, 2);
                DB::table('inventory_items')->where('id', $item->id)->update([
                    'quantity' => $newQuantity,
                    'unit_cost' => $weightedCost,
                    'updated_at' => now(),
                ]);
                DB::table('inventory_movements')->insert([
                    'inventory_item_id' => $item->id,
                    'type' => 'Compra',
                    'quantity_change' => $line->quantity,
                    'reference' => $purchase->number,
                    'note' => $purchase->supplier,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $items[$item->id]->quantity = $newQuantity;
                $items[$item->id]->unit_cost = $weightedCost;
            }

            DB::table('purchases')->where('id', $id)->update([
                'status' => 'Finalizada',
                'finalized_at' => now(),
                'updated_at' => now(),
            ]);
        }, attempts: 5);

        return redirect()->route('purchases.show', $id)->with('success', 'Compra finalizada. Las existencias fueron ingresadas al inventario.');
    }

    public function destroyPurchase(int $id): RedirectResponse
    {
        DB::transaction(function () use ($id): void {
            $purchase = DB::table('purchases')->where('id', $id)->lockForUpdate()->first();
            abort_unless($purchase, 404);
            abort_unless($purchase->status === 'Borrador', 403);
            DB::table('purchases')->where('id', $id)->delete();
        });

        return redirect()->route('purchases.index')->with('success', 'Borrador de compra eliminado.');
    }

    private function validatePurchase(Request $request): array
    {
        return $request->validate([
            'supplier' => ['required', 'string', 'max:160'],
            'reference' => ['nullable', 'string', 'max:100'],
            'purchased_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', 'integer', 'distinct', 'exists:inventory_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);
    }

    private function replacePurchaseLines(int $purchaseId, array $lines): void
    {
        foreach ($lines as $line) {
            DB::table('purchase_items')->insert([
                'purchase_id' => $purchaseId,
                'inventory_item_id' => $line['inventory_item_id'],
                'quantity' => $line['quantity'],
                'unit_cost' => $line['unit_cost'],
                'line_total' => round($line['quantity'] * $line['unit_cost'], 2),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function inventoryReport(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'type' => ['nullable', 'in:Compra,Venta,Orden de servicio,Ajuste'],
        ]);
        $movements = DB::table('inventory_movements')
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_movements.inventory_item_id')
            ->select('inventory_movements.*', 'inventory_items.name as item_name', 'inventory_items.sku')
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('inventory_movements.created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('inventory_movements.created_at', '<=', $to))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('inventory_movements.type', $type))
            ->latest('inventory_movements.created_at')
            ->paginate(25)
            ->withQueryString();
        $items = DB::table('inventory_items')->get();
        $summary = [
            'items' => $items->count(),
            'low_stock' => $items->filter(fn ($item): bool => $item->quantity <= $item->min_quantity)->count(),
            'units' => $items->sum('quantity'),
            'cost_value' => $items->sum(fn ($item): float => $item->quantity * $item->unit_cost),
            'sale_value' => $items->sum(fn ($item): float => $item->quantity * $item->sale_price),
        ];

        return view('workshop', compact('movements', 'summary', 'filters'))->with('page', 'inventory_report');
    }

    public function addOrderItem(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);
        DB::transaction(function () use ($data, $id): void {
            $order = DB::table('service_orders')->where('id', $id)->lockForUpdate()->first();
            abort_unless($order, 404);
            if (in_array($order->status, ['Entregado', 'Cancelado'], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['inventory' => 'No se pueden asignar repuestos a una orden cerrada.']);
            }
            if (DB::table('invoices')->where('service_order_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['inventory' => 'No se pueden modificar los repuestos de una orden facturada.']);
            }

            $item = DB::table('inventory_items')->where('id', $data['inventory_item_id'])->lockForUpdate()->first();
            if (!$item || $item->quantity < $data['quantity']) {
                throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => 'No hay existencias suficientes para esta orden.']);
            }

            $quantity = (int) $data['quantity'];
            DB::table('inventory_items')->where('id', $item->id)->update([
                'quantity' => $item->quantity - $quantity,
                'updated_at' => now(),
            ]);
            DB::table('service_order_items')->insert([
                'service_order_id' => $id,
                'inventory_item_id' => $item->id,
                'quantity' => $quantity,
                'unit_cost' => $item->unit_cost,
                'unit_price' => $item->sale_price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('inventory_movements')->insert([
                'inventory_item_id' => $item->id,
                'type' => 'Orden de servicio',
                'quantity_change' => -$quantity,
                'reference' => $order->number,
                'note' => 'Repuesto asignado a orden de servicio',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, attempts: 5);

        return redirect()->route('orders.show', $id)->with('success', 'Repuesto asignado y descontado del inventario.');
    }

    public function updateOrderItem(Request $request, int $id, int $lineId): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        DB::transaction(function () use ($data, $id, $lineId): void {
            $order = DB::table('service_orders')->where('id', $id)->lockForUpdate()->first();
            abort_unless($order, 404);
            if (in_array($order->status, ['Entregado', 'Cancelado'], true) || DB::table('invoices')->where('service_order_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => 'No se pueden editar cargos de una orden cerrada o facturada.']);
            }
            $line = DB::table('service_order_items')->where('service_order_id', $id)->where('id', $lineId)->lockForUpdate()->first();
            abort_unless($line, 404);
            $item = DB::table('inventory_items')->where('id', $line->inventory_item_id)->lockForUpdate()->first();
            $quantityChange = (int) $data['quantity'] - (int) $line->quantity;

            if ($quantityChange > 0 && $item->quantity < $quantityChange) {
                throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => 'No hay existencias suficientes para aumentar la cantidad.']);
            }

            if ($quantityChange !== 0) {
                DB::table('inventory_items')->where('id', $item->id)->update([
                    'quantity' => $item->quantity - $quantityChange,
                    'updated_at' => now(),
                ]);
                DB::table('inventory_movements')->insert([
                    'inventory_item_id' => $item->id,
                    'type' => $quantityChange > 0 ? 'Orden de servicio' : 'Ajuste',
                    'quantity_change' => -$quantityChange,
                    'reference' => $order->number,
                    'note' => 'Cantidad de repuesto ajustada en la orden',
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('service_order_items')->where('id', $lineId)->update([
                'quantity' => $data['quantity'],
                'updated_at' => now(),
            ]);
        }, attempts: 5);

        return redirect()->route('orders.show', $id)->with('success', 'Cargo de producto actualizado.');
    }

    public function removeOrderItem(int $id, int $lineId): RedirectResponse
    {
        DB::transaction(function () use ($id, $lineId): void {
            $order = DB::table('service_orders')->where('id', $id)->lockForUpdate()->first();
            abort_unless($order, 404);
            if (in_array($order->status, ['Entregado', 'Cancelado'], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['inventory' => 'No se pueden retirar cargos de una orden cerrada.']);
            }
            if (DB::table('invoices')->where('service_order_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['inventory' => 'No se pueden retirar cargos de una orden facturada.']);
            }
            $line = DB::table('service_order_items')->where('service_order_id', $id)->where('id', $lineId)->lockForUpdate()->first();
            abort_unless($line, 404);
            if (DB::table('invoice_lines')->where('service_order_item_id', $lineId)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['inventory' => 'No se puede retirar un repuesto que ya fue facturado.']);
            }

            $item = DB::table('inventory_items')->where('id', $line->inventory_item_id)->lockForUpdate()->first();
            DB::table('inventory_items')->where('id', $line->inventory_item_id)->update([
                'quantity' => $item->quantity + $line->quantity,
                'updated_at' => now(),
            ]);
            DB::table('inventory_movements')->insert([
                'inventory_item_id' => $item->id,
                'type' => 'Ajuste',
                'quantity_change' => $line->quantity,
                'reference' => DB::table('service_orders')->where('id', $id)->value('number'),
                'note' => 'Repuesto retirado de la orden y devuelto a existencias',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('service_order_items')->where('id', $lineId)->delete();
        }, attempts: 5);

        return redirect()->route('orders.show', $id)->with('success', 'Repuesto retirado y devuelto al inventario.');
    }

    public function storeOrderService(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        DB::transaction(function () use ($data, $id): void {
            $order = DB::table('service_orders')->where('id', $id)->lockForUpdate()->first();
            abort_unless($order, 404);
            if (in_array($order->status, ['Entregado', 'Cancelado'], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['service' => 'No se pueden agregar servicios a una orden cerrada.']);
            }
            if (DB::table('invoices')->where('service_order_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['service' => 'No se pueden modificar los servicios de una orden que ya tiene factura.']);
            }

            DB::table('service_order_services')->insert([
                'service_order_id' => $id,
                'description' => $data['description'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, attempts: 5);

        return redirect()->route('orders.show', $id)->with('success', 'Servicio agregado a la orden.');
    }

    public function updateOrderService(Request $request, int $id, int $serviceId): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        DB::transaction(function () use ($data, $id, $serviceId): void {
            $order = DB::table('service_orders')->where('id', $id)->lockForUpdate()->first();
            abort_unless($order, 404);
            if (in_array($order->status, ['Entregado', 'Cancelado'], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['service' => 'No se pueden editar servicios de una orden cerrada.']);
            }
            $service = DB::table('service_order_services')->where('service_order_id', $id)->where('id', $serviceId)->first();
            abort_unless($service, 404);
            if (DB::table('invoices')->where('service_order_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['service' => 'No se pueden modificar los servicios de una orden que ya tiene factura.']);
            }

            DB::table('service_order_services')->where('id', $serviceId)->update([
                'description' => $data['description'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'updated_at' => now(),
            ]);
        }, attempts: 5);

        return redirect()->route('orders.show', $id)->with('success', 'Servicio de la orden actualizado.');
    }

    public function destroyOrderService(int $id, int $serviceId): RedirectResponse
    {
        DB::transaction(function () use ($id, $serviceId): void {
            $order = DB::table('service_orders')->where('id', $id)->lockForUpdate()->first();
            abort_unless($order, 404);
            if (in_array($order->status, ['Entregado', 'Cancelado'], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['service' => 'No se pueden quitar servicios de una orden cerrada.']);
            }
            $service = DB::table('service_order_services')->where('service_order_id', $id)->where('id', $serviceId)->first();
            abort_unless($service, 404);
            if (DB::table('invoices')->where('service_order_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['service' => 'No se pueden eliminar servicios de una orden que ya tiene factura.']);
            }

            DB::table('service_order_services')->where('id', $serviceId)->delete();
        }, attempts: 5);

        return redirect()->route('orders.show', $id)->with('success', 'Servicio retirado de la orden.');
    }

    public function billing(): View
    {
        $invoices = DB::table('invoices')
            ->leftJoin('service_orders', 'service_orders.id', '=', 'invoices.service_order_id')
            ->leftJoin('customers as order_customers', 'order_customers.id', '=', 'service_orders.customer_id')
            ->leftJoin('customers as invoice_customers', 'invoice_customers.id', '=', 'invoices.customer_id')
            ->select('invoices.*', 'service_orders.number as order_number', DB::raw("COALESCE(invoice_customers.name, order_customers.name, 'Venta de mostrador') as customer"))
            ->latest('invoices.created_at')
            ->paginate(15);
        $invoiceLines = DB::table('invoice_lines')->whereIn('invoice_id', $invoices->pluck('id'))->get()->groupBy('invoice_id');
        return view('workshop', compact('invoices', 'invoiceLines'))->with('page', 'billing');
    }

    public function createInvoice(Request $request): View
    {
        $invoice = null;
        $lines = collect();
        $editable = true;
        $isServiceOrderInvoice = false;
        $items = DB::table('inventory_items')->where('quantity', '>', 0)->orderBy('name')->get();
        $customers = DB::table('customers')->orderBy('name')->get();
        $uninvoicedOrders = $this->uninvoicedOrders();
        $preselectedOrderId = $request->integer('service_order_id');

        return view('workshop', compact('invoice', 'lines', 'editable', 'isServiceOrderInvoice', 'items', 'customers', 'uninvoicedOrders', 'preselectedOrderId'))->with('page', 'invoice_form');
    }

    public function showInvoice(int $id): View
    {
        $invoice = DB::table('invoices')
            ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->leftJoin('service_orders', 'service_orders.id', '=', 'invoices.service_order_id')
            ->select('invoices.*', 'customers.name as customer_name', 'customers.document as customer_document', 'customers.phone as customer_phone', 'customers.address as customer_address', 'service_orders.number as order_number')
            ->where('invoices.id', $id)
            ->first();
        abort_unless($invoice, 404);
        $lines = DB::table('invoice_lines')->where('invoice_id', $id)->orderBy('id')->get();
        $editable = $invoice->status === 'Borrador';
        $isServiceOrderInvoice = $invoice->service_order_id !== null;
        $items = DB::table('inventory_items')->orderBy('name')->get();
        $customers = DB::table('customers')->orderBy('name')->get();
        $uninvoicedOrders = collect();

        return view('workshop', compact('invoice', 'lines', 'editable', 'isServiceOrderInvoice', 'items', 'customers', 'uninvoicedOrders'))->with('page', 'invoice_form');
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_order_id' => ['nullable', 'integer', 'exists:service_orders,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items' => ['required_without:service_order_id', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', 'integer', 'distinct', 'exists:inventory_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $invoiceId = DB::transaction(function () use ($data): int {
            $order = null;
            $customerId = $data['customer_id'] ?? null;
            if (!empty($data['service_order_id'])) {
                $order = DB::table('service_orders')->where('id', $data['service_order_id'])->lockForUpdate()->first();
                if (DB::table('invoices')->where('service_order_id', $order->id)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['service_order_id' => 'Esta orden ya tiene una factura.']);
                }
                $customerId = $order->customer_id;
            }

            $invoiceNumber = 'FAC-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
            $invoiceLines = [];
            $saleItems = collect($data['items'] ?? [])->sortBy('inventory_item_id')->values();

            if ($order) {
                $orderItems = DB::table('service_order_items')
                    ->join('inventory_items', 'inventory_items.id', '=', 'service_order_items.inventory_item_id')
                    ->where('service_order_items.service_order_id', $order->id)
                    ->select('service_order_items.*', 'inventory_items.name as item_name')
                    ->orderBy('service_order_items.id')
                    ->get();
                foreach ($orderItems as $orderItem) {
                    $invoiceLines[] = [
                        'inventory_item_id' => $orderItem->inventory_item_id,
                        'service_order_item_id' => $orderItem->id,
                        'service_order_service_id' => null,
                        'description' => $orderItem->item_name,
                        'quantity' => $orderItem->quantity,
                        'unit_cost' => $orderItem->unit_cost,
                        'unit_price' => $orderItem->unit_price,
                    ];
                }
                $orderServices = DB::table('service_order_services')->where('service_order_id', $order->id)->orderBy('id')->get();
                foreach ($orderServices as $orderService) {
                    $invoiceLines[] = [
                        'inventory_item_id' => null,
                        'service_order_item_id' => null,
                        'service_order_service_id' => $orderService->id,
                        'description' => $orderService->description,
                        'quantity' => $orderService->quantity,
                        'unit_cost' => 0,
                        'unit_price' => $orderService->unit_price,
                    ];
                }
                if ($orderServices->isEmpty() && $order->estimated_cost > 0) {
                    $invoiceLines[] = [
                        'inventory_item_id' => null,
                        'service_order_item_id' => null,
                        'service_order_service_id' => null,
                        'description' => 'Servicio técnico · '.$order->number,
                        'quantity' => 1,
                        'unit_cost' => 0,
                        'unit_price' => $order->estimated_cost,
                    ];
                }
                if ($invoiceLines === []) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['service_order_id' => 'Agrega repuestos o un costo estimado antes de facturar esta orden.']);
                }
            } else {
                $itemIds = $saleItems->pluck('inventory_item_id');
                $lockedItems = DB::table('inventory_items')->whereIn('id', $itemIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                if ($lockedItems->count() !== $itemIds->unique()->count()) {
                    abort(422);
                }
                foreach ($saleItems as $saleItem) {
                    $item = $lockedItems->get($saleItem['inventory_item_id']);
                    $quantity = (int) $saleItem['quantity'];
                    $invoiceLines[] = [
                        'inventory_item_id' => $item->id,
                        'service_order_item_id' => null,
                        'service_order_service_id' => null,
                        'description' => $item->name,
                        'quantity' => $quantity,
                        'unit_cost' => $item->unit_cost,
                        'unit_price' => $item->sale_price,
                    ];
                }
            }

            $subtotal = round(collect($invoiceLines)->sum(fn (array $line): float => $line['quantity'] * $line['unit_price']), 2);
            $tax = round($subtotal * ((float) $data['tax_rate'] / 100), 2);
            $invoiceId = DB::table('invoices')->insertGetId([
                'service_order_id' => $order?->id,
                'customer_id' => $customerId,
                'number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'tax_rate' => $data['tax_rate'],
                'total' => $subtotal + $tax,
                'status' => 'Borrador',
                'issued_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($invoiceLines as $line) {
                $line['invoice_id'] = $invoiceId;
                $line['line_total'] = $line['quantity'] * $line['unit_price'];
                $line['created_at'] = now();
                $line['updated_at'] = now();
                DB::table('invoice_lines')->insert($line);
            }

            return $invoiceId;
        }, attempts: 5);

        return redirect()->route('billing.show', $invoiceId)->with('success', 'Factura guardada como borrador. Revisa el documento y finalízalo cuando esté listo.');
    }

    public function updateInvoice(Request $request, int $id): RedirectResponse
    {
        $invoice = DB::table('invoices')->where('id', $id)->first();
        abort_unless($invoice, 404);
        abort_unless($invoice->status === 'Borrador', 403);

        if ($invoice->service_order_id !== null) {
            $data = $request->validate(['tax_rate' => ['required', 'numeric', 'min:0', 'max:100']]);
            DB::transaction(function () use ($id, $data): void {
                $invoice = DB::table('invoices')->where('id', $id)->lockForUpdate()->first();
                abort_unless($invoice && $invoice->status === 'Borrador', 403);
                DB::table('invoices')->where('id', $id)->update([
                    'tax_rate' => $data['tax_rate'],
                    'updated_at' => now(),
                ]);
                $this->recalculateInvoice($id, (float) $data['tax_rate']);
            }, attempts: 5);
        } else {
            $data = $request->validate([
                'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
                'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.inventory_item_id' => ['required', 'integer', 'distinct', 'exists:inventory_items,id'],
                'items.*.description' => ['required', 'string', 'max:255'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            ]);

            DB::transaction(function () use ($data, $id): void {
                $invoice = DB::table('invoices')->where('id', $id)->lockForUpdate()->first();
                abort_unless($invoice && $invoice->status === 'Borrador' && $invoice->service_order_id === null, 403);
                DB::table('invoices')->where('id', $id)->update([
                    'customer_id' => $data['customer_id'] ?? null,
                    'tax_rate' => $data['tax_rate'],
                    'updated_at' => now(),
                ]);
                DB::table('invoice_lines')->where('invoice_id', $id)->delete();
                foreach ($data['items'] as $line) {
                    $item = DB::table('inventory_items')->where('id', $line['inventory_item_id'])->first();
                    DB::table('invoice_lines')->insert([
                        'invoice_id' => $id,
                        'inventory_item_id' => $item->id,
                        'description' => $line['description'],
                        'quantity' => $line['quantity'],
                        'unit_cost' => $item->unit_cost,
                        'unit_price' => $line['unit_price'],
                        'line_total' => $line['quantity'] * $line['unit_price'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $this->recalculateInvoice($id, (float) $data['tax_rate']);
            }, attempts: 5);
        }

        return redirect()->route('billing.show', $id)->with('success', 'Borrador de factura actualizado.');
    }

    public function finalizeInvoice(int $id): RedirectResponse
    {
        DB::transaction(function () use ($id): void {
            $invoice = DB::table('invoices')->where('id', $id)->lockForUpdate()->first();
            abort_unless($invoice, 404);
            abort_unless($invoice->status === 'Borrador', 403);
            $lines = DB::table('invoice_lines')->where('invoice_id', $id)->get();

            if ($invoice->service_order_id === null) {
                $quantities = $lines->groupBy('inventory_item_id')->map(fn ($itemLines): int => $itemLines->sum('quantity'));
                $itemIds = $quantities->keys()->filter()->sort()->values();
                $items = DB::table('inventory_items')->whereIn('id', $itemIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                foreach ($quantities as $itemId => $quantity) {
                    $item = $items->get($itemId);
                    if (!$item || $item->quantity < $quantity) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['inventory' => 'No hay existencias suficientes para finalizar esta factura.']);
                    }
                    DB::table('inventory_items')->where('id', $itemId)->update([
                        'quantity' => $item->quantity - $quantity,
                        'updated_at' => now(),
                    ]);
                    DB::table('inventory_movements')->insert([
                        'inventory_item_id' => $itemId,
                        'type' => 'Venta',
                        'quantity_change' => -$quantity,
                        'reference' => $invoice->number,
                        'note' => 'Venta facturada',
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $this->recalculateInvoice($id, (float) $invoice->tax_rate);
            DB::table('invoices')->where('id', $id)->update([
                'status' => 'Finalizada',
                'issued_at' => now(),
                'finalized_at' => now(),
                'updated_at' => now(),
            ]);
        }, attempts: 5);

        return redirect()->route('billing.show', $id)->with('success', 'Factura finalizada. Queda bloqueada para edición.');
    }

    public function destroyInvoice(int $id): RedirectResponse
    {
        DB::transaction(function () use ($id): void {
            $invoice = DB::table('invoices')->where('id', $id)->lockForUpdate()->first();
            abort_unless($invoice, 404);
            abort_unless($invoice->status === 'Borrador', 403);
            DB::table('invoices')->where('id', $id)->delete();
        });

        return redirect()->route('billing.index')->with('success', 'Borrador de factura eliminado.');
    }

    private function uninvoicedOrders(): Collection
    {
        return DB::table('service_orders')
            ->join('customers', 'customers.id', '=', 'service_orders.customer_id')
            ->whereNotIn('service_orders.id', DB::table('invoices')->select('service_order_id')->whereNotNull('service_order_id'))
            ->whereNotIn('service_orders.status', ['Cancelado'])
            ->select('service_orders.id', 'service_orders.number', 'customers.name as customer', 'service_orders.estimated_cost')
            ->latest('service_orders.received_at')
            ->get();
    }

    private function recalculateInvoice(int $invoiceId, float $taxRate): void
    {
        $subtotal = (float) DB::table('invoice_lines')->where('invoice_id', $invoiceId)->sum('line_total');
        $tax = round($subtotal * ($taxRate / 100), 2);
        DB::table('invoices')->where('id', $invoiceId)->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'updated_at' => now(),
        ]);
    }
}
