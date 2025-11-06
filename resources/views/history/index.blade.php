@extends('layouts.app')

@section('title', 'Lịch sử thay thế')

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="text-center mb-4">
            <h2>LỊCH SỬ THAY THẾ</h2>
            <p class="text-muted">Danh sách lịch sử thay thế chân pin</p>
        </div>
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">📋 Lịch sử thay thế</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-success">
                        <tr>
                            <th>STT</th>
                            <th>Ngày thay thế</th>
                            <th>Model</th>
                            <th>Công đoạn</th>
                            <th>Mã Số Quản Lý</th>
                            <th>Người Thực Hiện</th>
                            <th>Xác nhận</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $i => $h)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $h->replacement_date ? $h->replacement_date->format('d/m/Y') : '' }}</td>
                            <td>{{ $h->model_name }}</td>
                            <td>{{ $h->process }}</td>
                            <td>{{ $h->management_code }}</td>
                            <td>{{ $h->executor }}</td>
                            <td>{{ $h->confirm }}</td>
                            <td>{{ $h->note }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Không có dữ liệu lịch sử thay thế</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
