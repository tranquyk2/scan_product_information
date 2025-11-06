<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\ProductData;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ExcelController extends Controller
{
    // Hiển thị trang upload
    public function showUploadForm()
    {
        $lastUpload = Product::select('excel_file_name', 'created_at')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $totalProducts = Product::count();
        
        return view('admin.upload', compact('lastUpload', 'totalProducts'));
    }

    // Xử lý upload file Excel
    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,xlsm,csv|max:10240', // Max 10MB, hỗ trợ xlsx, xls, xlsm, csv
        ]);

        try {
            $file = $request->file('excel_file');
            $fileName = $file->getClientOriginalName();
            
            // Đọc file Excel và tính toán công thức
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheetNamesInFile = $spreadsheet->getSheetNames();
            
            $imported = 0;
            $expectedSheets = ['ICT', 'MT', 'HIPOT', 'FT1', 'FT2']; // Chỉ lấy FT1 và FT2
            $importLog = []; // Log chi tiết
            
            // Bắt đầu transaction
            DB::beginTransaction();
            
            try {
                // Xóa hết data cũ
                Product::query()->delete();
                ProductHistory::query()->delete();
                ProductData::query()->delete();
                
                // Đọc từng sheet
                foreach ($sheetNamesInFile as $sheetIndex => $sheetName) {
                    $sheetImported = 0;
                    
                    // Kiểm tra sheet lịch sử thay thế (DATA hoặc LỊCH SỬ THAY THẾ, LICH SU THAY THE)
                    $sheetNameNormalized = strtolower(str_replace(' ', '', removeVietnamese($sheetName)));
                    // Sheet DATA ghi vào product_data
                    if ($sheetNameNormalized === 'data') {
                        $sheet = $spreadsheet->getSheet($sheetIndex);
                        $highestRow = $sheet->getHighestRow();
                        // Đọc từ dòng 2 trở đi (dòng 1 là header)
                        for ($row = 2; $row <= $highestRow; $row++) {
                            $modelName = trim($sheet->getCell('A' . $row)->getValue());
                            $quantity = $sheet->getCell('B' . $row)->getCalculatedValue();
                            $dateValue = $sheet->getCell('C' . $row)->getValue();
                            $replacementDate = null;
                            try {
                                if (is_numeric($dateValue)) {
                                    $replacementDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)->format('Y-m-d');
                                } elseif (is_string($dateValue)) {
                                    $parts = explode('/', $dateValue);
                                    if (count($parts) === 3) {
                                        $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                                        $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                                        $year = $parts[2];
                                        $replacementDate = "{$year}-{$month}-{$day}";
                                    } else {
                                        $replacementDate = now()->format('Y-m-d');
                                    }
                                } else {
                                    $replacementDate = now()->format('Y-m-d');
                                }
                            } catch (\Exception $e) {
                                $replacementDate = now()->format('Y-m-d');
                            }
                            if (!empty($modelName)) {
                                ProductData::create([
                                    'model_name' => $modelName,
                                    'quantity' => is_numeric($quantity) ? (int)$quantity : 0,
                                    'replacement_date' => $replacementDate,
                                    'excel_file_name' => $fileName,
                                ]);
                            }
                        }
                        $importLog[] = "Sheet DATA: Import lịch sử thay gần nhất vào bảng product_data";
                        continue;
                    }
                    // Sheet LỊCH SỬ THAY THẾ ghi vào product_histories
                    if (in_array($sheetNameNormalized, ['lichsuthaythe', 'lichsuthaythế', 'lichsuthaythe'])) {
                        $sheet = $spreadsheet->getSheet($sheetIndex);
                        $highestRow = $sheet->getHighestRow();
                        // Đọc từ dòng 4 trở đi (dòng 3 là header)
                        for ($row = 4; $row <= $highestRow; $row++) {
                            $replacementDate = $sheet->getCell('B' . $row)->getValue();
                            $modelName = trim($sheet->getCell('C' . $row)->getValue());
                            $process = trim($sheet->getCell('D' . $row)->getValue());
                            $managementCode = trim($sheet->getCell('E' . $row)->getValue());
                            $executor = trim($sheet->getCell('F' . $row)->getValue());
                            $confirm = trim($sheet->getCell('G' . $row)->getValue());
                            $note = trim($sheet->getCell('H' . $row)->getValue());
                            // Xử lý ngày tháng
                            $replacementDateFormatted = null;
                            $validDate = false;
                            try {
                                if (is_numeric($replacementDate)) {
                                    $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($replacementDate);
                                    $replacementDateFormatted = $dateObj->format('Y-m-d');
                                    if ((int)$dateObj->format('Y') >= 2000) $validDate = true;
                                } elseif (is_string($replacementDate)) {
                                    $parts = explode('/', $replacementDate);
                                    if (count($parts) === 3) {
                                        $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                                        $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                                        $year = $parts[2];
                                        $replacementDateFormatted = "{$year}-{$month}-{$day}";
                                        if ((int)$year >= 2000) $validDate = true;
                                    }
                                }
                            } catch (\Exception $e) {
                                $replacementDateFormatted = null;
                            }
                            $quantity = 0;
                            // Chỉ import nếu model hợp lệ và ngày >= năm 2000
                            if (!empty($modelName) && $validDate) {
                                ProductHistory::create([
                                    'model_name' => $modelName,
                                    'quantity' => $quantity,
                                    'replacement_date' => $replacementDateFormatted,
                                    'process' => $process,
                                    'management_code' => $managementCode,
                                    'executor' => $executor,
                                    'confirm' => $confirm,
                                    'note' => $note,
                                    'excel_file_name' => $fileName,
                                ]);
                            }
                        }
                        $importLog[] = "Sheet {$sheetName}: Import lịch sử thay thế vào bảng product_histories";
                        continue;
                    }
                    
                    // Kiểm tra xem sheet có thuộc 4 sheet cần đọc không (không phân biệt hoa thường)
                    $isExpectedSheet = false;
                    foreach ($expectedSheets as $expected) {
                        if (strtoupper(trim($sheetName)) === strtoupper($expected)) {
                            $isExpectedSheet = true;
                            break;
                        }
                    }
                    
                    if (!$isExpectedSheet) {
                        $importLog[] = "Bỏ qua sheet: {$sheetName}";
                        continue;
                    }
                    
                    // Lấy sheet
                    $sheet = $spreadsheet->getSheet($sheetIndex);
                    $highestRow = $sheet->getHighestRow();
                    
                    if ($highestRow < 4) {
                        $importLog[] = "Sheet {$sheetName}: Rỗng";
                        continue;
                    }
                    
                    // Đọc từ dòng 4 trở đi (bỏ qua 3 dòng header: tiêu đề, trống, MODEL/SỐ LƯỢNG)
                    for ($row = 4; $row <= $highestRow; $row++) {
                        $modelCell = $sheet->getCell('A' . $row);
                        $quantityCell = $sheet->getCell('B' . $row);
                        
                        $model = trim($modelCell->getValue());
                        
                        // Lấy giá trị đã tính toán (calculated value) thay vì công thức
                        $quantityValue = $quantityCell->getCalculatedValue();
                        
                        if (!empty($model) && $model !== 'MODEL') {
                            // Chuyển đổi số lượng
                            $quantity = 0;
                            if (is_numeric($quantityValue)) {
                                $quantity = (int)$quantityValue;
                            } elseif (is_string($quantityValue)) {
                                // Xử lý trường hợp có dấu phẩy/chấm
                                $quantity = (int)str_replace([',', '.'], ['', '.'], $quantityValue);
                            }
                            
                            Product::create([
                                'barcode' => $model,
                                'model_name' => $model,
                                'quantity' => $quantity,
                                'excel_file_name' => $fileName,
                                'sheet_name' => strtoupper(trim($sheetName)),
                            ]);
                            $imported++;
                            $sheetImported++;
                        }
                    }
                    
                    $importLog[] = "Sheet {$sheetName}: {$sheetImported} sản phẩm";
                }
                
                DB::commit();
                
                $logMessage = implode(' | ', $importLog);
                return back()->with('success', "Đã import thành công {$imported} sản phẩm từ file: {$fileName}. Chi tiết: {$logMessage}");
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}

function removeVietnamese($str) {
    $str = preg_replace(["/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/", "/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/", "/ì|í|ị|ỉ|ĩ/", "/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/", "/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/", "/ỳ|ý|ỵ|ỷ|ỹ/", "/đ/", "/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/", "/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/", "/Ì|Í|Ị|Ỉ|Ĩ/", "/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/", "/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/", "/Ỳ|Ý|Ỵ|Ỷ|Ỹ/", "/Đ/"], ['a','e','i','o','u','y','d','A','E','I','O','U','Y','D'], $str);
    return $str;
}
