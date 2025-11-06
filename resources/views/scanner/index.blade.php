@extends('layouts.app')

@section('title', 'Scan QR - InforModel')

@section('styles')
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    #reader {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        border: 2px solid #ddd;
        border-radius: 8px;
    }
    
    .result-card {
        display: none;
        animation: fadeIn 0.4s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .scanner-container {
        position: relative;
    }
    
    .manual-input {
        margin-top: 20px;
    }
    
    /* Custom styling cho sheet cards */
    .sheet-card {
        transition: all 0.3s ease;
        border-radius: 8px;
    }
    
    .sheet-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    /* Gradient backgrounds */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="text-center mb-4">
            <h2>Quét mã Model sản phẩm</h2>
            <p class="text-muted">Quét barcode/QR code bằng camera hoặc nhập tên model thủ công</p>
        </div>

        <!-- ZXing-JS Scanner -->
        <div class="card shadow mb-4" id="zxingScannerCard">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">📷 Quét mã QR Model</h5>
            </div>
            <div class="card-body">
                <div class="scanner-container text-center">
                    <!-- Khung quét camera phía trên -->
                    <div style="width:250px;height:250px;margin:0 auto;position:relative;">
                        <video id="zxing-video" style="width:250px;height:250px;border:2px solid #ffc107;border-radius:8px;display:none;object-fit:cover;position:absolute;top:0;left:0;" playsinline></video>
                        <!-- Khung viền scan overlay -->
                        <div id="scan-frame" style="width:250px;height:250px;position:absolute;top:0;left:0;border:2px dashed #ff9800;border-radius:8px;pointer-events:none;"></div>
                    </div>
                    <button class="btn btn-warning btn-lg mt-3" id="zxingBtn">Bật Camera</button>
                </div>
            </div>
        </div>

        <!-- Manual Input -->
        <div class="card shadow mb-4 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">✍️ Nhập thủ công</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success mb-3">
                    <strong>Cách sử dụng tốt nhất:</strong> Nhập MODEL sản phẩm vào ô bên dưới và nhấn "Tra cứu"
                </div>
                <div class="input-group input-group-lg">
                    <input type="text" id="manualBarcode" class="form-control" 
                           placeholder="Nhập MODEL sản phẩm..." autofocus>
                    <button class="btn btn-success btn-lg" id="searchBtn">🔍 Tra cứu</button>
                </div>
            </div>
        </div>

        <!-- Result Display -->
        <div id="resultCard" class="card shadow result-card">
            <div class="card-header text-white" id="resultHeader">
                <h5 class="mb-0" id="resultTitle"></h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody id="resultBody">
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Đã chuyển video lên trên, không cần video ở đây nữa -->
    </div>
</div>
@endsection

@section('scripts')
<!-- qr-scanner (nimiq) Library -->
<script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>

<script>
// qr-scanner (nimiq) QR code only
let qrScanner = null;
let isQrScanning = false;
document.getElementById('zxingBtn').addEventListener('click', async function() {
    const videoElem = document.getElementById('zxing-video');
    if (isQrScanning) {
        if (qrScanner) {
            qrScanner.stop();
            qrScanner.destroy();
            qrScanner = null;
        }
        videoElem.style.display = 'none';
        this.textContent = 'Bật Camera';
        isQrScanning = false;
        return;
    }
    videoElem.style.display = 'block';
    this.textContent = 'Tắt Camera';
    isQrScanning = true;
    qrScanner = new QrScanner(videoElem, result => {
        // Nếu result là object, lấy thuộc tính 'data', nếu là string thì dùng luôn
        const code = (typeof result === 'object' && result.data) ? result.data : result;
        lookupBarcode(code);
        if (qrScanner) {
            qrScanner.stop();
            qrScanner.destroy();
            qrScanner = null;
        }
        videoElem.style.display = 'none';
        document.getElementById('zxingBtn').textContent = 'Bật Camera';
        isQrScanning = false;
    }, {
        preferredCamera: 'environment',
        highlightScanRegion: true,
        highlightCodeOutline: true,
        maxScansPerSecond: 20
    });
    qrScanner.start();
});

// Tìm kiếm thủ công
document.getElementById('searchBtn').addEventListener('click', function() {
    const barcode = document.getElementById('manualBarcode').value.trim();
    if (barcode) {
        lookupBarcode(barcode);
    }
});

// Enter để search
document.getElementById('manualBarcode').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('searchBtn').click();
    }
});

