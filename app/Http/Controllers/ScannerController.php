<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\ProductData;

class ScannerController extends Controller
{
    // Trang quét barcode (public - không cần login)
    public function index()
    {
        return view('scanner.index');
    }

    // API lookup barcode
    public function lookup(Request $request)
    {
        try {
            $request->validate([
                'barcode' => 'required|string',
            ]);


            $products = Product::where('barcode', $request->barcode)->get();

            // Lấy tất cả chuỗi con 6 ký tự chữ/số trong barcode
            $barcode = $request->barcode;
            // Lấy đúng 6 ký tự cuối trước dấu _ (hoặc cuối barcode nếu không có _)
            $barcodeMain = $barcode;
            if (strpos($barcode, '_') !== false) {
                $barcodeMain = explode('_', $barcode)[0];
            }
            $modelSub = substr($barcodeMain, -6);

            // Tìm trong bảng product_data các bản ghi có model_name chứa chuỗi này
            $matchedData = ProductData::where('model_name', 'like', "%$modelSub%")
                ->orderBy('replacement_date', 'desc')
                ->limit(10)
                ->get();

            $dataMatch = $matchedData->map(function($item) {
                return [
                    'model_name' => $item->model_name,
                    'quantity' => $item->quantity,
                    'date' => $item->replacement_date ? \Carbon\Carbon::parse($item->replacement_date)->format('d/m/Y') : '',
                ];
            });

            if ($products->isNotEmpty()) {
                // Tạo mảng kết quả theo từng sheet - HIỂN THỊ TẤT CẢ các sheet
                $results = [];
                foreach ($products as $product) {
                    $results[] = [
                        'sheet' => $product->sheet_name,
                        'model' => $product->model_name,
                        'quantity' => $product->quantity,
                    ];
                }
                return response()->json([
                    'success' => true,
                    'barcode' => $request->barcode,
                    'data' => $results,
                    'data_match' => $dataMatch,
                    'source_file' => $products->first()->excel_file_name,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm với mã: ' . $request->barcode,
                'data_match' => $dataMatch,
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Trang hiển thị lịch sử thay thế
    public function history()
    {
        $histories = \App\Models\ProductHistory::orderBy('replacement_date', 'desc')->get();
        // Trả về view với các cột đúng như sheet: replacement_date, model_name, process, management_code, executor, confirm, note
        return view('history.index', compact('histories'));
    }
}
