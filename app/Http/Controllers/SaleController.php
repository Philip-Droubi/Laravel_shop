<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use function PHPUnit\Framework\isNull;

class SaleController extends Controller
{
    public function store(Request $request, $id)
    {
        $product = Product::find($id);
        if ($product) {
            if (Gate::allows('Product-Protection', $product)) {
                if (!$product->sales()->get()->first()) {
                    $input = $request->only('start', 'end', 'discount');
                    $request->validate([
                        'start' => ['required', 'string', 'date'],
                        'end' => ['required', 'string', 'date'],
                        'discount' => ['required', 'numeric', 'min:0', 'max:100']
                    ]);
                    $input["product_id"] = $id;
                    $input['start'] = Carbon::createFromFormat('Y-m-d', $request->start)->format('Y-m-d');
                    $input['end'] = Carbon::createFromFormat('Y-m-d', $request->end)->format('Y-m-d');
                    if ($input["start"] >= $input["end"]) {
                        return response()->json(["ُmessage" => __("messages.Start discount date should be < End discount date : ") . $input['end']], 400);
                    }
                    if ($product->exp_date && (Carbon::parse($product->exp_date)->lte($input["start"]) || Carbon::parse($product->exp_date)->lte($input["end"]))) {
                        return response()->json(["ُmessage" => __("messages.Start discount date and End discount date should be < Expire date ") . $product->exp_date], 400);
                    }
                    if ($input["start"] < Carbon::now()->addDays(-1)) {
                        return response()->json(["ُmessage" => __("messages.Start discount date should be > ") . Carbon::now()->addDays(-1)->format('Y-m-d')], 400);
                    }
                    if ($input["end"] < Carbon::now()) {
                        return response()->json(["ُmessage" => __("messages.End discount date should be > ") . Carbon::now()->format('Y-m-d')], 400);
                    }
                    Sale::create($input);
                    return response()->json(["message" => "success"], 201);
                }
                return response()->json(["message" => __("messages.You already added a discount to this product")], 400);
            }
            return response()->json(["message" => __("messages.You are not allowed to this")], 401);
        }
        return response()->json(["message" => __("messages.The product you are trying to edit is not exists")], 404);
    }


    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if ($product) {
            if (Gate::allows('Product-Protection', $product)) {
                if ($product->sales()->get()->first()) {
                    $sale = $product->sales()->get()->first();
                    $input = $request->all();
                    $request->validate([
                        'start' => ['nullable', 'string', 'date'],
                        'end' => ['nullable', 'string', 'date'],
                        'discount' => ['nullable', 'numeric', 'min:0', 'max:100']
                    ]);
                    if ($request->start) {
                        $input["start"] = Carbon::createFromFormat('Y-m-d', $request->start)->format('Y-m-d');
                        if ($request->end) {
                            $input['end'] = Carbon::createFromFormat('Y-m-d', $request->end)->format('Y-m-d');
                            if ($input["end"] <= $input["start"] || (!isNull($product->exp_date) && $input["end"] >= $product->exp_date) || $input["end"] < Carbon::now()) {
                                return response()->json(["ُmessage" => __("messages.End discount date is invalid")], 400);
                            }
                            if ($input["start"] >= $input["end"] || (!isNull($product->exp_date) && $input["start"] >= $product->exp_date) || $input["start"] < Carbon::now()->addDays(-1)) {
                                return response()->json(["ُmessage" => __("messages.Start discount date is invalid")], 400);
                            }
                        } elseif ($input["start"] >= $sale->end || (!isNull($product->exp_date) && $input["start"] >= $product->exp_date) || $input["start"] < Carbon::now()->addDays(-1)) {
                            return response()->json(["ُmessage" => __("messages.Start discount date is invalid")], 400);
                        }
                        $sale->update([
                            'start' => $request->start
                        ]);
                    }
                    //
                    if ($request->end) {
                        $input['end'] = Carbon::createFromFormat('Y-m-d', $request->end)->format('Y-m-d');
                        if ($input["end"] >= $sale->start || (!isNull($product->exp_date) && $input["end"] >= $product->exp_date) || $input["end"] < Carbon::now()) {
                            return response()->json(["ُmessage" => __("messages.End discount date is invalid ")], 400);
                        }
                        $sale->update([
                            'end' => $request->end
                        ]);
                        return response()->json(["message" => "success"], 200);
                    }
                    //
                    if ($request->discount) {
                        $sale->update([
                            'discount' => $request->discount
                        ]);
                    }
                    return response()->json(["message" => "success"], 200);
                }
                return response()->json(["message" => __("messages.It look there is no discount on this product")], 404);
            }
            return response()->json(["message" => __("messages.You are not allowed to this")], 401);
        }
        return response()->json(["message" => __("messages.The product you are trying to edit is not exists")], 404);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product) {
            if (Gate::allows('Product-Protection', $product)) {
                $product->sales()->delete();
                return response()->json(["message" => "success"], 200);
            }
            return response()->json(["message" => __("messages.You are not allowed to this")], 401);
        }
        return response()->json(["message" => __("messages.The product you are trying to edit is not exists")], 404);
    }
}