// Gọi API lookup
function lookupBarcode(barcode) {
    // Đảm bảo barcode là string
    const barcodeStr = (typeof barcode === 'string') ? barcode : String(barcode);
    fetch('{{ route("api.lookup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ barcode: barcodeStr })
    })
    .then(response => response.json())
    .then(data => {
        displayResult(data, barcodeStr);
    })
    .catch(error => {
        displayError('Lỗi kết nối: ' + error.message);
    });
}

// Hiển thị kết quả
function displayResult(data, barcode) {
    const resultCard = document.getElementById('resultCard');
    const resultHeader = document.getElementById('resultHeader');
    const resultTitle = document.getElementById('resultTitle');
    const resultBody = document.getElementById('resultBody');
    
    if (data.success) {
        resultHeader.className = 'card-header bg-success text-white';
        resultTitle.innerHTML = '<i class="bi bi-check-circle-fill"></i> Tìm thấy sản phẩm';
        
        // Tạo HTML hiện đại cho từng sheet
        let tableRows = `
            <tr class="table-light">
                <td colspan="2" class="text-center py-3">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-upc-scan fs-4"></i>
                        <strong class="fs-5">${data.barcode}</strong>
                    </div>
                </td>
            </tr>
        `;
        
        // Màu sắc cho từng sheet
        const sheetColors = {
            'ICT': { bg: 'bg-primary', text: 'text-primary', badge: 'badge bg-primary' },
            'MT': { bg: 'bg-success', text: 'text-success', badge: 'badge bg-success' },
            'HIPOT': { bg: 'bg-warning', text: 'text-warning', badge: 'badge bg-warning' },
            'FT': { bg: 'bg-info', text: 'text-info', badge: 'badge bg-info' }
        };
        
        // Chỉ hiển thị sheet ICT nếu có, ẩn các sheet khác
        let ictItem = null;
        if (data.data && Array.isArray(data.data)) {
            ictItem = data.data.find(item => item.sheet === 'ICT');
        }
        if (ictItem) {
            const color = sheetColors['ICT'] || { bg: 'bg-primary', text: 'text-primary', badge: 'badge bg-primary' };
            tableRows += `
                <tr style="border-left: 4px solid var(--bs-primary);">
                    <td colspan="2" class="p-0">
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="${color.badge} px-3 py-2 rounded-pill">
                                    <i class="bi bi-folder-fill"></i> ICT
                                </span>
                                <small class="text-muted">
                                    <i class="bi bi-box-seam"></i> ${ictItem.model}
                                </small>
                            </div>
                            <div class="text-center py-2">
                                <div class="text-muted small mb-1">Số lượng</div>
                                <span class="fs-2 fw-bold ${color.text}">${ictItem.quantity.toLocaleString()}</span>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            // Nếu không có ICT, hiển thị các sheet khác như cũ
            data.data.forEach((item, index) => {
                if (item.sheet !== 'ICT') {
                    const color = sheetColors[item.sheet] || { bg: 'bg-secondary', text: 'text-secondary', badge: 'badge bg-secondary' };
                    const displayQuantity = item.quantity > 0 
                        ? `<span class="fs-2 fw-bold ${color.text}">${item.quantity.toLocaleString()}</span>`
                        : `<span class="text-muted fs-4">0</span>`;
                    tableRows += `
                        <tr style="border-left: 4px solid var(--bs-${item.sheet === 'MT' ? 'success' : item.sheet === 'HIPOT' ? 'warning' : 'info'});">
                            <td colspan="2" class="p-0">
                                <div class="p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="${color.badge} px-3 py-2 rounded-pill">
                                            <i class="bi bi-folder-fill"></i> ${item.sheet}
                                        </span>
                                        <small class="text-muted">
                                            <i class="bi bi-box-seam"></i> ${item.model}
                                        </small>
                                    </div>
                                    <div class="text-center py-2">
                                        <div class="text-muted small mb-1">Số lượng</div>
                                        ${displayQuantity}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            });
        }
        
        // Hiển thị lịch sử thay thế (nếu có)
        if (data.history && data.history.length > 0) {
            tableRows += `
                <tr class="table-secondary">
                    <td colspan="2" class="text-center py-2">
                        <strong><i class="bi bi-clock-history"></i> Lịch sử thay thế gần đây</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="p-3">
                        <div class="row g-2">
            `;
            
            data.history.forEach(h => {
                tableRows += `
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body p-2 text-center">
                                <div class="text-muted small">
                                    <i class="bi bi-calendar3"></i> ${h.date}
                                </div>
                                <div class="fw-bold text-primary">${h.quantity.toLocaleString()}</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            tableRows += `
                        </div>
                    </td>
                </tr>
            `;
        }
        
        // Hiển thị lịch sử thay thế từ sheet DATA nếu có (history_match)
        if (data.history_match && data.history_match.length > 0) {
            tableRows += `
                <tr class="table-secondary">
                    <td colspan="2" class="text-center py-2">
                        <strong><i class="bi bi-clock-history"></i> Lịch sử thay thế (sheet DATA)</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="p-3">
                        <div class="row g-2">
            `;
            data.history_match.forEach(h => {
                tableRows += `
                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <div class="card border-0 bg-light">
                            <div class="card-body p-2 text-center">
                                <div class="text-muted small mb-1">
                                    <i class="bi bi-calendar3"></i> ${h.date}
                                </div>
                                <div class="fw-bold text-primary mb-1">${h.model_name}</div>
                                <div class="mb-1"><span class="badge bg-info">Số lượng thay: ${h.quantity}</span></div>
                                <div class="small text-muted">Công đoạn: ${h.process || ''}</div>
                                <div class="small text-muted">Mã QL: ${h.management_code || ''}</div>
                                <div class="small text-muted">Người: ${h.executor || ''}</div>
                                <div class="small text-muted">Xác nhận: ${h.confirm || ''}</div>
                                <div class="small text-muted">Ghi chú: ${h.note || ''}</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            tableRows += `
                        </div>
                    </td>
                </tr>
            `;
        }
        
        // Hiển thị kết quả đối chiếu sheet DATA nếu có (data_match)
        if (data.data_match && data.data_match.length > 0) {
            tableRows += `
                <tr class="table-secondary">
                    <td colspan="2" class="text-center py-2">
                        <strong><i class="bi bi-clock-history"></i> Đối chiếu sheet DATA</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="p-3">
                        <div class="row g-2">
            `;
            data.data_match.forEach(h => {
                tableRows += `
                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <div class="card border-0 bg-light">
                            <div class="card-body p-2 text-center">
                                <div class="fw-bold text-primary mb-1">${h.model_name}</div>
                                <div class="mb-1"><span class="badge bg-info">Số lượng: ${h.quantity}</span></div>
                                <div class="small text-muted">Ngày: ${h.date}</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            tableRows += `
                        </div>
                    </td>
                </tr>
            `;
        }
        
        tableRows += `
            <tr class="table-light">
                <td colspan="2" class="text-center py-2">
                    <small class="text-muted">
                        <i class="bi bi-file-earmark-excel"></i> 
                        ${data.source_file || 'N/A'}
                    </small>
                </td>
            </tr>
        `;
        
        resultBody.innerHTML = tableRows;
    } else {
        resultHeader.className = 'card-header bg-danger text-white';
        resultTitle.innerHTML = '<i class="bi bi-x-circle-fill"></i> Không tìm thấy';
        
        resultBody.innerHTML = `
            <tr>
                <td class="text-center py-5">
                    <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                    <p class="mb-1 fs-5">${data.message}</p>
                    <small class="text-muted">Mã: ${barcode}</small>
                </td>
            </tr>
        `;
    }
    
    resultCard.style.display = 'block';
    resultCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    // Clear input
    document.getElementById('manualBarcode').value = '';
}

function displayError(message) {
    const resultCard = document.getElementById('resultCard');
    const resultHeader = document.getElementById('resultHeader');
    const resultTitle = document.getElementById('resultTitle');
    const resultBody = document.getElementById('resultBody');
    
    resultHeader.className = 'card-header bg-warning text-dark';
    resultTitle.textContent = '⚠️ Lỗi';
    resultBody.innerHTML = `<tr><td class="text-center">${message}</td></tr>`;
    resultCard.style.display = 'block';
}
</script>
@endsection
