<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class WorkshopController extends Controller
{
    public function dashboard()
    {
        $stats = ['active' => DB::table('service_orders')->whereNotIn('status', ['Entregado', 'Cancelado'])->count(), 'today' => DB::table('service_orders')->whereDate('received_at', today())->count(), 'ready' => DB::table('service_orders')->where('status', 'Listo para entrega')->count(), 'customers' => DB::table('customers')->count()];
        $orders = DB::table('service_orders')->join('customers','customers.id','=','service_orders.customer_id')->select('service_orders.*','customers.name as customer')->latest('received_at')->limit(7)->get();
        return view('workshop', compact('stats','orders'))->with('page', 'dashboard');
    }
    public function reception() { return view('workshop', ['page'=>'reception']); }
    public function store(Request $request)
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
    public function orders(Request $request) { $orders=DB::table('service_orders')->join('customers','customers.id','=','service_orders.customer_id')->select('service_orders.*','customers.name as customer')->when($request->q,fn($q)=>$q->where(fn($w)=>$w->where('service_orders.number','like','%'.$request->q.'%')->orWhere('customers.name','like','%'.$request->q.'%')->orWhere('customers.phone','like','%'.$request->q.'%')))->latest('received_at')->paginate(15); return view('workshop',compact('orders'))->with('page','orders'); }
    public function showOrder(int $id)
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
        $statuses = ['Recibido', 'En diagnóstico', 'Esperando aprobación', 'En reparación', 'Listo para entrega', 'Entregado', 'Cancelado'];
        $publicStatusExpiresAt = now()->addDays(30);
        $publicStatusUrl = URL::temporarySignedRoute('orders.public-status', $publicStatusExpiresAt, ['id' => $id]);

        return view('workshop', compact('order', 'accessories', 'checks', 'photos', 'statuses', 'publicStatusUrl', 'publicStatusExpiresAt'))->with('page', 'order_detail');
    }

    public function publicOrderStatus(int $id)
    {
        $order = DB::table('service_orders')
            ->select('service_orders.number', 'service_orders.device_type', 'service_orders.brand', 'service_orders.model', 'service_orders.status', 'service_orders.received_at', 'service_orders.updated_at')
            ->where('service_orders.id', $id)
            ->first();

        abort_unless($order, 404);

        return view('service-order-status', compact('order'));
    }

    public function updateOrder(Request $request, int $id)
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
    public function customers(Request $request) { $customers=DB::table('customers')->when($request->q,fn($q)=>$q->where(fn($w)=>$w->where('name','like','%'.$request->q.'%')->orWhere('document','like','%'.$request->q.'%')->orWhere('phone','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%')))->latest()->paginate(15); return view('workshop',compact('customers'))->with('page','customers'); }
    public function inventory() { $items=DB::table('inventory_items')->orderBy('quantity')->get(); return view('workshop',compact('items'))->with('page','inventory'); }
    public function billing() { $invoices=DB::table('invoices')->join('service_orders','service_orders.id','=','invoices.service_order_id')->join('customers','customers.id','=','service_orders.customer_id')->select('invoices.*','service_orders.number as order_number','customers.name as customer')->latest('invoices.created_at')->get(); return view('workshop',compact('invoices'))->with('page','billing'); }
}
