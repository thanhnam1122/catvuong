@extends('layouts.app')

@section('title', 'Thông Số Kỹ Thuật & Hình Ảnh Phụ Kiện (Trang 2) - Cát Vượng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-image-fill text-info me-2"></i> Thông Số Kỹ Thuật & Hình Ảnh Phụ Kiện (Trang 2)</h4>
        <p class="text-secondary small mb-0">Quản lý các mẫu bảng thông số kỹ thuật, hình ảnh phụ kiện sửa chữa hiển thị tại Trang 2 phiếu báo giá.</p>
    </div>
    <button type="button" class="btn btn-info text-white fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createSpecModal">
        <i class="bi bi-plus-lg me-1"></i> Thêm Mẫu Trang 2 Mới
    </button>
</div>

<!-- Card Filter & Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-3">
        <!-- Search bar -->
        <form action="{{ route('tech-specs.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Tìm Model, Hãng Cân, Thông số..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary">Tìm kiếm</button>
                @if(request('search'))
                    <a href="{{ route('tech-specs.index') }}" class="btn btn-link text-secondary">Xóa bộ lọc</a>
                @endif
            </div>
        </form>

        <!-- Page 2 Table Replica -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.88rem; border-color: #000000;">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 3rem;">#</th>
                        <th style="width: 10rem;">Hãng Cân<br>Thương Hiệu</th>
                        <th style="width: 11rem;">Model<br>Tải Trọng</th>
                        <th style="width: 13rem;">Hình Ảnh Phụ Kiện</th>
                        <th>Thông Số Kỹ Thuật và Tính Năng</th>
                        <th style="width: 6.5rem;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($specs as $index => $spec)
                        <tr>
                            <td class="text-center">{{ $specs->firstItem() + $index }}</td>
                            <td class="fw-bold text-center text-primary">{{ $spec->brand }}</td>
                            <td class="fw-bold text-center">{{ $spec->model }}</td>
                            <td class="text-center p-2">
                                @if($spec->image_path)
                                    <img src="{{ $spec->image_path }}" class="img-thumbnail rounded" style="max-height: 5.5rem; max-width: 11rem;" alt="Hình ảnh linh kiện">
                                @else
                                    <div class="border rounded p-2 bg-light d-inline-flex flex-column align-items-center justify-content-center" style="width: 10rem; height: 5rem;">
                                        <svg viewBox="0 0 200 100" style="width: 100%; height: 100%;">
                                            <rect x="5" y="5" width="190" height="90" rx="4" fill="#2d3748" stroke="#1a202c" stroke-width="2"/>
                                            <rect x="20" y="15" width="160" height="60" rx="2" fill="#9ae6b4"/>
                                            <text x="30" y="55" font-family="monospace" font-size="28" font-weight="bold" fill="#1a202c">888.8 0.00 g</text>
                                            <rect x="15" y="80" width="170" height="10" fill="#4a5568"/>
                                        </svg>
                                        <span class="text-secondary small mt-1" style="font-size: 0.65rem;">(Ảnh SVG mẫu)</span>
                                    </div>
                                @endif
                            </td>
                            <td class="small" style="white-space: pre-line; line-height: 1.4;">{{ $spec->specs }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" data-bs-target="#editSpecModal{{ $spec->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('tech-specs.destroy', $spec) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa mẫu thông số này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Spec Modal -->
                        <div class="modal fade" id="editSpecModal{{ $spec->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('tech-specs.update', $spec) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-info me-2"></i> Cập Nhật Thông Số Kỹ Thuật (Trang 2)</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Hãng Cân Thương Hiệu <span class="text-danger">*</span></label>
                                                <input type="text" name="brand" class="form-control" value="{{ $spec->brand }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Model Tải Trọng <span class="text-danger">*</span></label>
                                                <input type="text" name="model" class="form-control" value="{{ $spec->model }}" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Tải ảnh phụ kiện mới (Tùy chọn)</label>
                                                <input type="file" name="image" class="form-control" accept="image/*">
                                                @if($spec->image_path)
                                                    <div class="mt-2">
                                                        <span class="small text-muted me-2">Ảnh hiện tại:</span>
                                                        <img src="{{ $spec->image_path }}" style="max-height: 4rem;" class="rounded border">
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Thông số kỹ thuật và Tính năng</label>
                                                <textarea name="specs" class="form-control" rows="8">{{ $spec->specs }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-info text-white fw-bold">Lưu Cập Nhật</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-1"></i> Chưa có mẫu thông số kỹ thuật nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $specs->links() }}
        </div>
    </div>
</div>

<!-- Create Spec Modal -->
<div class="modal fade" id="createSpecModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('tech-specs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill text-info me-2"></i> Thêm Mẫu Thông Số Kỹ Thuật (Trang 2)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hãng Cân Thương Hiệu <span class="text-danger">*</span></label>
                        <input type="text" name="brand" class="form-control" placeholder="Shimadzu Nhật" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Model Tải Trọng <span class="text-danger">*</span></label>
                        <input type="text" name="model" class="form-control" placeholder="Display UX/UW UP-X/UP-Y" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tải ảnh phụ kiện / linh kiện (Tùy chọn)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Thông số kỹ thuật và Tính năng</label>
                        <textarea name="specs" class="form-control" rows="8" placeholder="- Display LCD (màn hình hiển thị số)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-info text-white fw-bold"><i class="bi bi-save me-1"></i> Thêm Mới</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
