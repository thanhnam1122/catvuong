@extends('layouts.app')

@section('title', 'Quản Lý Khách Hàng - Cát Vượng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i> Quản Lý Khách Hàng</h4>
        <p class="text-secondary small mb-0">Quản lý danh sách khách hàng, thông tin người liên hệ, SĐT, địa chỉ và mã số thuế.</p>
    </div>
    <button type="button" class="btn btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
        <i class="bi bi-plus-lg me-1"></i> Thêm Khách Hàng Mới
    </button>
</div>

<!-- Card Filter & Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-3">
        <!-- Search bar -->
        <form action="{{ route('customers.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Tìm tên công ty, sĐT, người liên hệ..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary">Tìm kiếm</button>
                @if(request('search'))
                    <a href="{{ route('customers.index') }}" class="btn btn-link text-secondary">Xóa bộ lọc</a>
                @endif
            </div>
        </form>

        <!-- Customer Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 3rem;">#</th>
                        <th style="width: 6rem;">Mã KH</th>
                        <th>Kính gửi / Tên Công Ty</th>
                        <th>Người Liên Hệ</th>
                        <th style="width: 8rem;">Điện thoại</th>
                        <th style="width: 8rem;">Fax</th>
                        <th>Địa chỉ</th>
                        <th>Mail, Teams, Zalo</th>
                        <th style="width: 7rem;" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $index => $customer)
                        <tr>
                            <td>{{ $customers->firstItem() + $index }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary border fw-bold">{{ $customer->code ?: 'N/A' }}</span></td>
                            <td class="fw-bold text-dark">{{ $customer->name }}</td>
                            <td>{{ $customer->contact_person ?: '-' }}</td>
                            <td>{{ $customer->tel ?: '-' }}</td>
                            <td>{{ $customer->fax ?: '-' }}</td>
                            <td class="small text-secondary" style="max-width: 12rem;">{{ $customer->address ?: '-' }}</td>
                            <td class="small text-info-emphasis fw-medium">{{ $customer->social_contact ?: '-' }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $customer->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa khách hàng này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Customer Modal -->
                        <div class="modal fade" id="editCustomerModal{{ $customer->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('customers.update', $customer) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i> Cập Nhật Khách Hàng</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Mã Khách Hàng</label>
                                                <input type="text" name="code" class="form-control" value="{{ $customer->code }}">
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label fw-semibold">Kính gửi / Tên Công Ty <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Người liên hệ</label>
                                                <input type="text" name="contact_person" class="form-control" value="{{ $customer->contact_person }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Điện thoại / Tel</label>
                                                <input type="text" name="tel" class="form-control" value="{{ $customer->tel }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Fax</label>
                                                <input type="text" name="fax" class="form-control" value="{{ $customer->fax }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Địa chỉ</label>
                                                <input type="text" name="address" class="form-control" value="{{ $customer->address }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Mail, Teams, Zalo</label>
                                                <input type="text" name="social_contact" class="form-control" value="{{ $customer->social_contact }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-primary">Lưu Cập Nhật</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-1"></i> Chưa có thông tin khách hàng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $customers->links() }}
        </div>
    </div>
</div>

<!-- Create Customer Modal -->
<div class="modal fade" id="createCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill text-primary me-2"></i> Thêm Khách Hàng Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã Khách Hàng</label>
                        <input type="text" name="code" class="form-control" placeholder="Ví dụ: TUICO03">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Kính gửi / Tên Công Ty <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Công Ty Cổ Phần TuiCo 3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Người liên hệ</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="Chị Hằng (thu mua) 0989 169170">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Điện thoại / Tel</label>
                        <input type="text" name="tel" class="form-control" placeholder="02513 671222">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fax</label>
                        <input type="text" name="fax" class="form-control" placeholder="02513 671666">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Địa chỉ</label>
                        <input type="text" name="address" class="form-control" placeholder="Lô đất số 1-16, KCN Hố Nai, TP. Đồng Nai">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Mail, Teams, Zalo</label>
                        <input type="text" name="social_contact" class="form-control" placeholder="Zalo, hang@tuico.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Thêm Khách Hàng</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
