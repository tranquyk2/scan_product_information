@extends('layouts.app')

@section('title', 'Upload Excel - Admin')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">📤 Upload File Excel</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>ℹ️ Hướng dẫn:</strong>
                    <ul class="mb-0">
                        <li>File Excel phải có định dạng: <strong>.xlsx, .xls, .xlsm, .csv</strong></li>
                        <li>File Excel phải có <strong>4 sheet: ICT, MT, HIPOT, FT</strong></li>
                        <li>Mỗi sheet có cấu trúc: dòng 1-2 là tiêu đề, dòng 3 trở đi là dữ liệu</li>
                        <li>Cột A: MODEL (barcode), Cột B: SỐ LƯỢNG</li>
                        <li>Upload file mới sẽ <strong>xóa hết</strong> dữ liệu cũ</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('admin.upload.post') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel</label>
                        <input type="file" name="excel_file" class="form-control @error('excel_file') is-invalid @enderror" 
                               accept=".xlsx,.xls,.xlsm,.csv" required>
                        @error('excel_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        📤 Upload & Import
                    </button>
                </form>
            </div>
        </div>

        <!-- Thông tin upload gần nhất -->
        @if($lastUpload)
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title">📊 Thông tin hiện tại:</h6>
                <ul class="mb-0">
                    <li><strong>File gần nhất:</strong> {{ $lastUpload->excel_file_name }}</li>
                    <li><strong>Thời gian:</strong> {{ $lastUpload->created_at->format('d/m/Y H:i:s') }}</li>
                    <li><strong>Tổng sản phẩm:</strong> {{ number_format($totalProducts) }} sản phẩm</li>
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
