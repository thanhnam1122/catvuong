@extends('layouts.app')

@section('title', 'Quản Lý Sản Phẩm & Linh Kiện - Cát Vượng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-box-seam-fill text-warning me-2"></i> Quản Lý Sản Phẩm & Linh Kiện</h4>
        <p class="text-secondary small mb-0">Danh mục cân điện tử, thiết bị thay thế, đơn giá, thời gian bảo hành và thông số kỹ thuật.</p>
    </div>
    <button type="button" class="btn btn-warning rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createProductModal">
        <i class="bi bi-plus-lg me-1"></i> Thêm Sản Phẩm Mới
    </button>
</div>

<!-- Card Filter & Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-3">
        <!-- Search bar -->
        <form action="{{ route('products.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Tìm theo Model, Hãng SX, Diễn giải..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary">Tìm kiếm</button>
                @if(request('search'))
                    <a href="{{ route('products.index') }}" class="btn btn-link text-secondary">Xóa bộ lọc</a>
                @endif
            </div>
        </form>

        <!-- Product Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 3rem;">#</th>
                        <th style="width: 6.5rem;">Mã SP</th>
                        <th style="width: 8rem;">Hãng SX</th>
                        <th style="width: 9rem;">Model</th>
                        <th>Diễn giải chi tiết</th>
                        <th style="width: 5.5rem;">ĐVT</th>
                        <th style="width: 8rem;" class="text-end">Đơn giá (VNĐ)</th>
                        <th style="width: 7.5rem;">Bảo hành</th>
                        <th style="width: 6.5rem;" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $product)
                        <tr>
                            <td>{{ $products->firstItem() + $index }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary border fw-bold">{{ $product->code ?: 'N/A' }}</span></td>
                            <td class="fw-bold text-primary">{{ $product->brand ?: '-' }}</td>
                            <td class="fw-bold text-dark">{{ $product->model }}</td>
                            <td class="small" style="max-width: 16rem; white-space: pre-line;">{{ $product->description ?: '-' }}</td>
                            <td>{{ $product->unit }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($product->price, 0, ',', '.') }}</td>
                            <td><span class="badge bg-info-subtle text-info-emphasis border">{{ $product->warranty_period ?: 'K/BH' }}</span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" data-bs-target="#editProductModal{{ $product->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Product Modal -->
                        <div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('products.update', $product) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i> Cập Nhật Sản Phẩm</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Mã Sản Phẩm</label>
                                                <input type="text" name="code" class="form-control" value="{{ $product->code }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Hãng SX (品牌)</label>
                                                <input type="text" name="brand" class="form-control" value="{{ $product->brand }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Model (型號) <span class="text-danger">*</span></label>
                                                <input type="text" name="model" class="form-control" value="{{ $product->model }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Đơn giá (VNĐ)</label>
                                                <input type="number" name="price" class="form-control" value="{{ $product->price }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Đơn vị tính</label>
                                                <input type="text" name="unit" class="form-control" value="{{ $product->unit }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Thời gian bảo hành</label>
                                                <input type="text" name="warranty_period" class="form-control" value="{{ $product->warranty_period }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Diễn giải (說明)</label>
                                                <textarea name="description" class="form-control" rows="3">{{ $product->description }}</textarea>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Thông số kỹ thuật & Tính năng (Trang 2)</label>
                                                <textarea name="specs" class="form-control" rows="3">{{ $product->specs }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-warning fw-bold">Lưu Cập Nhật</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-1"></i> Chưa có sản phẩm / linh kiện nào trong CSDL.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $products->links() }}
        </div>
    </div>
</div>

<!-- Create Product Modal -->
<div class="modal fade" id="createProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-box-seam text-warning me-2"></i> Thêm Sản Phẩm / Linh Kiện Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã Sản Phẩm</label>
                        <input type="text" name="code" class="form-control" placeholder="Ví dụ: JWI-3100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hãng SX (品牌)</label>
                        <input type="text" name="brand" class="form-control" placeholder="Jadever, Shimadzu...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Model (型號) <span class="text-danger">*</span></label>
                        <input type="text" name="model" class="form-control" placeholder="JWI-3100" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Đơn giá (VNĐ)</label>
                        <input type="number" name="price" class="form-control" placeholder="750000">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Đơn vị tính</label>
                        <input type="text" name="unit" class="form-control" value="Cái">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Thời gian bảo hành</label>
                        <input type="text" name="warranty_period" class="form-control" placeholder="01 tháng">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Diễn giải (說明)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Chi tiết sản phẩm, lỗi sửa chữa..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Thông số kỹ thuật & Tính năng (Trang 2)</label>
                        <textarea name="specs" class="form-control" rows="3" placeholder="Các thông số hiển thị ở trang 2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-save me-1"></i> Thêm Sản Phẩm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
